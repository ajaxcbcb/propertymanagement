# Invoice Search - Redirect to Payments Tab with Filter

## ✅ UPDATED IMPLEMENTATION

When you search for a **receipt number** or **invoice** in the global search and click on the result, you will now be redirected to the **Payments tab** (not Account Statements) with the tenant filter automatically applied.

## 🎯 What Changed

### Previous Behavior
- Searching for invoice → Redirected to **Account Statements** page
- Showed full account statement for the tenant

### New Behavior  
- Searching for invoice → Redirects to **Payments tab**
- Shows filtered list of payments for that tenant
- User can see all payments without the detailed account statement view

## 📝 Files Modified

### 1. InvoiceResource.php
**Path:** `app/Filament/Resources/Invoices/InvoiceResource.php`

**Change:**
```php
public static function getGlobalSearchResultUrl(\Illuminate\Database\Eloquent\Model $record): ?string
{
    // Redirect to Payments list filtered by tenant
    if ($record->tenant_id) {
        return static::getUrl('index', [
            'tenant' => $record->tenant_id,
        ]);
    }
    
    // If no tenant (e.g., expenditure), redirect to payments list
    return static::getUrl('index');
}
```

**Impact:**
- Now redirects to `/admin/invoices?tenant=X` instead of Account Statements
- Shows the Payments list page with tenant filter applied

### 2. InvoicesTable.php
**Path:** `app/Filament/Resources/Invoices/Tables/InvoicesTable.php`

**Change:** Added tenant filter
```php
->filters([
    \Filament\Tables\Filters\SelectFilter::make('type')
        ->options([
            'received' => 'Received',
            'expenditure' => 'Expenses',
        ]),
    \Filament\Tables\Filters\SelectFilter::make('tenant_id')
        ->label('Tenant')
        ->relationship('tenant', 'name')
        ->searchable()
        ->preload(),
])
```

**Impact:**
- Tenant filter is now available in the Payments table
- Can be applied via URL parameter

### 3. ListInvoices.php
**Path:** `app/Filament/Resources/Invoices/Pages/ListInvoices.php`

**Change:** Added mount method
```php
public function mount(): void
{
    parent::mount();
    
    // Check for tenant filter from global search
    $tenantId = request()->query('tenant');
    
    if ($tenantId) {
        $this->tableFilters = [
            'tenant_id' => ['value' => $tenantId],
        ];
    }
}
```

**Impact:**
- Detects `?tenant=X` parameter from URL
- Automatically applies tenant filter when page loads

## 🔄 How It Works

### Complete Flow

```
User searches "REC-S1-1" in global search
    ↓
Search results show invoice details
    ↓
User clicks on invoice result
    ↓
System detects invoice has tenant_id = 1
    ↓
Redirects to: /admin/invoices?tenant=1
    ↓
ListInvoices page loads
    ↓
mount() method detects ?tenant=1 parameter
    ↓
Applies tenant_id filter automatically
    ↓
User sees Payments tab filtered to show only that tenant's payments
```

## 🧪 How to Test

### Test 1: Search for Receipt Number

1. Go to `http://127.0.0.1:8888/admin`
2. Click global search (🔍)
3. Type: **"REC-S1-1"**
4. Click on the invoice result
5. **Expected Result:**
   - Redirected to Payments tab (`/admin/invoices`)
   - URL contains `?tenant=1`
   - Tenant filter is automatically applied
   - Shows only payments for "Tenant One (Good)"

### Test 2: Direct URL Test

```
http://127.0.0.1:8888/admin/invoices?tenant=1
```

This should show the Payments page filtered to Tenant ID 1.

### Test 3: Search for Expenditure Invoice

1. Search for an expenditure invoice (one without a tenant)
2. Click on the result
3. **Expected Result:**
   - Redirected to Payments tab
   - No filter applied (shows all payments)

## ✅ Success Indicators

When working correctly:

### URL Indicators
- ✅ URL is `/admin/invoices?tenant=X`
- ✅ NOT `/admin/account-statements/account-overviews`

### Visual Indicators
- ✅ You're on the "Payments" page (Finance menu)
- ✅ Filter indicator shows "Filters (1 active)" or similar
- ✅ Tenant filter is visible and applied

