# Global Search - Visual User Guide

## 🔍 How to Access

```
┌─────────────────────────────────────────────────────────────────┐
│  ADMIN DASHBOARD                                                │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  ┌───────────────────────────────────────────────────────────┐ │
│  │ 🔍 GLOBAL SEARCH                                          │ │
│  │                                                           │ │
│  │ Search for invoices, properties, or tenants to view      │ │
│  │ detailed account reports                                 │ │
│  │                                                           │ │
│  │ ┌─────────────────────────────────────────┐  ┌────────┐  │ │
│  │ │ Search by invoice number, property...   │  │ Search │  │ │
│  │ └─────────────────────────────────────────┘  └────────┘  │ │
│  │                                                           │ │
│  │ 📄 Invoice Search  🏠 Property Search  👤 Tenant Search   │ │
│  └───────────────────────────────────────────────────────────┘ │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

## 📊 Report Examples

### 1️⃣ INVOICE REPORT

**Search**: "INV-2024-001"

```
╔═══════════════════════════════════════════════════════════════╗
║                     INVOICE DETAILS REPORT                    ║
╠═══════════════════════════════════════════════════════════════╣
║                                                               ║
║  Invoice Number: INV-2024-001                                 ║
║  Type: Received                    Date: 15 Jan 2024          ║
║  Status: [PAID]                                               ║
║                                                               ║
║  ┌─────────────────────────┐  ┌─────────────────────────┐    ║
║  │ TENANT                  │  │ PROPERTY                │    ║
║  │ John Doe                │  │ Apartment A-101         │    ║
║  │ john@example.com        │  │ Lot 123                 │    ║
║  └─────────────────────────┘  └─────────────────────────┘    ║
║                                                               ║
║  ┌───────────────────────────────────────────────────────┐   ║
║  │ AMOUNT BREAKDOWN                                      │   ║
║  ├───────────────────────────────────────────────────────┤   ║
║  │ Base Amount                          RM 1,000.00      │   ║
║  │ SST (8%)                             RM    80.00      │   ║
║  │ Late Fee                             RM     0.00      │   ║
║  ├───────────────────────────────────────────────────────┤   ║
║  │ TOTAL AMOUNT                         RM 1,080.00      │   ║
║  └───────────────────────────────────────────────────────┘   ║
║                                                               ║
║  [View Full Invoice →]                                        ║
║                                                               ║
╚═══════════════════════════════════════════════════════════════╝
```

### 2️⃣ PROPERTY & TENANCY REPORT

**Search**: "Jalan Ampang" or "Lot 123"

```
╔═══════════════════════════════════════════════════════════════╗
║              PROPERTY & TENANCY REPORT                        ║
║              Found 2 properties matching "Jalan Ampang"       ║
╠═══════════════════════════════════════════════════════════════╣
║                                                               ║
║  PROPERTIES                                                   ║
║  ┌─────────────────────────────────────────────────────────┐ ║
║  │ 🏠 Apartment A-101                    [Occupied]        │ ║
║  │    Lot 123                                              │ ║
║  │    123 Jalan Ampang, Kuala Lumpur                       │ ║
║  │    Type: Apartment                                      │ ║
║  └─────────────────────────────────────────────────────────┘ ║
║  ┌─────────────────────────────────────────────────────────┐ ║
║  │ 🏢 Shop Lot B-05                      [Occupied]        │ ║
║  │    Lot 456                                              │ ║
║  │    456 Jalan Ampang, Kuala Lumpur                       │ ║
║  │    Type: Commercial                                     │ ║
║  └─────────────────────────────────────────────────────────┘ ║
║                                                               ║
║  TENANCY AGREEMENTS                                           ║
║  ┌─────────────────────────────────────────────────────────┐ ║
║  │ Tenant      │ Property      │ Period      │ Rent        │ ║
║  ├─────────────┼───────────────┼─────────────┼─────────────┤ ║
║  │ John Doe    │ Apartment     │ Jan-Dec     │ RM 1,000.00 │ ║
║  │             │ A-101         │ 2024        │             │ ║
║  ├─────────────┼───────────────┼─────────────┼─────────────┤ ║
║  │ ABC Sdn Bhd │ Shop Lot      │ Mar 24-     │ RM 3,500.00 │ ║
║  │             │ B-05          │ Feb 25      │             │ ║
║  └─────────────┴───────────────┴─────────────┴─────────────┘ ║
║                                                               ║
║  TENANTS IN THIS AREA                                         ║
║  ┌─────────────────────┐  ┌─────────────────────┐            ║
║  │ 👤 John Doe         │  │ 👤 ABC Sdn Bhd      │            ║
║  │ john@example.com    │  │ abc@company.com     │            ║
║  │ +60123456789        │  │ +60198765432        │            ║
║  │ [SST Registered]    │  │ [SST Registered]    │            ║
║  └─────────────────────┘  └─────────────────────┘            ║
║                                                               ║
╚═══════════════════════════════════════════════════════════════╝
```

### 3️⃣ TENANT ACCOUNT STATEMENT

**Search**: "John Doe" or "john@example.com" or "+60123456789"

```
╔═══════════════════════════════════════════════════════════════╗
║                  TENANT ACCOUNT STATEMENT                     ║
╠═══════════════════════════════════════════════════════════════╣
║                                                               ║
║  Tenant: John Doe                                             ║
║  Email: john@example.com                                      ║
║  Phone: +60123456789                                          ║
║  SST Status: [Registered]                                     ║
║                                                               ║
║  ┌──────────────┬──────────────────┬──────────────────┐       ║
║  │ Wallet       │ Total            │ Total Paid       │       ║
║  │ Balance      │ Outstanding      │                  │       ║
║  ├──────────────┼──────────────────┼──────────────────┤       ║
║  │ RM 5,000.00  │ RM 2,160.00      │ RM 12,000.00     │       ║
║  └──────────────┴──────────────────┴──────────────────┘       ║
║                                                               ║
║  ASSOCIATED PROPERTIES                                        ║
║  ┌─────────────────────┐  ┌─────────────────────┐            ║
║  │ 🏠 Apartment A-101  │  │ 🏢 Shop Lot C-03    │            ║
║  │ Lot 123             │  │ Lot 789             │            ║
║  │ Jalan Ampang, KL    │  │ Jalan Sultan, KL    │            ║
║  │ Apartment|Occupied  │  │ Commercial|Occupied │            ║
║  └─────────────────────┘  └─────────────────────┘            ║
║                                                               ║
║  INVOICE HISTORY                                              ║
║                                                               ║
║  🔴 OVERDUE INVOICES (2)                                      ║
║  ┌─────────────────────────────────────────────────────────┐ ║
║  │ Invoice#  │ Date    │ Property      │ Type │ Amount    │ ║
║  ├───────────┼─────────┼───────────────┼──────┼───────────┤ ║
║  │ INV-001   │ 15 Jan  │ Apartment     │ Recv │ 1,080.00  │ ║
║  │           │         │ A-101         │      │           │ ║
║  ├───────────┼─────────┼───────────────┼──────┼───────────┤ ║
║  │ INV-002   │ 15 Feb  │ Apartment     │ Recv │ 1,080.00  │ ║
║  │           │         │ A-101         │      │           │ ║
║  ├───────────┴─────────┴───────────────┴──────┼───────────┤ ║
║  │                              Subtotal:     │ 2,160.00  │ ║
║  └────────────────────────────────────────────┴───────────┘ ║
║                                                               ║
║  🟡 PENDING INVOICES (1)                                      ║
║  ┌─────────────────────────────────────────────────────────┐ ║
║  │ Invoice#  │ Date    │ Property      │ Type │ Amount    │ ║
║  ├───────────┼─────────┼───────────────┼──────┼───────────┤ ║
║  │ INV-003   │ 15 Mar  │ Shop Lot      │ Recv │ 2,700.00  │ ║
║  │           │         │ C-03          │      │           │ ║
║  ├───────────┴─────────┴───────────────┴──────┼───────────┤ ║
║  │                              Subtotal:     │ 2,700.00  │ ║
║  └────────────────────────────────────────────┴───────────┘ ║
║                                                               ║
║  🟢 PAID INVOICES (10)                                        ║
║  ┌─────────────────────────────────────────────────────────┐ ║
║  │ Invoice#  │ Date    │ Property      │ Type │ Amount    │ ║
║  ├───────────┼─────────┼───────────────┼──────┼───────────┤ ║
║  │ INV-004   │ 15 Apr  │ Apartment     │ Recv │ 1,080.00  │ ║
║  │           │         │ A-101         │      │           │ ║
║  ├───────────┼─────────┼───────────────┼──────┼───────────┤ ║
║  │ INV-005   │ 15 May  │ Apartment     │ Recv │ 1,080.00  │ ║
║  │           │         │ A-101         │      │           │ ║
║  ├───────────┴─────────┴───────────────┴──────┼───────────┤ ║
║  │ ... (8 more invoices)                      │           │ ║
║  ├────────────────────────────────────────────┼───────────┤ ║
║  │                              Subtotal:     │12,000.00  │ ║
║  └────────────────────────────────────────────┴───────────┘ ║
║                                                               ║
╚═══════════════════════════════════════════════════════════════╝
```

## 🎯 Quick Reference

| What You Want | What to Search | What You Get |
|--------------|----------------|--------------|
| Invoice details | Invoice number (e.g., "INV-2024-001") | Complete invoice report with breakdown |
| Properties in area | Address (e.g., "Jalan Ampang") | All properties + agreements + tenants |
| Property by lot | Lot number (e.g., "Lot 123") | Property details + agreements + tenants |
| Tenant account | Name (e.g., "John Doe") | Full account statement |
| Tenant by email | Email (e.g., "john@example.com") | Full account statement |
| Tenant by phone | Phone (e.g., "+60123456789") | Full account statement |

## 🎨 Color Legend

- 🟢 **Green** = Paid, Active, Occupied
- 🟡 **Yellow** = Pending
- 🔴 **Red** = Overdue
- 🔵 **Blue** = SST Registered, Wallet Balance
- ⚪ **Gray** = Inactive, Void

## 📱 Features

✅ **Responsive Design** - Works on desktop, tablet, and mobile
✅ **Dark Mode Support** - Comfortable viewing in any lighting
✅ **Print Ready** - Professional format for printing or PDF export
✅ **Real-time Data** - Always shows current information
✅ **Smart Detection** - Automatically determines what you're searching for
✅ **Comprehensive** - Shows all related information in one view

## 🚀 Getting Started

1. **Go to Dashboard**
   - Navigate to `/admin`
   
2. **Find the Search Widget**
   - Located at the top of the dashboard
   
3. **Enter Your Search**
   - Type invoice number, property address, or tenant name
   
4. **View Report**
   - Click "Search" to see the formatted report
   
5. **Take Action**
   - Click links to edit records or view more details

## 💡 Pro Tips

- Search is **case-insensitive** - "john doe" = "John Doe"
- Use **partial matches** - "Jalan" will find "Jalan Ampang"
- **Lot numbers** work great for property searches
- **Email or phone** works for tenant searches
- Reports are **print-friendly** - use browser print function

---

**Need Help?** Check the documentation files:
- `GLOBAL_SEARCH_ACCOUNTS_REPORT.md` - Complete user guide
- `GLOBAL_SEARCH_IMPLEMENTATION_SUMMARY.md` - Technical details
