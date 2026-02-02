# PropMaster: Property Management System Documentation

## 1. Core Modules

### **Tenant Management**
*   **Profile Storage:** Stores comprehensive tenant details (Name, IC/Passport, Contact, Emergency Contacts).
*   **Wallet system:** Each tenant has a virtual wallet. Surplus payments (overpayments) are automatically credited to the wallet for future rent offsets.
*   **SST Tracking:** Captures SST registration dates for tenants who are registered businesses, facilitating tax reporting.

### **Property Management**
*   **Asset Inventory:** Tracks property inventory with details like Lot Number, Address, and Base Rent.
*   **Status Tracking:** Properties are categorized as `vacant`, `occupied`, `under_maintenance`, or `reserved`.
*   **Rent Standardization:** Sets a `base_rent` per property which acts as the default for new tenancy agreements.

### **Tenancy Agreements**
*   **Smart Selection:** When creating an agreement, the system only shows `vacant` properties and automatically fetches their `base_rent`.
*   **Lifecycle Automation:** 
    *   **Auto-Status:** The `is_active` status is automatically managed based on the current date relative to the `start_date` and `end_date`.
    *   **Validation:** Prevents more than one `active` agreement for the same property.
    *   **Renewal Logic:** System encourages creating new records for renewals rather than extending old ones to maintain a clean historical audit trail.

---

## 2. Financial & Billing Engine

### **Advanced Invoice System**
*   **Categorization:** 
    *   `Received` (Income): Rent, Utilities, Late Fees.
    *   `Expenditure` (Expenses): Repairs, Taxes, Maintenance costs.
*   **Tax Integration (SST):** Support for calculating SST on top of base rent where applicable, with snapshots of the tax rate at the time of billing.
*   **Status Workflow:** `pending` -> `partial` -> `paid`. Also supports `void` for cancelled bills.

### **Payment Offset Logic (FIFO - First In, First Out)**
*   **Automated Allocation:** When a payment is recorded, the `PaymentOffsetService` automatically applies the amount to the **oldest outstanding invoices first**.
*   **Balance Reduction:** If a payment is less than the total debt, it marks the invoice as `partial` and updates the remaining balance.
*   **Zero-Debt Surplus:** Any payment exceeding the total outstanding debt is automatically credited to the Tenant's Wallet for future logic to auto-apply.

### **Late Fee Automation**
*   **Dynamic Calculation:** System identifies overdue invoices and applies late fees based on defined rates.
*   **Grace Periods:** Considers the due date and only applies fees after the grace period ends.
*   **Safety Rules:** Late fees are never applied to `paid` or `void` invoices.

---

## 3. Automation & System Tasks (Artisan Commands)

The system includes several "Cleaners" and "Workers" that should be scheduled locally:
*   `php artisan invoices:generate`: Scans active agreements and creates the monthly rent invoices.
*   `php artisan late-fees:apply`: Daily task to check for overdue payments and penalize them.
*   `php artisan tenancy:check-expiry`: Daily task to automatically toggle the `is_active` flag for agreements that have expired (passed their end date).
*   `php artisan payments:process-offsets`: Manually triggers the wallet-to-invoice allocation for all tenants.

---

## 4. Reporting & User Interface

### **Dashboard (Live Stats)**
*   **Total Monthly Rent:** View potential income from all active agreements.
*   **Last Month's Expenses:** Real-time tracking of outflows.
*   **Outstanding Balance:** Current month's net outstanding (Total Potential minus Received Payments).
*   **Occupancy Rate:** Percentage of properties successfully rented.

### **Tenant Account Statements**
*   **Professional Ledger:** A redesigned view for each tenant showing every transaction (Rent, Late Fees, Payments).
*   **Running Balance:** Real-time calculation of account health (Overdue in red, Credit in green).
*   **Print-ready:** Fully optimized for PDF export and formal printing.

### **SST Reporting**
*   **CSV Export:** A dedicated tool within the Invoices section to export all SST-taxable income for a specific month, specifically formatted for tax filing.

---

## 5. Key Business Rules & Validations

1.  **Strict Property Exclusivity:** A property **cannot** have more than one active tenancy agreement.
2.  **Date-Driven Integrity:** A tenancy agreement's status is strictly controlled by its date range; manual overrides are disabled.
3.  **Financial FIFO:** Payments must always offset the oldest debt first to ensure accurate aging reports.
4.  **Wallet Priority:** Surplus money is always stored in the wallet and cannot be "lost" or unallocated.

---

## 6. Technology Stack & Branding
*   **Stack:** Laravel 11 + Filament v3.
*   **Branding:** Custom-branded as **PropMaster** with dedicated Light/Dark mode SVG logos.
*   **Architecture:** Utilizes Service Classes and Eloquent Observers to separate business logic from the UI.
