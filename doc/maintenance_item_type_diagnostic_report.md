# Maintenance Item Types: Pre-Migration Diagnostics Report

This document summarizes all code changes made to support role-based `is_default` and `business_id` logic for Maintenance Item Types. Use this report to perform your diagnostics before we run the database migration.

## 1. Database Migrations (Pending Execution)
A new migration was created but **not yet migrated**: 
#### [NEW] [2026_08_08_171610_add_is_default_and_business_id_to_maintenance_item_types_table.php](file:///run/media/md-rony-mia/2f07b954-9593-455e-a276-21daa6c5d9c3/property-management/property-management-backend/database/migrations/2026_08_08_171610_add_is_default_and_business_id_to_maintenance_item_types_table.php)
- Added `is_default` (boolean, default: false).
- Added `business_id` (foreignId, nullable, constrained to `businesses` table with `onDelete('cascade')`).

## 2. Model Updates
#### [MODIFY] [MaintenanceItemType.php](file:///run/media/md-rony-mia/2f07b954-9593-455e-a276-21daa6c5d9c3/property-management/property-management-backend/app/Models/MaintenanceItemType.php)
- Added `is_default` and `business_id` to the `$fillable` array to allow mass-assignment.
- Added `protected $hidden = ['deleted_at'];` to hide soft deletes from API JSON responses.

## 3. Controller Logic Updates
#### [MODIFY] [MaintenanceItemTypeController.php](file:///run/media/md-rony-mia/2f07b954-9593-455e-a276-21daa6c5d9c3/property-management/property-management-backend/app/Http/Controllers/MaintenanceItemTypeController.php)
Strict role checks and data-ownership isolation were added to all endpoints:

- **Auth Compliance**: Replaced `auth()->user()` with the `Auth::user()` facade and strict `/** @var \App\Models\User $authUser */` type-hinting to satisfy Project Rule #2. Added uppercase inline comments to satisfy Project Rule #4.
- **Create Endpoint**: 
  - If `superadmin`: Automatically inserts `is_default = 1` and `business_id = null`.
  - If `non-superadmin`: Automatically inserts `is_default = 0` and `business_id = Auth::user()->business_id`.
- **Update, Toggle Active, and Delete Endpoints**: 
  - `superadmin` can freely manage default data.
  - `non-superadmin` users are heavily restricted: they can ONLY modify/delete records where `is_default = 0` AND `business_id` matches their own business ID. 
- **List / Filter Endpoint (`query_filters`)**: 
  - `non-superadmin` users will automatically fetch ALL default items (`is_default = 1`) alongside the items belonging specifically to their business.
  - `superadmin` users will fetch all records without business restrictions.

## 4. Form Request Validations
#### [NEW] [MaintenanceItemTypeRequest.php](file:///run/media/md-rony-mia/2f07b954-9593-455e-a276-21daa6c5d9c3/property-management/property-management-backend/app/Http/Requests/MaintenanceItemTypeRequest.php)
- Created a single, unified Form Request class to handle both Creation and Updates, satisfying Project Rule #9.
- Uses dynamic validation: it automatically injects `id => required, numeric` if the request is a `PUT` or `PATCH` operation.
- Complex data-ownership validations (e.g., checking if the user owns the record they are trying to update) were moved strictly into the Controller where the role checks are performed.

#### [DELETE] MaintenanceItemTypeCreateRequest.php
#### [DELETE] MaintenanceItemTypeUpdateRequest.php
