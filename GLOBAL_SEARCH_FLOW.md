# Global Search Logic Flow

## Search Query Processing

```
User Input → Global Search → Resource Matching → Results Display
```

## Detailed Flow Diagrams

### 1. Invoice Number Search
```
┌─────────────────────────────────────────────────────────────┐
│ User searches: "INV-2024-001"                               │
└────────────────────┬────────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────────┐
│ InvoiceResource.getGloballySearchableAttributes()           │
│ - Searches: invoice_number, description                     │
└────────────────────┬────────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────────┐
│ Match Found: Invoice #INV-2024-001                          │
│ Eager loads: tenant, property, tenancyAgreement             │
└────────────────────┬────────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────────┐
│ Display Result:                                             │
│ ┌─────────────────────────────────────────────────────────┐ │
│ │ 📄 INV-2024-001                                         │ │
│ │ Type: Received                                          │ │
│ │ Amount: RM 1,080.00                                     │ │
│ │ Status: Paid                                            │ │
│ │ Tenant: John Doe                                        │ │
│ │ Property: Apartment A-101                               │ │
│ └─────────────────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────────┘
```

### 2. Property/Location Search
```
┌─────────────────────────────────────────────────────────────┐
│ User searches: "Jalan Ampang"                               │
└────────────────────┬────────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────────┐
│ PropertyResource.getGloballySearchableAttributes()          │
│ - Searches: name, address, lot_number                       │
└────────────────────┬────────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────────┐
│ Matches Found: 2 Properties                                 │
│ Eager loads: tenancyAgreements.tenant                       │
└────────────────────┬────────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────────┐
│ TenancyAgreementResource.getGloballySearchableAttributes()  │
│ - Searches: property.address (via relationship)             │
└────────────────────┬────────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────────┐
│ Matches Found: 3 Tenancy Agreements                         │
│ Eager loads: tenant, property                               │
└────────────────────┬────────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────────┐
│ Display Results (Grouped):                                  │
│                                                              │
│ PROPERTIES (2)                                              │
│ ┌─────────────────────────────────────────────────────────┐ │
│ │ 🏠 Apartment A-101 (Lot 123)                            │ │
│ │ Address: 123 Jalan Ampang, KL                           │ │
│ │ Type: Apartment                                         │ │
│ │ Current Tenants: John Doe                               │ │
│ └─────────────────────────────────────────────────────────┘ │
│ ┌─────────────────────────────────────────────────────────┐ │
│ │ 🏠 Shop Lot B-05 (Lot 456)                              │ │
│ │ Address: 456 Jalan Ampang, KL                           │ │
│ │ Type: Commercial                                        │ │
│ │ Current Tenants: ABC Sdn Bhd                            │ │
│ └─────────────────────────────────────────────────────────┘ │
│                                                              │
│ TENANCY AGREEMENTS (3)                                      │
│ ┌─────────────────────────────────────────────────────────┐ │
│ │ 📋 Agreement: John Doe @ Apartment A-101                │ │
│ │ Rent: RM 1,000.00                                       │ │
│ │ Period: Jan 2024 - Dec 2024                             │ │
│ │ Status: Active                                          │ │
│ └─────────────────────────────────────────────────────────┘ │
│ ... (2 more agreements)                                     │
└─────────────────────────────────────────────────────────────┘
```

### 3. Tenant Search
```
┌─────────────────────────────────────────────────────────────┐
│ User searches: "John Doe" or "john@example.com"             │
└────────────────────┬────────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────────┐
│ TenantResource.getGloballySearchableAttributes()            │
│ - Searches: name, email, phone                              │
└────────────────────┬────────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────────┐
│ Match Found: John Doe                                       │
│ Eager loads: tenancyAgreements.property, invoices           │
└────────────────────┬────────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────────┐
│ Aggregates Related Data:                                    │
│ - Gets all properties via tenancy agreements                │
│ - Counts active tenancies                                   │
│ - Calculates invoice statistics (pending/overdue)           │
└────────────────────┬────────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────────┐
│ Display Result:                                             │
│ ┌─────────────────────────────────────────────────────────┐ │
│ │ 👤 John Doe                                             │ │
│ │ Email: john@example.com                                 │ │
│ │ Phone: +60123456789                                     │ │
│ │ SST Registered: Yes                                     │ │
│ │ Properties: Apartment A-101, Shop Lot C-03              │ │
│ │ Active Tenancies: 2                                     │ │
│ │ Invoices: 1 pending, 2 overdue                          │ │
│ └─────────────────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────────┘
```

