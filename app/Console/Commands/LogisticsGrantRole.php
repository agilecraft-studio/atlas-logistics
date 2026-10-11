<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Give an existing role access to the logistics modules (the Roles screen does not list them yet).
 *
 *   php artisan logistics:grant-role "Fleet Manager" --modules=Trips,TripExpenses,Compliance --actions=list,add,edit
 *   php artisan logistics:grant-role "Accountant"    --modules=TripExpenses,Advances,LogisticsReports --actions=list --special=TripExpenses\ approve
 *   php artisan logistics:grant-role "Fleet Manager" --revoke   (removes all logistics permissions)
 */
class LogisticsGrantRole extends Command
{
    protected $signature = 'logistics:grant-role {role : Existing role name}
        {--modules=* : Comma list. Default: all}
        {--actions=list,add,edit : Comma list of list,add,edit,delete}
        {--special=* : Extra permissions e.g. "Trips start", "Trips override", "TripExpenses approve"}
        {--revoke : Remove every logistics permission from the role}';

    protected $description = 'Grant or revoke logistics module permissions for a role';

    protected array $all = ['Trips', 'TripExpenses', 'Advances', 'Trailers', 'Compliance', 'FleetRegister', 'MasterData', 'LogisticsReports'];
    protected array $specials = ['Trips start', 'Trips override', 'TripExpenses approve'];

    public function handle(): int
    {
        $role = Role::where('name', $this->argument('role'))->first();
        if (!$role) {
            $this->error('Role not found: ' . $this->argument('role') . '. Existing roles: ' . Role::pluck('name')->implode(', '));
            return 1;
        }

        $actions = array_filter(array_map('trim', explode(',', implode(',', (array) $this->option('actions')))));
        $actions = $actions ?: ['list', 'add', 'edit'];
        $modules = array_filter(array_map('trim', explode(',', implode(',', (array) $this->option('modules')))));
        $modules = $modules ?: $this->all;

        if ($this->option('revoke')) {
            $names = $this->specials;
            foreach ($this->all as $m) {
                foreach (['list', 'add', 'edit', 'delete'] as $a) { $names[] = "$m $a"; }
            }
            $role->revokePermissionTo(Permission::whereIn('name', $names)->get());
            app(PermissionRegistrar::class)->forgetCachedPermissions();
            $this->info("Removed logistics permissions from {$role->name}.");
            return 0;
        }

        $bad = array_diff($modules, $this->all);
        if ($bad || array_diff($actions, ['list', 'add', 'edit', 'delete'])) {
            $this->error('Unknown module or action. Modules: ' . implode(', ', $this->all) . '. Actions: list, add, edit, delete.');
            return 1;
        }

        $names = [];
        foreach ($modules as $m) {
            foreach ($actions as $a) { $names[] = "$m $a"; }
        }
        foreach ((array) $this->option('special') as $sp) {
            if (!in_array($sp, $this->specials, true)) {
                $this->error("Unknown special permission: $sp. Allowed: " . implode(', ', $this->specials));
                return 1;
            }
            $names[] = $sp;
        }

        $perms = Permission::whereIn('name', $names)->get();
        if ($perms->count() !== count(array_unique($names))) {
            $this->error('Some permissions do not exist yet. Run: php artisan migrate');
            return 1;
        }

        $role->givePermissionTo($perms);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->info("Granted {$perms->count()} permission(s) to {$role->name}: " . implode(', ', $names));
        return 0;
    }
}
