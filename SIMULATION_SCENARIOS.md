# Property Management System - Simulation Scenarios Summary

## 🎯 Overview
This document summarizes the 6 simulation scenarios created to demonstrate the Property Management System's capabilities, with special focus on the **Payment Offset Logic** for handling surplus payments.

---

## 📊 Scenario Breakdown

### ✅ Scenario 1: Good Tenant (SST Registered)
**Tenant:** Tenant One (Good)  
**Property:** Scenario 1 - Suria KLCC  
**Rent:** RM 5,000/month  
**Status:** All invoices paid on time  
**Special:** SST Registered (8% tax = RM 400 per invoice)

**Key Features:**
- Demonstrates SST calculation
- Perfect payment history
- No outstanding balance

**Test Command:**
```bash
php artisan tinker --execute="dump(App\Models\Tenant::where('email', 'good@simulation.com')->with('tenancyAgreements.property')->first());"
```

---

### ⏰ Scenario 2: Late Payer
**Tenant:** Tenant Two (Late)  
**Property:** Scenario 2 - Mid Valley  
**Rent:** RM 3,000/month  
**Status:** 2 pending invoices with late fees applied

**Outstanding:**
- INV-S2-3: RM 3,060.00 (includes RM 60 late fee)
- INV-S2-4: RM 3,060.00 (includes RM 60 late fee)
- **Total:** RM 6,120.00

**Key Features:**
- Demonstrates late fee calculation (2% per month)
- Shows overdue invoice tracking
- Late fee grace period (7 days)

**Test Commands:**
```bash
# Preview what would happen if tenant pays from wallet
php artisan payments:process-offsets --tenant=2 --preview

# Apply late fees
php artisan invoices:apply-late-fees
```

---

### 💰 Scenario 3: Rich Tenant (Large Wallet)
**Tenant:** Tenant Three (Rich)  
**Property:** Scenario 3 - Pavilion  
**Rent:** RM 8,000/month  
**Wallet Balance:** RM 25,000.00

**Key Features:**
- Demonstrates advance payment handling
- Large wallet balance for auto-deduction
- Can process wallet offsets

**Test Command:**
```bash
php artisan payments:process-offsets --tenant=3 --preview
```

---

### ⌛ Scenario 4: Expiring Tenancy
**Tenant:** Tenant Four (Expiring)  
**Property:** Scenario 4 - Sunway Pyramid  
**Rent:** RM 4,500/month  
**Agreement Ends:** In 5 days

**Key Features:**
- Demonstrates tenancy lifecycle management
- All payments up to date
- Agreement about to expire

**Test Command:**
```bash
php artisan tenancy:check-expiry
```

---

### 🔧 Scenario 5: Maintenance Heavy Property
**Property:** Scenario 5 - Old Shop  
**Status:** Vacant  
**Expenditures:** 5 maintenance records

**Expenses:**
1. Roof Leak
2. Plumbing
3. Repaint Walls
4. Door Fix
5. Aircon Service

**Key Features:**
- Demonstrates expense tracking
- Multiple expenditure records
- Property maintenance history

**Test Command:**
```bash
php artisan tinker --execute="dump(App\Models\Invoice::where('property_id', 5)->where('type', 'expenditure')->get());"
```

---

### ⭐ Scenario 6: Surplus Payment (AUTO OFFSET) - **NEW!**
**Tenant:** Tenant Six (Surplus Payer)  
**Property:** Scenario 6 - KLCC Office  
**Rent:** RM 2,500/month

**Initial State:**
- Pending Invoices: 2
  - INV-S6-2: RM 2,500.00 (Nov 2025)
  - INV-S6-3: RM 2,500.00 (Dec 2025)
- Total Outstanding: RM 5,000.00
- Wallet Balance: RM 0.00

**Action:**
Tenant makes a payment of **RM 7,000.00**

**Automatic Processing (FIFO):**
1. ✅ Apply RM 2,500 to INV-S6-2 → Status: **PAID**
2. ✅ Apply RM 2,500 to INV-S6-3 → Status: **PAID**
3. ✅ Remaining RM 2,000 → Added to **Wallet**

**Final State:**
- INV-S6-2: **PAID** (RM 2,500.00)
- INV-S6-3: **PAID** (RM 2,500.00)
- Wallet Balance: **RM 2,000.00**

**Key Features:**
- ✨ **Automatic payment offset logic**
- 📅 **FIFO** (First In, First Out) - oldest bills paid first
- 💳 **Surplus handling** - excess added to wallet
- 🔄 **Real-time processing** - triggered by InvoiceObserver
- 📊 **Transparent tracking** - original amounts preserved

**Test Command:**
```bash
php artisan test:surplus-payment
```

**Expected Output:**
```
Payment Received:     RM 7,000.00
Applied to Bills:     RM 5,000.00
Added to Wallet:      RM 2,000.00

✓ SUCCESS! Payment offset logic working correctly!
```

---

## 🎬 Running All Scenarios

### Quick Demo
```bash
php artisan test:surplus-payment
```

### Full Database Reset
```bash
php artisan db:seed --class=SimulationSeeder
```

### Preview Payment Offsets
```bash
# For specific tenant
php artisan payments:process-offsets --tenant=6 --preview

# For all tenants with wallet balance
php artisan payments:process-offsets --preview
```

### Apply Payment Offsets
```bash
# For specific tenant
php artisan payments:process-offsets --tenant=6

# For all tenants
php artisan payments:process-offsets
```

---

## 📋 Summary Table

| Scenario | Tenant | Key Feature | Outstanding | Wallet | Status |
|----------|--------|-------------|-------------|--------|--------|
| 1 | Good Tenant | SST Registered | RM 0 | RM 0 | ✅ All Paid |
| 2 | Late Payer | Late Fees | RM 6,120 | RM 0 | ⚠️ Overdue |
| 3 | Rich Tenant | Large Wallet | RM 0 | RM 25,000 | ✅ Advance |
| 4 | Expiring | Lifecycle | RM 0 | RM 0 | ⏰ Ending Soon |
| 5 | N/A (Property) | Maintenance | N/A | N/A | 🔧 Vacant |
| 6 | Surplus Payer | **Auto Offset** | RM 5,000 → RM 0 | RM 0 → RM 2,000 | ⭐ **DEMO** |

---

## 🚀 Key Innovations

### Payment Offset System
1. **Automatic Processing** - No manual intervention needed
2. **FIFO Logic** - Fair and transparent
3. **Surplus Handling** - Excess goes to wallet
4. **Partial Payments** - Supports incomplete payments
5. **Audit Trail** - Original amounts preserved
6. **Real-time** - Triggered by database observers

### Technical Implementation
- **Service Layer:** `PaymentOffsetService`
- **Observer Pattern:** `InvoiceObserver`
- **Database Field:** `original_amount_total`
- **Status Support:** `pending`, `partial`, `paid`, `void`

---

## 📖 Documentation
See `PAYMENT_OFFSET_LOGIC.md` for complete technical documentation.

---

## ✅ Verification

All scenarios have been tested and verified:
- ✅ Scenario 1: SST calculation working
- ✅ Scenario 2: Late fees applied correctly
- ✅ Scenario 3: Wallet balance tracked
- ✅ Scenario 4: Expiry detection working
- ✅ Scenario 5: Expenditures recorded
- ✅ **Scenario 6: Payment offset logic WORKING!** ⭐

**Last Tested:** 2026-01-30  
**Status:** All systems operational ✅
