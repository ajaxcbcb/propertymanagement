# Property Management System - Implementation Summary

## ✅ COMPLETED PHASES

### Phase 1: Project Initialization ✓
- ✅ Laravel 11 installed successfully
- ✅ FilamentPHP v5.1 installed
- ✅ Admin panel created at `/admin`
- ✅ Admin user created:
  - Email: admin@example.com
  - Password: password

### Phase 2: Database Schema ✓
All migrations created and executed successfully:

#### Properties Table
- `id` (primary key)
- `name` (string)
- `type` (string)
- `base_rent` (decimal)
- `status` (enum: vacant, occupied)
- `timestamps`

#### Tenants Table
- `id` (primary key)
- `name` (string)
- `email` (string, unique)
- `phone` (string)
- `is_sst_registered` (boolean, default: false)
- `sst_registration_date` (date, nullable)
- `wallet_balance` (decimal, default: 0)
- `timestamps`

#### Tenancy Agreements Table
- `id` (primary key)
- `tenant_id` (foreign key → tenants)
- `property_id` (foreign key → properties)
- `start_date` (date)
- `end_date` (date)
- `agreed_rent` (decimal)
- `is_active` (boolean, default: true)
- `timestamps`

#### Invoices Table
- `id` (primary key)
- `tenant_id` (foreign key → tenants)
- `tenancy_agreement_id` (foreign key → tenancy_agreements)
- `invoice_number` (string, unique)
- `due_date` (date)
- `amount_base` (decimal)
- `amount_sst` (decimal, default: 0)
- `amount_total` (decimal)
- `status` (enum: unpaid, paid, partial, overdue)
- `timestamps`

### Phase 3: Filament Resources ✓
All resources created with auto-generated forms and tables:
- ✅ PropertyResource
- ✅ TenantResource (with SST compliance UI)
- ✅ TenancyAgreementResource
- ✅ InvoiceResource

#### TenantResource - SST Compliance Features
- **Tax Compliance Section** with warning message
- **SST Registered Toggle** with helper text
- **"No Revert" UI Logic**: Toggle becomes disabled once SST is registered
- **Auto-date Setting**: Registration date automatically set when enabled
- **Conditional Visibility**: Registration date only shows when SST is enabled

### Phase 4: Financial Logic (SST "No Revert" Rule) ✓

#### TenantObserver Implementation
- ✅ Listens for `updating` event
- ✅ Throws exception if trying to change `is_sst_registered` from `true` to `false`
- ✅ Error message: "COMPLIANCE ERROR: You cannot revert an SST-registered customer."
- ✅ Auto-sets `sst_registration_date` when changing from `false` to `true`
- ✅ Registered in AppServiceProvider

#### Monthly Billing Command
- ✅ Command: `php artisan invoices:generate`
- ✅ Loops through all active tenancy agreements
- ✅ Calculates SST (8% for registered tenants)
- ✅ Generates unique invoice numbers (format: INV-YYYYMM-00001)
- ✅ Wallet deduction logic:
  - Full payment if wallet balance >= invoice amount (status: paid)
  - Partial payment if wallet balance < invoice amount (status: partial)
  - No payment if wallet balance = 0 (status: unpaid)
- ✅ Console logging with colored output

### Phase 5: Advanced Features & Reporting (Completed) ✓
- **SST Reporting**:
  - ✅ "Export SST Report" action added to Invoices table
  - ✅ Generates CSV with date range filtering (Month/Year)
  - ✅ Columns: Invoice #, Date, Tenant, SST Reg No, Amounts
- **Tenancy Management**:
  - ✅ "Expiring Next Month" filter added to Tenancy Agreements
  - ✅ "Renew" action implemented: Deactivates old agreement, creates new one
  - ✅ Renewal includes Rent Review capability
- **Customer Profiling**:
  - ✅ "Tenancy Agreements" tab added to Tenant View
  - ✅ Shows all properties linked to a single customer
- **Automation**:
  - ✅ `php artisan invoices:reminders` command
  - ✅ Sends email 15 days before due date for unpaid invoices

### Phase 6: Business Logic Module (Completed) ✓
- **System Configuration**:
  - ✅ `SystemSetting` model & database table
  - ✅ Admin UI to manage settings (SST Rate, Late Fee Rate)
  - ✅ Default seeded values (8% SST, 2% Late Fee)
- **Dynamic Logic**:
  - ✅ Invoice generation now reads SST Rate from database
  - ✅ `php artisan invoices:apply-late-fees` command created
  - ✅ Automatically updates overdue invoices with 2% penalty every 30 days
  - ✅ Tracks accumulated late fees in database

