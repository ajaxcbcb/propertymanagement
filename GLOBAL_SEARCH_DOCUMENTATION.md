# Global Search Implementation

## Overview

The global search feature has been implemented across all major resources in the Property Management System. This allows users to quickly find invoices, properties, tenants, and tenancy agreements from the top navigation bar.

## Search Capabilities

### 1. Invoice Search
**Searchable Fields:**
- Invoice Number
- Description

**Search Results Display:**
- Invoice Number (title)
- Type (Received/Expenditure)
- Total Amount (RM)
- Status (Paid/Pending/Overdue/Void)
- Tenant Name
- Property Name

**Use Case:** When you search for an invoice number (e.g., "INV-2024-001"), the system will show the matching invoice with all relevant details.

---

### 2. Property Search
**Searchable Fields:**
- Property Name
- Address
- Lot Number

**Search Results Display:**
- Property Name with Lot Number (title)
- Lot Number
- Full Address
- Property Type
- Status
- Current Tenants (if any active tenancies)

**Use Case:** When you search for a property by area/location (e.g., "Jalan Ampang" or "Lot 123"), the system will show all matching properties along with their current tenants and tenancy agreements.

---

### 3. Tenant Search
**Searchable Fields:**
- Tenant Name
- Email Address
- Phone Number

**Search Results Display:**
- Tenant Name (title)
- Email
- Phone
- SST Registration Status
- All Associated Properties (comma-separated list)
- Active Tenancies Count
- Invoice Statistics (pending and overdue counts)

**Use Case:** When you search for a tenant (e.g., "John Doe" or "john@example.com"), the system provides a comprehensive overview including:
- All properties the tenant is associated with
- Number of active tenancies
- Invoice status summary (e.g., "2 pending, 1 overdue")

---

### 4. Tenancy Agreement Search
**Searchable Fields:**
- Tenant Name (via relationship)
- Property Name (via relationship)
- Property Address (via relationship)
- Property Lot Number (via relationship)

**Search Results Display:**
- Agreement Title (Tenant @ Property)
- Tenant Name
- Property Name with Lot Number
- Agreed Rent Amount
- Agreement Period (Start - End dates)
- Status (Active/Inactive)

**Use Case:** When you search for properties in an area, tenancy agreements will also appear in the results, showing which tenants are associated with properties in that location.

---

## How to Use Global Search

1. **Access the Search Bar:** Click on the search icon or press `Cmd/Ctrl + K` in the Filament admin panel
2. **Type Your Query:** Enter any of the searchable fields mentioned above
3. **View Results:** Results are grouped by resource type (Invoices, Properties, Tenants, Agreements)
4. **Click to Navigate:** Click on any result to navigate directly to that record's detail page

---

## Search Logic Summary

### Invoice Number Search
```
Search: "INV-2024-001"
Results: Shows the specific invoice with type, amount, status, tenant, and property
```

### Property/Location Search
```
Search: "Jalan Ampang" or "Lot 123"
Results: Shows:
- Properties matching the address/lot number
- Tenancy agreements for those properties
- Current tenants associated with those properties
```

### Tenant Search
```
Search: "John Doe" or "john@example.com"
Results: Shows tenant overview with:
- Contact information
- All properties they're renting
- Active tenancy count
- Invoice statistics (pending/overdue)
```

---

## Technical Implementation

### Resources Modified
1. `InvoiceResource.php` - Added invoice number and description search
2. `PropertyResource.php` - Added property name, address, and lot number search
3. `TenantResource.php` - Added tenant name, email, and phone search with comprehensive details
4. `TenancyAgreementResource.php` - Added relationship-based search for tenant and property

### Key Methods Implemented
- `getGloballySearchableAttributes()` - Defines which fields are searchable
- `getGlobalSearchResultDetails()` - Customizes what information appears in search results
- `getGlobalSearchResultTitle()` - Defines the main title for each search result
- `getGlobalSearchEloquentQuery()` - Optimizes database queries with eager loading

### Performance Optimization
- All searches use eager loading to prevent N+1 query problems
- Relationships are pre-loaded (tenant, property, tenancyAgreements, invoices)
- Results are limited to 50 per resource by default (Filament standard)

---

## Benefits

1. **Quick Access:** Find any record instantly without navigating through multiple pages
2. **Contextual Information:** See related data at a glance (e.g., tenant's properties and invoices)
3. **Comprehensive Search:** Search across multiple fields and relationships
4. **Smart Grouping:** Results are organized by resource type for easy scanning
5. **Direct Navigation:** Click any result to go directly to the record

---

## Future Enhancements

Potential improvements for future versions:
- Add search filters by date range
- Include invoice amount range search
- Add property status filters in search
- Implement saved searches for common queries
- Add search history
