<?php

namespace App\Http\Controllers\Admin\Logistics;

use App\Http\Controllers\Controller;
use App\Services\FleetDashboardService;
use Illuminate\Http\Request;

/**
 * Fleet Operations Dashboard.
 *
 * NOTE: The application has no HomeController (removed), so the dashboard controller
 * extends the application base Controller instead.
 */
class FleetDashboardController extends Controller
{
    public function __construct(
        protected readonly FleetDashboardService $dashboardService
    ) {
    }

    /**
     * Show the fleet operations dashboard.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $period = $request->query('period', 'month');

        $valid = ['month', 'week', 'year'];
        if (!
            in_array($period, $valid, true)
        ) {
            $period = 'month';
        }

        $dashboard = $this->dashboardService->data($period);

        return view('fleet.dashboard.index', [
            'dashboard' => $dashboard,
            'currentPeriod' => $period,
        ]);
    }
}
