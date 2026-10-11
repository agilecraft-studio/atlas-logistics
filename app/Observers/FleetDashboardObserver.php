<?php

namespace App\Observers;

use App\Services\FleetDashboardService;

/**
 * Busts the Fleet Operations Dashboard cache (all periods) whenever any of
 * the dashboard's source tables is written. Registered in AppServiceProvider.
 */
class FleetDashboardObserver
{
    public function created($model): void
    {
        FleetDashboardService::flushCache();
    }

    public function updated($model): void
    {
        FleetDashboardService::flushCache();
    }

    public function deleted($model): void
    {
        FleetDashboardService::flushCache();
    }

    public function restored($model): void
    {
        FleetDashboardService::flushCache();
    }

    public function forceDeleted($model): void
    {
        FleetDashboardService::flushCache();
    }
}
