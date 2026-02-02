# 🚀 Quick Start Guide - Property Management System

## ✅ System Status: READY TO USE

Your Property Management System is fully installed and tested. All features are working correctly!

---

## 🌐 Access the Admin Panel

### Option 1: Using Laravel Herd (Recommended)
Since you have Laravel Herd installed, it should automatically serve your project:

1. **Open your browser**
2. **Navigate to**: `http://localhost:8888/admin`
3. **Login with**:
   - Email: `admin@example.com`
   - Password: `password`

### Option 2: Using the Reliable Start Script (Recommended)
Since Herd networking can be tricky, use our included script:

```bash
# Open PowerShell or Command Prompt
cd d:\propertymanagement

# Run the helper script
.\serve_app.bat
```

Then navigate to: `http://localhost:8888/admin`

Then navigate to: `http://localhost:9000/admin`

---

## 📊 Test Data Already Created

The system already has sample data for testing:

### Properties (2)
- Unit 5A, Block B (Apartment, RM 1,500/month) - Occupied
- House 12, Jalan Merdeka (House, RM 2,500/month) - Occupied

### Tenants (3)
- **John Doe** (No SST, Wallet: RM 500)
- **Jane Smith** (SST Registered, Wallet: RM 2,300)
- **Ahmad Ali** (SST Registered, Wallet: RM 500)

### Tenancy Agreements (2)
- John Doe → Unit 5A
- Jane Smith → House 12

### Invoices (2)
- INV-202601-00001: John Doe, RM 1,500 (PAID)
- INV-202601-00002: Jane Smith, RM 2,700 (PAID, includes 8% SST)

---

## 🎯 What You Can Do Now

### 1. View Existing Data
Navigate through the admin panel to see:
- **Properties** - View the 2 sample properties
- **Tenants** - See the 3 tenants with their SST status
- **Tenancy Agreements** - Check the active agreements
- **Invoices** - View the generated invoices with SST calculations

### 2. Test SST Compliance
1. Go to **Tenants**
2. Click on **Jane Smith** (SST registered)
3. Try to **disable the SST toggle** - it's disabled!
4. This demonstrates the "No Revert" rule

### 3. Create New Data
Try creating:
- A new property
- A new tenant (with or without SST)
- A new tenancy agreement
- Generate new invoices

### 4. Generate Monthly Invoices
Run the billing command:
```bash
php artisan invoices:generate
```

This will create invoices for all active agreements.

---

## 🔐 SST Compliance Features

### The "No Revert" Rule
Once a tenant is marked as SST-registered:
- ✅ Toggle becomes **disabled** in the UI
- ✅ Backend throws exception if attempted via code
- ✅ Registration date is **automatically recorded**
- ✅ Cannot be changed back to non-registered

### SST Calculation
- **Rate**: 8% (Malaysian SST)
- **Applied to**: SST-registered tenants only
- **Automatic**: Calculated during invoice generation

---

## 💰 Wallet System

### How It Works
1. **Full Payment**: Wallet balance ≥ invoice amount
   - Deducts full amount
   - Marks invoice as "PAID"

2. **Partial Payment**: Wallet balance < invoice amount
   - Deducts available balance
   - Marks invoice as "PARTIAL"

3. **No Payment**: Wallet balance = 0
   - No deduction
   - Marks invoice as "UNPAID"

---

## 📝 Common Tasks

### Create a New Property
1. Go to **Properties** → **New Property**
2. Fill in: Name, Type, Base Rent, Status
3. Click **Create**

### Create a New Tenant
1. Go to **Tenants** → **New Tenant**
2. Fill in basic information
3. In **Tax Compliance** section:
   - Enable SST if needed (⚠️ Cannot be disabled later!)
   - Add wallet balance if desired
4. Click **Create**

### Create a Tenancy Agreement
1. Go to **Tenancy Agreements** → **New**
2. Select tenant and property
3. Set dates and agreed rent
4. Mark as **Active**
5. Click **Create**

### Generate Invoices
```bash
php artisan invoices:generate
```

---

## 🛠️ Useful Commands

```bash
# Start development server
php artisan serve --port=9000

# Generate invoices
php artisan invoices:generate

# Create test data
php artisan db:seed --class=TestDataSeeder

# Create new admin user
php artisan make:filament-user

# Clear cache
php artisan cache:clear

# View routes
php artisan route:list
```

---

## 📁 Important Files

### Configuration
- `.env` - Environment configuration
- `config/database.php` - Database settings

### Models
- `app/Models/Property.php`
- `app/Models/Tenant.php`
- `app/Models/TenancyAgreement.php`
- `app/Models/Invoice.php`

### Filament Resources
- `app/Filament/Resources/Properties/`
- `app/Filament/Resources/Tenants/` (SST compliance UI)
- `app/Filament/Resources/TenancyAgreements/`
- `app/Filament/Resources/Invoices/`

### Business Logic
- `app/Observers/TenantObserver.php` (SST protection)
- `app/Console/Commands/GenerateInvoices.php` (Monthly billing)

---

## 🎓 Documentation

- **README.md** - Full project documentation
- **IMPLEMENTATION_SUMMARY.md** - Implementation details
- **TEST_RESULTS.md** - Complete test results

---

## 🐛 Troubleshooting

### Can't Access Admin Panel?
1. Make sure server is running
2. Check the URL (should end with `/admin`)
3. Try clearing browser cache

### PHP Not Found?
Run this first:
```bash
$env:Path = [System.Environment]::GetEnvironmentVariable("Path","Machine") + ";" + [System.Environment]::GetEnvironmentVariable("Path","User")
```

### Database Issues?
```bash
php artisan migrate:fresh
php artisan db:seed --class=TestDataSeeder
```

---

## ✅ System Health Check

Run these to verify everything works:

```bash
# Verify test data
php verify_test.php

# Test SST rule
php test_sst_rule.php

# Generate invoices
php artisan invoices:generate
```

---

## 🎉 You're All Set!

Your Property Management System is ready to use. Just:
1. **Access the admin panel** at `http://localhost:8888/admin`
2. **Login** with admin@example.com / password
3. **Explore** the existing test data
4. **Create** your own properties and tenants
5. **Generate** invoices with the command

**Enjoy your new Property Management System!** 🚀

---

**Need Help?**
- Check the README.md for detailed documentation
- Review TEST_RESULTS.md to see what's been tested
- All features from Phases 1-4 are complete and working!
