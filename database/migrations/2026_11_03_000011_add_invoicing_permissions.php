<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Invoicing permissions. Admin/Super Admin can do everything; the Finance role can raise and
 * settle invoices but is intentionally not granted unrelated fleet modules.
 */
return new class extends Migration
{
    protected array $perms = [
        'Invoices list', 'Invoices add', 'Invoices edit', 'Invoices delete',
        'Invoices issue', 'Invoices cancel', 'Invoices payments',
    ];

    public function up(): void
    {
        if (!Schema::hasTable('permissions') || !Schema::hasTable('roles')) {
            return;
        }

        foreach ($this->perms as $name) {
            Permission::findOrCreate($name, 'web');
        }

        foreach (['Super Admin', 'Admin'] as $roleName) {
            Role::where('name', $roleName)->first()?->givePermissionTo($this->perms);
        }

        $finance = Role::findOrCreate('Finance', 'web');
        $wanted = array_merge($this->perms, [
            'MasterData list', 'Trips list', 'TripExpenses list', 'LogisticsReports list',
        ]);
        $finance->givePermissionTo(Permission::whereIn('name', $wanted)->pluck('name')->all());

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        // Permissions are intentionally left in place.
    }
};
