<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Atlas Logistics - permissions for the new logistics modules. Granted to the "Super Admin" and
 * "Admin" roles when they exist; other roles can be given these in Roles > Edit.
 */
return new class extends Migration
{
    protected array $modules = ['Trips', 'TripExpenses', 'Advances', 'Trailers', 'Compliance', 'FleetRegister', 'MasterData', 'LogisticsReports'];
    protected array $extra = ['Trips start', 'Trips override', 'TripExpenses approve'];

    public function up(): void
    {
        if (!Schema::hasTable('permissions') || !Schema::hasTable('roles')) {
            return;
        }

        $names = $this->extra;
        foreach ($this->modules as $m) {
            foreach (['list', 'add', 'edit', 'delete'] as $a) {
                $names[] = $m . ' ' . $a;
            }
        }

        foreach ($names as $name) {
            Permission::findOrCreate($name, 'web');
        }

        foreach (['Super Admin', 'Admin'] as $roleName) {
            $role = Role::where('name', $roleName)->first();
            if ($role) {
                $role->givePermissionTo($names);
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        // Permissions are intentionally left in place.
    }
};
