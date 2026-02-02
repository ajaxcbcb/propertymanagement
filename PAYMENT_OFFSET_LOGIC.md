# Payment Offset Logic - Documentation

## Overview
The Payment Offset System automatically applies received payments to settle the oldest outstanding invoices first (FIFO - First In, First Out). When a tenant pays more than what they owe, the system:
1. Settles pending invoices starting from the oldest
2. Adds any surplus amount to the tenant's wallet balance

## Key Components

### 1. PaymentOffsetService (`app/Services/PaymentOffsetService.php`)
The core service that handles all payment offset logic.

**Main Methods:**
- `applyPaymentOffset(Invoice $newPayment)` - Automatically applies payment to oldest bills
- `processWalletOffsets(int $tenantId)` - Uses tenant wallet to pay pending invoices
- `getOffsetPreview(int $tenantId)` - Preview what would happen without making changes

### 2. InvoiceObserver (`app/Observers/InvoiceObserver.php`)
Automatically triggers payment offset when:
- A new payment is created with status 'paid'
- An existing invoice status changes to 'paid'

### 3. Database Schema
**New Field Added:**
- `original_amount_total` - Stores the initial invoice amount before any offsets

**Invoice Statuses:**
- `pending` - Not yet paid
- `partial` - Partially paid (some amount received, but not full)
- `paid` - Fully paid
- `void` - Cancelled/voided

## How It Works

### Example: Surplus Payment Scenario

**Initial State:**
```
Tenant: Tenant Six (Surplus Payer)
Wallet Balance: RM 0.00

Pending Invoices:
- INV-S6-2: RM 2,500.00 (Nov 2025)
- INV-S6-3: RM 2,500.00 (Dec 2025)
Total Outstanding: RM 5,000.00
```

**Action:**
Tenant makes a payment of RM 7,000.00

**Automatic Processing:**
1. System identifies 2 pending invoices (oldest first)
2. Applies RM 2,500 to INV-S6-2 → Status: PAID
3. Applies RM 2,500 to INV-S6-3 → Status: PAID
4. Remaining RM 2,000 → Added to tenant wallet

**Final State:**
```
INV-S6-2: PAID (RM 2,500.00)
INV-S6-3: PAID (RM 2,500.00)
Tenant Wallet Balance: RM 2,000.00
```

## Artisan Commands

### 1. Process Payment Offsets
```bash
# Preview what would happen for a specific tenant
php artisan payments:process-offsets --tenant=6 --preview

# Actually process offsets for a specific tenant
php artisan payments:process-offsets --tenant=6

# Process all tenants with wallet balance
php artisan payments:process-offsets
```

### 2. Test Surplus Payment
```bash
# Run the surplus payment demonstration
php artisan test:surplus-payment
```

### 3. Demo All Scenarios
```bash
# Run all 6 simulation scenarios
php artisan demo:all-scenarios
```

## Simulation Scenarios

### Scenario 1: Good Tenant (SST Registered)
- All invoices paid on time
- SST registered (8% tax applied)
- No outstanding balance

### Scenario 2: Late Payer
- Has pending invoices with late fees
- Demonstrates overdue payment tracking
- Late fees automatically applied

### Scenario 3: Rich Tenant (Large Wallet)
- Has RM 25,000 in wallet balance
- Can use wallet to auto-pay future invoices
- Demonstrates advance payment handling

### Scenario 4: Expiring Tenancy
- Agreement expires in 5 days
- All payments up to date
- Demonstrates tenancy lifecycle

### Scenario 5: Maintenance Heavy Property
- Multiple expenditure records
- Vacant property with maintenance costs
- Demonstrates expense tracking

### Scenario 6: Surplus Payment ⭐ NEW
- Tenant pays more than owed
- **Automatic offset to old bills**
- **Excess added to wallet**
- Demonstrates FIFO payment application

## Usage in Application

### Automatic Processing
When you create a new payment through the Filament UI:
1. Fill in the payment form (tenant, amount, etc.)
2. Set status to 'paid'
3. Save the record
4. **System automatically:**
   - Finds oldest pending invoices
   - Applies payment (FIFO)
   - Updates invoice statuses
   - Credits excess to wallet

### Manual Processing
Use the Artisan command to process wallet balances:
```bash
php artisan payments:process-offsets --tenant=6
```

## Benefits

1. **Automatic** - No manual calculation needed
2. **Fair** - FIFO ensures oldest debts are settled first
3. **Transparent** - All actions logged
4. **Flexible** - Handles partial payments, surplus, and wallet credits
5. **Auditable** - Original amounts preserved in `original_amount_total`

## Testing

Run the simulation seeder:
```bash
php artisan db:seed --class=SimulationSeeder
```

Then test the surplus payment:
```bash
php artisan test:surplus-payment
```

Expected output: Payment of RM 7,000 settles RM 5,000 in bills, RM 2,000 to wallet.

## Future Enhancements

- [ ] Email notifications when payments are offset
- [ ] Detailed offset history/audit trail
- [ ] Bulk payment processing
- [ ] Payment allocation preferences (FIFO, LIFO, specific invoices)
- [ ] Integration with payment gateways