### Data Indicators
- ✅ Table shows only that tenant's payments
- ✅ Can see both received and expenditure payments (if type filter not applied)
- ✅ Record count is reduced (filtered)

### Interaction Indicators
- ✅ You can clear the tenant filter
- ✅ You can add additional filters (type, etc.)
- ✅ You can edit payments from this view

## 📊 Available Filters

The Payments tab now has two filters:

| Filter | Options | Purpose |
|--------|---------|---------|
| **Type** | Received, Expenses | Filter by payment type |
| **Tenant** | All tenants (searchable) | Filter by tenant |

You can combine both filters, for example:
- Type = Received + Tenant = Tenant One
- Shows only received payments from Tenant One

## 🎨 Comparison

### Before (Account Statements)

```
Search "REC-S1-1"
    ↓
Click result
    ↓
Account Statements page
    ↓
Shows:
- Tenant details
- Property details  
- Full transaction history
- Charges, payments, late fees
- Balance calculations
```

### After (Payments Tab)

```
Search "REC-S1-1"
    ↓
Click result
    ↓
Payments tab
    ↓
Shows:
- List of payments
- Filtered to that tenant
- Type, amount, date, status
- Can edit payments directly
```

## 💡 Benefits

1. **More Relevant** - Shows payments list instead of full account statement
2. **Faster Access** - Direct access to payment records
3. **Better Context** - See all payments for that tenant in one view
4. **Easy Editing** - Can edit payments directly from the filtered list
5. **Flexible Filtering** - Can add more filters (type, date, etc.)

## 🔍 What You'll See

### Payments Tab with Tenant Filter Applied

```
┌────────────────────────────────────────────────────────────────┐
│ Payments                                  🔽 Filters (1 active)│
│ ────────────────────────────────────────────────────────────── │
│ Active Filters:                                                │
│ • Tenant: Tenant One (Good) [X]                                │
│ ────────────────────────────────────────────────────────────── │
│ Type    │ Tenant      │ Receipt No. │ Date       │ Total      │
│ ────────────────────────────────────────────────────────────── │
│ Received│ Tenant One  │ REC-S1-1    │ 2024-01-01 │ RM 5,400  │
│ Received│ Tenant One  │ REC-S1-2    │ 2024-02-01 │ RM 5,400  │
│ Received│ Tenant One  │ REC-S1-3    │ 2024-03-01 │ RM 5,400  │
│ ────────────────────────────────────────────────────────────── │
│ Showing 3 of 3 results (filtered)                              │
└────────────────────────────────────────────────────────────────┘
```

## 🐛 Troubleshooting

### Issue: Filter not applying

**Solutions:**
1. Clear browser cache (Ctrl+Shift+Delete)
2. Hard refresh (Ctrl+F5)
3. Check URL has `?tenant=X` parameter
4. Check browser console for errors (F12)

### Issue: Shows all payments instead of filtered

**Possible Causes:**
- URL parameter missing
- Filter not being applied by mount() method

**Solutions:**
- Check the URL in address bar
- Try direct URL: `http://127.0.0.1:8888/admin/invoices?tenant=1`

### Issue: Redirects to Account Statements instead of Payments

**Cause:** Old code still cached

**Solution:**
- Clear browser cache
- Restart PHP server
- Hard refresh the page

## 📚 Related Documentation

- **GLOBAL_SEARCH_COMPLETE_SUMMARY.md** - Overview of all search types
- **GLOBAL_SEARCH_QUICK_REF.md** - Quick reference guide

## 🎯 Summary

Now when you search for invoices/receipts:

| Search For | Redirects To | Filter Applied | Shows |
|------------|-------------|----------------|-------|
| Receipt Number | **Payments Tab** | Tenant | Filtered payment list |
| Invoice Description | **Payments Tab** | Tenant | Filtered payment list |

**Previous:** Redirected to Account Statements (full statement view)
**Current:** Redirects to Payments tab (filtered payment list) ✅

---

**Status:** ✅ IMPLEMENTED AND READY TO TEST

Invoice search now redirects to the Payments tab with tenant filter applied!

**Last Updated:** 2026-01-30
