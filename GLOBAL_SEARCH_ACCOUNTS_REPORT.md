# Global Search - Accounts Report Format

## Overview

The global search has been implemented with an **Accounts Report Format** that provides detailed, formatted reports based on what you search for. Instead of showing simple search results, the system displays comprehensive account statements and reports.

## How It Works

### Access the Search

1. **Navigate to Dashboard**: Go to your admin panel dashboard
2. **Use the Search Widget**: You'll see a prominent search bar at the top
3. **Enter Your Query**: Type invoice number, property address, tenant name, email, or phone
4. **View Report**: Click "Search" to see the formatted account report

## Search Scenarios

### 1. Invoice Number Search

**What to search**: Invoice number (e.g., "INV-2024-001")

**Report Format Displayed**:
```
┌─────────────────────────────────────────────────────────────┐
│ INVOICE DETAILS REPORT                                      │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│ Invoice Number: INV-2024-001                                │
│ Type: Received                                              │
│ Date: 15 Jan 2024                                           │
│ Status: [Paid/Pending/Overdue/Void]                         │
│                                                             │
│ Tenant: John Doe                                            │
│ Email: john@example.com                                     │
│                                                             │
│ Property: Apartment A-101                                   │
│ Lot Number: Lot 123                                         │
│                                                             │
├─────────────────────────────────────────────────────────────┤
│ AMOUNT BREAKDOWN                                            │
├─────────────────────────────────────────────────────────────┤
│ Base Amount                              RM 1,000.00        │
│ SST (8%)                                 RM    80.00        │
│ Late Fee                                 RM     0.00        │
│ ─────────────────────────────────────────────────           │
│ Total Amount                             RM 1,080.00        │
└─────────────────────────────────────────────────────────────┘
```

### 2. Property/Location Search

**What to search**: Property name, address, or lot number (e.g., "Jalan Ampang", "Lot 123")

**Report Format Displayed**:
```
┌─────────────────────────────────────────────────────────────┐
│ PROPERTY & TENANCY REPORT                                   │
│ Found 2 properties matching "Jalan Ampang"                  │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│ PROPERTIES                                                  │
│ ┌─────────────────────────────────────────────────────────┐ │
│ │ Apartment A-101                                         │ │
│ │ Lot 123                                                 │ │
│ │ 123 Jalan Ampang, Kuala Lumpur                          │ │
│ │ Type: Apartment | Status: Occupied                      │ │
│ └─────────────────────────────────────────────────────────┘ │
│ ┌─────────────────────────────────────────────────────────┐ │
│ │ Shop Lot B-05                                           │ │
│ │ Lot 456                                                 │ │
│ │ 456 Jalan Ampang, Kuala Lumpur                          │ │
│ │ Type: Commercial | Status: Occupied                     │ │
│ └─────────────────────────────────────────────────────────┘ │
│                                                             │
├─────────────────────────────────────────────────────────────┤
│ TENANCY AGREEMENTS                                          │
├─────────────────────────────────────────────────────────────┤
│ Tenant          Property        Period          Rent        │
│ John Doe        Apartment A-101 Jan-Dec 2024   RM 1,000.00  │
│ ABC Sdn Bhd     Shop Lot B-05   Mar 24-Feb 25  RM 3,500.00  │
│                                                             │
├─────────────────────────────────────────────────────────────┤
│ TENANTS IN THIS AREA                                        │
├─────────────────────────────────────────────────────────────┤
│ ┌──────────────────────┐  ┌──────────────────────┐         │
│ │ John Doe             │  │ ABC Sdn Bhd          │         │
│ │ john@example.com     │  │ abc@company.com      │         │
│ │ +60123456789         │  │ +60198765432         │         │
│ │ [SST Registered]     │  │ [SST Registered]     │         │
│ └──────────────────────┘  └──────────────────────┘         │
└─────────────────────────────────────────────────────────────┘
```

### 3. Tenant Search (Account Statement)

**What to search**: Tenant name, email, or phone (e.g., "John Doe", "john@example.com", "+60123456789")

