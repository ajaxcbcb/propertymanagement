# Super Admin & Audit Log Implementation

## 1. Super Admin Account
- **Role Field**: Added `role` column to `users` table.
- **Seeder**: Created `Database\Seeders\SuperAdminSeeder` to create a default super admin.
  - Email: `superadmin@propertymanagement.com`
  - Password: `password`
- **Helper Methods**: Added `isSuperAdmin()` and `isAdmin()` to `User` model.

## 2. Access Control
- **User Management**: Created `UserResource`. Access is restricted to Super Admin only.
- **System Settings**: Restricted `SystemSettingResource` access to Super Admin only.
- **Activity Logs**: Created `ActivityLogResource` (view-only) restricted to Super Admin only.

## 3. Audit Logging (Data Logs)
- Installed `spatie/laravel-activitylog`.
- Configured logging on the following models:
  - `User`
  - `Property`
  - `Tenant`
  - `TenancyAgreement`
  - `Invoice`
  - `SystemSetting`
- Logs track changes (`created`, `updated`, `deleted`) and modified attributes.

## 4. Troubleshooting
- **Missing Sessions Table**: If you encounter `SQLSTATE[HY000]: General error: 1 no such table: sessions`, run `php artisan migrate`. A fix migration has been added.

## 5. How to Use
1. **Login as Super Admin**: Use the credentials above.
2. **Manage Users**: Go to the "Users" resource to create/edit users and assign roles (`Admin` or `Super Admin`).
3. **View Logs**: Go to "Activity Logs" to see a history of changes across the system.
4. **System Settings**: Only visible and editable by Super Admin.
