<?php

namespace App\Http\Controllers\Admin\Logistics;

use App\Http\Controllers\Controller;
use App\Services\ComplianceService;

/** One screen showing everything expired or expiring: trucks, trailers, drivers, company. */
class ComplianceDashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:Compliance list');
    }

    public function index(ComplianceService $service)
    {
        $days = (int) request('days', 30);
        $days = in_array($days, [7, 14, 30, 60, 90], true) ? $days : 30;
        $type = in_array(request('type'), ['vehicle', 'trailer', 'driver', 'company'], true) ? request('type') : null;

        $all = $service->items(90);

        $items = $all->filter(fn ($i) => $i['days_left'] <= $days);
        if ($type) {
            $items = $items->where('owner_type', $type);
        }

        return view('logistics.compliance.dashboard', [
            'items'   => $items->values(),
            'days'    => $days,
            'type'    => $type,
            'counts'  => [
                'expired' => $all->where('status', 'expired')->count(),
                'critical_expired' => $all->where('status', 'expired')->where('critical', true)->count(),
                'd7'  => $all->filter(fn ($i) => $i['days_left'] >= 0 && $i['days_left'] <= 7)->count(),
                'd30' => $all->filter(fn ($i) => $i['days_left'] >= 0 && $i['days_left'] <= 30)->count(),
                'd90' => $all->filter(fn ($i) => $i['days_left'] >= 0 && $i['days_left'] <= 90)->count(),
            ],
        ]);
    }
}