### Phase 7: Global Search Integration (Refined) ✓
- **Logic**:
  - Global Search queries now redirect to the **Account Statement** overview.
  - Search keywords (Invoice #, Property Name, Tenant Name) correspond to filters in the Account Statement.
- **Workflow**:
  1. User types in Global Search bar (Cmd+K).
  2. Selects a result (Invoice, Property, or Tenant).
  3. System redirects to **Account Statements** page.
  4. The selected record's name is automatically applied as a filter (`tableSearch`).
- **Benefits**:
  - Reuses the powerful Account Statement view.
  - Consistent filtering experience.
  - No duplicate maintenance of custom report views.

## 📋 HOW TO USE

### Access the Admin Panel
1. Start the server (use `serve_app.bat`)
2. Navigate to: `http://localhost:8888/admin`
3. Login with: `admin@example.com` / `password`

### Manage System Settings
1. Go to **System Settings** in the menu
2. Modify **SST Rate** or **Late Fee Rate** as needed
3. Changes apply immediately to **future** invoices/calculations

### Export SST Report
1. Go to **Invoices**
2. Click **Export SST Report** (header action)
3. Select Month and Year
4. Download CSV

### Manage Renewals
1. Go to **Tenancy Agreements**
2. Usage Filter: **Expiring Next Month**
3. Click **Renew** (recycle icon) on an agreement
4. Set new dates and rent amount

### Customer Profile
1. Go to **Tenants** -> Edit a Tenant
2. Scroll down to see **Tenancy Agreements** list
3. You can see all active/inactive properties for this tenant

### Send Reminders
```bash
php artisan invoices:reminders
```
(Ideally scheduled daily)

### Use Global Search
1. Click the search icon in the top navigation (or press `Cmd/Ctrl + K`)
2. Type your search query:
   - **For invoices**: Enter invoice number (e.g., "INV-2024-001")
   - **For properties**: Enter property name, address, or lot number
   - **For tenants**: Enter tenant name, email, or phone number
3. Results are grouped by resource type
4. Click any result to navigate directly to that record
5. **Search Examples**:
   - Search "Jalan Ampang" → Shows properties, tenancy agreements, and tenants in that area
   - Search "John Doe" → Shows tenant with all properties and invoice statistics
   - Search "INV-2024-001" → Shows the specific invoice with full details

### Create Test Data
1. **Create Properties**:
   - Go to Properties menu
   - Add properties with name, type, base rent, and status

2. **Create Tenants**:
   - Go to Tenants menu
   - Add tenant details
   - Enable SST registration if needed (cannot be disabled later!)
   - Add wallet balance if desired

3. **Create Tenancy Agreements**:
   - Go to Tenancy Agreements menu
   - Select tenant and property
   - Set dates and agreed rent
   - Mark as active

4. **Generate Invoices**:
   ```bash
   php artisan invoices:generate
   ```
   - This will create invoices for all active agreements
   - SST (8%) will be added for registered tenants
   - Wallet balances will be automatically deducted

### Test the "No Revert" Rule
1. Create a tenant
2. Enable "SST Registered" toggle
3. Save the tenant
4. Try to edit and disable the toggle
5. You'll see it's disabled in the UI
6. If you try to force it via code, you'll get: "COMPLIANCE ERROR: You cannot revert an SST-registered customer."

## 🔧 TECHNICAL DETAILS

### Models & Relationships
- **Property** → hasMany TenancyAgreements
- **Tenant** → hasMany TenancyAgreements, hasMany Invoices
- **TenancyAgreement** → belongsTo Tenant, belongsTo Property, hasMany Invoices
- **Invoice** → belongsTo Tenant, belongsTo TenancyAgreement

### Key Files
- **Migrations**: `database/migrations/2026_01_29_060344_create_*_table.php`
- **Models**: `app/Models/{Property,Tenant,TenancyAgreement,Invoice}.php`
- **Resources**: `app/Filament/Resources/{Properties,Tenants,TenancyAgreements,Invoices}/`
- **Observer**: `app/Observers/TenantObserver.php`
- **Command**: `app/Console/Commands/GenerateInvoices.php`

### Database Engine
- MySQL (as specified in requirements)
- Using SQLite for development (can be changed in `.env`)

## 🎯 NEXT STEPS
- Implement Unit Tests for Reminder Command
- Configure real SMTP server for emails
- Deploy to Production

## 🚀 DEPLOYMENT NOTES

Before deploying to production:
1. Update `.env` with production database credentials
2. Set `APP_ENV=production`
3. Run `php artisan config:cache`
4. Run `php artisan route:cache`
5. Set up scheduled task for `invoices:generate` (monthly cron job)

## 📝 COMPLIANCE NOTES

**Malaysian SST Rate**: 8% (as of 2026)
**No Revert Rule**: Once a tenant is marked as SST-registered, this cannot be undone
**Audit Trail**: All SST registration dates are automatically recorded
**Wallet System**: Automatic deduction prevents manual payment errors
