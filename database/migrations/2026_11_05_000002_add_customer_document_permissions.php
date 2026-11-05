<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    protected array $perms = [
        'Customer documents list',
        'Customer documents add',
        'Customer documents edit',
        'Customer documents delete',
    ];

    public function up(): void
    {
        if (!Schema::hasTable('permissions') || !Schema::hasTable('roles')) {
            return;
        }

        foreach ($this->perms as $name) {
            Permission::findOrCreate($name, 'web');
        }

        foreach (['Super Admin', 'Admin', 'Finance'] as $roleName) {
            Role::where('name', $roleName)->first()?->givePermissionTo($this->perms);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        // Permissions are intentionally left in place.
    }
};
