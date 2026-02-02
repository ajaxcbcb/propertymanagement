# Dashboard Data Inaccuracy Fix Summary

## Date: 2026-01-29

## Issues Identified

The dashboard was displaying inaccurate data due to **mismatched database schema values**. The code was querying for values that didn't exist in the database.

### Database Schema (Actual Values)
- **Invoice Types**: `'received'`, `'expenditure'`
- **Invoice Statuses**: `'paid'`, `'pending'`, `'void'`

### Code Issues (What Was Wrong)
- **Invoice Types**: Code was using `'expense'` instead of `'expenditure'`
- **Invoice Statuses**: Code was using `'unpaid'`, `'overdue'`, `'partial'` which don't exist

## Files Fixed

### 1. **StatsOverview.php** (Dashboard Widget)
- **Line 17**: Changed status filter from `whereIn('status', ['unpaid', 'overdue', 'partial'])` to `where('status', 'pending')`
- **Line 21**: Changed type from `'expense'` to `'expenditure'`
- **Line 43**: Updated description from "Unpaid & Overdue" to "Pending Payments"

### 2. **RevenueChart.php** (Dashboard Widget)
- **Line 28**: Changed status filter from `['paid', 'partial', 'unpaid', 'overdue']` to `['paid', 'pending']`
- **Line 32**: Changed type from `'expense'` to `'expenditure'`

### 3. **RecentInvoices.php** (Dashboard Widget)
- **Lines 23-32**: Changed type from `'expense'` to `'expenditure'` in badge color and formatting
- **Lines 45-53**: Removed non-existent statuses (`'unpaid'`, `'partial'`, `'overdue'`) and kept only `'paid'`, `'pending'`, `'void'`

### 4. **InvoicesTable.php** (Invoice Resource Table)
- **Lines 23, 28**: Changed type from `'expense'` to `'expenditure'` in badge display

### 5. **InvoiceForm.php** (Invoice Resource Form)
- **Line 19**: Changed type option from `'expense'` to `'expenditure'`
- **Lines 68-69**: Updated property field visibility/required conditions
- **Lines 71, 76, 81, 87, 118, 127**: Updated all label and visibility conditions to use `'expenditure'` instead of `'expense'`

### 6. **TenantResource.php** (Tenant Resource)
- **Lines 62, 69-70**: Removed query for non-existent `'overdue'` status

## Verification Results

After fixes, the dashboard queries now return accurate data:

```
1. Total Monthly Rent: RM 344,023.00
2. Outstanding (Pending): RM 706,633.20
3. Monthly Expenses (Paid this month): RM 11,282.00
4. Total Properties: 50
5. Occupied Properties: 50
6. Occupancy Rate: 100%
```

## Impact

✅ **Dashboard widgets now display accurate data**
✅ **Invoice filtering works correctly**
✅ **Revenue chart shows correct income/expense trends**
✅ **Recent invoices widget displays proper status badges**
✅ **Global search for tenants shows accurate invoice statistics**

## Notes

- The system uses `'pending'` status for unpaid invoices, not separate `'unpaid'` and `'overdue'` statuses
- Late fees are applied to pending invoices based on the `ApplyLateFees` command
- The `'expenditure'` type is used for expenses, not `'expense'`