## Search Attribute Mapping

### Invoice Resource
| Field | Searchable | Displayed in Results |
|-------|-----------|---------------------|
| invoice_number | ✅ Yes | ✅ Yes (Title) |
| description | ✅ Yes | ❌ No |
| type | ❌ No | ✅ Yes |
| amount_total | ❌ No | ✅ Yes |
| status | ❌ No | ✅ Yes |
| tenant.name | ❌ No | ✅ Yes (if exists) |
| property.name | ❌ No | ✅ Yes (if exists) |

### Property Resource
| Field | Searchable | Displayed in Results |
|-------|-----------|---------------------|
| name | ✅ Yes | ✅ Yes (Title) |
| address | ✅ Yes | ✅ Yes |
| lot_number | ✅ Yes | ✅ Yes (Title) |
| type | ❌ No | ✅ Yes |
| status | ❌ No | ✅ Yes |
| current_tenants | ❌ No | ✅ Yes (calculated) |

### Tenant Resource
| Field | Searchable | Displayed in Results |
|-------|-----------|---------------------|
| name | ✅ Yes | ✅ Yes (Title) |
| email | ✅ Yes | ✅ Yes |
| phone | ✅ Yes | ✅ Yes |
| is_sst_registered | ❌ No | ✅ Yes |
| properties | ❌ No | ✅ Yes (calculated) |
| active_tenancies | ❌ No | ✅ Yes (calculated) |
| invoice_stats | ❌ No | ✅ Yes (calculated) |

### Tenancy Agreement Resource
| Field | Searchable | Displayed in Results |
|-------|-----------|---------------------|
| tenant.name | ✅ Yes (relationship) | ✅ Yes |
| property.name | ✅ Yes (relationship) | ✅ Yes (Title) |
| property.address | ✅ Yes (relationship) | ❌ No |
| property.lot_number | ✅ Yes (relationship) | ✅ Yes |
| agreed_rent | ❌ No | ✅ Yes |
| start_date | ❌ No | ✅ Yes |
| end_date | ❌ No | ✅ Yes |
| is_active | ❌ No | ✅ Yes |

## Performance Considerations

### Eager Loading Strategy
```php
// Invoice Search
->with(['tenant', 'property', 'tenancyAgreement'])

// Property Search
->with(['tenancyAgreements.tenant'])

// Tenant Search
->with(['tenancyAgreements.property', 'invoices'])

// Tenancy Agreement Search
->with(['tenant', 'property'])
```

### Query Optimization
- **Limit**: 50 results per resource (Filament default)
- **Indexing**: Database indexes on searchable columns recommended
- **Caching**: Consider implementing query caching for frequently searched terms

## Use Cases Summary

| Search Query | Primary Results | Secondary Results |
|-------------|----------------|-------------------|
| Invoice Number | Invoice details | - |
| Property Address | Properties | Tenancy Agreements |
| Lot Number | Properties | Tenancy Agreements |
| Tenant Name | Tenant overview | Tenancy Agreements |
| Tenant Email | Tenant overview | - |
| Tenant Phone | Tenant overview | - |

## Implementation Files

- `app/Filament/Resources/Invoices/InvoiceResource.php`
- `app/Filament/Resources/Properties/PropertyResource.php`
- `app/Filament/Resources/Tenants/TenantResource.php`
- `app/Filament/Resources/TenancyAgreements/TenancyAgreementResource.php`
