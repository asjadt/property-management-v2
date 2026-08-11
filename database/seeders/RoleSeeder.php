<?php

namespace Database\Seeders;

use App\Http\Controllers\SetUpController;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Reuses the existing logic in SetUpController to sync config/setup-config.php with DB
     */
    public function run(): void
    {
        // RESET CACHED ROLES AND PERMISSIONS
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = config("setup-config.permissions");

        // setup permissions
        foreach ($permissions as $permission) {
            \Spatie\Permission\Models\Permission::updateOrCreate(
                ['name' => $permission, 'guard_name' => 'api']
            );
        }
        
        // setup roles
        $roles = config("setup-config.roles");
        foreach ($roles as $role) {
            \Spatie\Permission\Models\Role::updateOrCreate(
                [
                    'name' => $role,
                    'guard_name' => 'api'
                ],
                [
                    "is_system_default" => 1,
                    "business_id" => NULL,
                    "is_default" => 1,
                    "is_default_for_business" => (in_array($role, [
                        "business_experts",
                        "business_receptionist"
                    ]) ? 1 : 0)
                ]
            );
        }

        // setup roles and permissions
        $role_permissions = config("setup-config.roles_permission");
        foreach ($role_permissions as $role_permission) {
            $role = \Spatie\Permission\Models\Role::where(["name" => $role_permission["role"]])->first();

            $permissions = $role_permission["permissions"];

            // Get current permissions associated with the role
            $currentPermissions = $role->permissions()->pluck('name')->toArray();

            // Determine permissions to remove
            $permissionsToRemove = array_diff($currentPermissions, $permissions);

            // Deassign permissions not included in the configuration
            if (!empty($permissionsToRemove)) {
                foreach ($permissionsToRemove as $permission) {
                    $role->revokePermissionTo($permission);
                }
            }

            // Assign permissions from the configuration
            $role->syncPermissions($permissions);
        }

        $this->command->info('Roles and permissions synced successfully from config/setup-config.php');
    }
}
