# Property Management System

A comprehensive property management system built with Laravel 11 and FilamentPHP v5, specifically designed for the Malaysian market with full SST (Sales & Service Tax) compliance.

## 🌟 Features

### Core Functionality
- **Property Management**: Track properties with status (vacant/occupied)
- **Tenant Management**: Complete tenant profiles with contact information
- **Tenancy Agreements**: Link tenants to properties with rental terms
- **Invoice Generation**: Automated monthly billing system
- **Wallet System**: Automatic payment deduction from tenant wallets

### Malaysian SST Compliance
- **8% SST Rate**: Automatically applied to SST-registered tenants
- **"No Revert" Rule**: SST registration cannot be disabled once enabled
- **Compliance Protection**: Both UI and backend enforcement
- **Audit Trail**: Automatic recording of SST registration dates

## 🚀 Quick Start

### Prerequisites
- PHP 8.2 or higher
- Composer
- MySQL (or SQLite for development)
- Laravel Herd (recommended) or XAMPP

### Installation

1. **Clone or navigate to the project directory**
   ```bash
   cd d:\propertymanagement
   ```

2. **Install dependencies** (already done)
   ```bash
   composer install
   ```

3. **Configure environment**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Run migrations** (already done)
   ```bash
   php artisan migrate
   ```

5. **Start the server**
   ```bash
   php artisan serve
   ```

6. **Access the admin panel**
   - URL: `http://localhost:8000/admin`
   - Email: `admin@example.com`
   - Password: `password`

## 📊 Usage

### Creating a Property
1. Navigate to **Properties** in the admin panel
2. Click **New Property**
3. Fill in:
   - Name (e.g., "Unit 5A, Block B")
   - Type (e.g., "Apartment", "House")
   - Base Rent (e.g., 1500.00)
   - Status (Vacant or Occupied)

### Creating a Tenant
1. Navigate to **Tenants**
2. Click **New Tenant**
3. Fill in basic information:
   - Name
   - Email
   - Phone
   - Wallet Balance (optional)
4. **Tax Compliance Section**:
   - Enable "SST Registered" if applicable
   - ⚠️ **Warning**: Once enabled, this cannot be disabled!

### Creating a Tenancy Agreement
1. Navigate to **Tenancy Agreements**
2. Click **New Tenancy Agreement**
3. Select:
   - Tenant
   - Property
   - Start Date
   - End Date
   - Agreed Rent
   - Mark as Active

### Generating Monthly Invoices
Run the command:
```bash
php artisan invoices:generate
```

This will:
- Create invoices for all active tenancy agreements
- Add 8% SST for SST-registered tenants
- Automatically deduct from tenant wallet balances
- Set invoice status (paid/partial/unpaid)

## 🔐 SST Compliance Rules

### The "No Revert" Rule
Once a tenant is marked as SST-registered:
1. **UI Protection**: The toggle becomes disabled
2. **Backend Protection**: Observer throws exception if attempted
3. **Error Message**: "COMPLIANCE ERROR: You cannot revert an SST-registered customer."

### Automatic Date Recording
When SST registration is enabled:
- Registration date is automatically set to current date
- Date is stored for audit purposes
- Date is displayed (read-only) in the form

## 💰 Wallet & Payment Logic

### How It Works
1. **Full Payment**: If wallet balance >= invoice amount
   - Deduct full amount from wallet
   - Mark invoice as "paid"

2. **Partial Payment**: If wallet balance < invoice amount
   - Deduct available balance
   - Set wallet to 0
   - Mark invoice as "partial"

3. **No Payment**: If wallet balance = 0
   - No deduction
   - Mark invoice as "unpaid"

## 📁 Project Structure

```
app/
├── Console/Commands/
│   └── GenerateInvoices.php      # Monthly billing command
├── Filament/Resources/
│   ├── Properties/                # Property management UI
│   ├── Tenants/                   # Tenant management UI (with SST)
│   ├── TenancyAgreements/         # Agreement management UI
│   └── Invoices/                  # Invoice management UI
├── Models/
│   ├── Property.php
│   ├── Tenant.php
│   ├── TenancyAgreement.php
│   └── Invoice.php
└── Observers/
    └── TenantObserver.php         # SST compliance enforcement

database/migrations/
├── *_create_properties_table.php
├── *_create_tenants_table.php
├── *_create_tenancy_agreements_table.php
└── *_create_invoices_table.php
```

## 🛠️ Artisan Commands

### Generate Invoices
```bash
php artisan invoices:generate
```
Generates monthly invoices for all active tenancy agreements.

### Create Admin User
```bash
php artisan make:filament-user
```
Creates a new admin user for the Filament panel.

## 📝 Database Schema

### Properties
- name, type, base_rent, status (vacant/occupied)

### Tenants
- name, email, phone
- is_sst_registered, sst_registration_date
- wallet_balance

### Tenancy Agreements
- tenant_id, property_id
- start_date, end_date, agreed_rent
- is_active

### Invoices
- tenant_id, tenancy_agreement_id
- invoice_number, due_date
- amount_base, amount_sst, amount_total
- status (unpaid/paid/partial/overdue)

## 🔄 Scheduled Tasks (Future)

To automate monthly invoice generation, add to your cron:

```bash
# Run on the 1st of every month at 00:00
0 0 1 * * cd /path/to/project && php artisan invoices:generate
```

Or in Laravel's scheduler (`app/Console/Kernel.php`):
```php
$schedule->command('invoices:generate')->monthly();
```

## 🐛 Troubleshooting

### Can't access admin panel
- Ensure server is running: `php artisan serve`
- Check URL: `http://localhost:8000/admin`
- Verify admin user exists

### SST toggle not working
- Check browser console for errors
- Ensure TenantObserver is registered in AppServiceProvider
- Clear cache: `php artisan cache:clear`

### Invoices not generating
- Verify active tenancy agreements exist
- Check tenant and property relationships
- Run with verbose output: `php artisan invoices:generate -v`

## 📚 Technologies Used

- **Laravel 11**: PHP framework
- **FilamentPHP v5.1**: Admin panel builder
- **MySQL**: Database (SQLite for development)
- **PHP 8.4**: Programming language

## 📄 License

This project is built for the Malaysian property management market.

## 👨‍💻 Support

For issues or questions, refer to:
- Laravel Documentation: https://laravel.com/docs
- Filament Documentation: https://filamentphp.com/docs

---

**Built with ❤️ for Malaysian Property Management**
