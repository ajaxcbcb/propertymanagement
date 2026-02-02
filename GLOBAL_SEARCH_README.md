# 🔍 Global Search Integration

## Overview

The global search now seamlessly integrates with the **Account Statement** module. Instead of a separate report page, searching for any entity redirects you to the comprehensive Account Statement overview, pre-filtered for your search term.

## 🎯 How It Works

### 1. Search
Use the Global Search bar (Cmd+K / Ctrl+K) to search for:
- **Invoice Number**: e.g. `INV-2024-001`
- **Property**: e.g. `Jalan Ampang` or `Lot 123`
- **Tenant**: e.g. `John Doe`

### 2. Select
Click on a result in the dropdown.

### 3. View Statement
You will be redirected to the **Account Statements** page. 
- The search term (e.g. Tenant Name, Property Name) will be automatically applied as a filter.
- You will see the specific account overview, balance, and transaction history for that entity.

## ✅ Benefits
- **Unified View**: All financial data is centralizes in the Account Statement view.
- **Consistent Filters**: Uses the same robust filtering logic as the main reports.
- **Quick Access**: Jump straight to a tenant's financial standing from anywhere in the app.

## 🛠 Technical Details
- **Invoice Search**: Redirects using the associated *Tenant Name*.
- **Property Search**: Redirects using the *Property Name*.
- **Tenant Search**: Redirects using the *Tenant Name*.