**Report Format Displayed**:
```
┌─────────────────────────────────────────────────────────────┐
│ TENANT ACCOUNT STATEMENT                                    │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│ Tenant Name: John Doe                                       │
│ Email: john@example.com                                     │
│ Phone: +60123456789                                         │
│ SST Status: Registered                                      │
│                                                             │
│ ┌──────────────────┬──────────────────┬──────────────────┐  │
│ │ Wallet Balance   │ Total Outstanding│ Total Paid       │  │
│ │ RM 5,000.00      │ RM 2,160.00      │ RM 12,000.00     │  │
│ └──────────────────┴──────────────────┴──────────────────┘  │
│                                                             │
├─────────────────────────────────────────────────────────────┤
│ ASSOCIATED PROPERTIES                                       │
├─────────────────────────────────────────────────────────────┤
│ ┌──────────────────────┐  ┌──────────────────────┐         │
│ │ Apartment A-101      │  │ Shop Lot C-03        │         │
│ │ Lot 123              │  │ Lot 789              │         │
│ │ Jalan Ampang, KL     │  │ Jalan Sultan, KL     │         │
│ │ Apartment | Occupied │  │ Commercial | Occupied│         │
│ └──────────────────────┘  └──────────────────────┘         │
│                                                             │
├─────────────────────────────────────────────────────────────┤
│ INVOICE HISTORY                                             │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│ OVERDUE INVOICES (2)                                        │
│ ┌─────────────────────────────────────────────────────────┐ │
│ │ Invoice#    Date      Property        Type    Amount   │ │
│ │ INV-001     15 Jan    Apartment A-101 Received 1,080.00│ │
│ │ INV-002     15 Feb    Apartment A-101 Received 1,080.00│ │
│ │                                      Subtotal: 2,160.00│ │
│ └─────────────────────────────────────────────────────────┘ │
│                                                             │
│ PENDING INVOICES (1)                                        │
│ ┌─────────────────────────────────────────────────────────┐ │
│ │ Invoice#    Date      Property        Type    Amount   │ │
│ │ INV-003     15 Mar    Shop Lot C-03   Received 2,700.00│ │
│ │                                      Subtotal: 2,700.00│ │
│ └─────────────────────────────────────────────────────────┘ │
│                                                             │
│ PAID INVOICES (10)                                          │
│ ┌─────────────────────────────────────────────────────────┐ │
│ │ Invoice#    Date      Property        Type    Amount   │ │
│ │ INV-004     15 Apr    Apartment A-101 Received 1,080.00│ │
│ │ INV-005     15 May    Apartment A-101 Received 1,080.00│ │
│ │ ... (8 more invoices)                                  │ │
│ │                                     Subtotal: 12,000.00│ │
│ └─────────────────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────────┘
```

## Features

### Invoice Report
- ✅ Complete invoice details
- ✅ Tenant and property information
- ✅ Itemized amount breakdown (Base + SST + Late Fees)
- ✅ Status badges with color coding
- ✅ Direct link to edit invoice

### Property Report
- ✅ All matching properties with full details
- ✅ Complete list of tenancy agreements
- ✅ All tenants in the searched area
- ✅ Property status and type indicators
- ✅ Rent amounts and agreement periods

### Tenant Account Statement
- ✅ Complete tenant profile
- ✅ Financial summary (Wallet, Outstanding, Paid)
- ✅ All associated properties
- ✅ Invoice history grouped by status
- ✅ Subtotals for each invoice category
- ✅ Color-coded status indicators

## Visual Design

### Color Coding
- **Green**: Paid invoices, active agreements, occupied properties
- **Yellow**: Pending invoices
- **Red**: Overdue invoices
- **Blue**: SST registered, wallet balance
- **Gray**: Inactive/void items

### Report Sections
Each report is divided into clear sections with:
- Header with report title
- Summary cards with key metrics
- Detailed tables with all relevant data
- Subtotals and totals where applicable
- Action buttons for further navigation

## Files Created

1. **`app/Filament/Pages/GlobalSearchResults.php`**
   - Main page controller for search results
   - Handles search logic and data aggregation
   - Determines report type based on search query

2. **`resources/views/filament/pages/global-search-results.blade.php`**
   - Report template with three different layouts
   - Responsive design with dark mode support
   - Professional accounting report format

3. **`app/Filament/Widgets/GlobalSearchWidget.php`**
   - Dashboard widget for easy access
   - Search form with helpful descriptions

4. **`resources/views/filament/widgets/global-search-widget.blade.php`**
   - Widget view with search interface
   - Visual guides for different search types

## Usage Instructions

### For Invoice Lookup
1. Type the invoice number in the search box
2. Click "Search"
3. View the detailed invoice report with full breakdown

### For Property/Area Research
1. Type property name, address, or lot number
2. Click "Search"
3. View all properties, agreements, and tenants in that area

### For Tenant Account Review
1. Type tenant name, email, or phone number
2. Click "Search"
3. View complete account statement with:
   - All properties
   - Invoice history by status
   - Financial summary

## Benefits

✅ **Professional Format**: Reports look like formal account statements
✅ **Comprehensive Data**: All related information in one view
✅ **Easy Navigation**: Direct links to edit records
✅ **Financial Clarity**: Clear breakdown of amounts and totals
✅ **Status Visibility**: Color-coded indicators for quick assessment
✅ **Grouped Information**: Invoices organized by status for easy review
✅ **Responsive Design**: Works on desktop and mobile devices
✅ **Dark Mode Support**: Comfortable viewing in any lighting

## Next Steps

The global search is now fully functional with accounts report format. You can:

1. Access it from the dashboard
2. Search for any invoice, property, or tenant
3. View beautifully formatted reports
4. Navigate to edit pages for detailed management

The system intelligently detects what you're searching for and displays the appropriate report format automatically.
