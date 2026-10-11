<?php
declare(strict_types=1);

namespace App\Http\Controllers\Admin\Logistics;

use App\Http\Controllers\Controller;
use App\Model\Trip;
use App\Services\CurrencyService;
use App\Services\TripFinanceService;
use Illuminate\Http\Request;

class TripReportsController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:LogisticsReports list');
    }

    public function profitability(Request $request, TripFinanceService $finance)
    {
        $request->validate([
            'from'   => 'nullable|date',
            'to'     => 'nullable|date|after_or_equal:from',
            'status' => 'nullable|in:completed,closed,both',
        ]);

        $from = $request->query('from', now()->startOfMonth()->toDateString());
        $to = $request->query('to', now()->toDateString());
        $status = $request->query('status', 'both');

        $trips = Trip::with(['vehicle', 'driver', 'customer', 'expenses.category', 'advances', 'incidents'])
            ->whereIn('status', $status === 'both' ? ['completed', 'closed'] : [$status])
            ->whereDate('actual_start_at', '>=', $from)
            ->whereDate('actual_start_at', '<=', $to)
            ->orderByDesc('actual_start_at')
            ->get();

        $rows = $trips->map(function (Trip $t) use ($finance) {
            $s = $finance->summary($t);
            return [
                'id' => $t->id, 
                'number' => $t->trip_number, 
                'route' => $t->route_label,
                'truck' => optional($t->vehicle)->license_plate, 
                'truck_id' => $t->vehicle_id,
                'driver' => optional($t->driver)->name, 
                'driver_id' => $t->driver_id,
                'customer' => optional($t->customer)->name, 
                'status' => config('logistics.trip_statuses.' . $t->status, $t->status),
                'km' => (float) $t->distance_km, 
                's' => $s,
            ];
        });

        $total = [
            'revenue' => $rows->sum(fn ($r) => $r['s']['revenue']),
            'cost'    => $rows->sum(fn ($r) => $r['s']['total_cost']),
            'profit'  => $rows->sum(fn ($r) => $r['s']['profit']),
            'km'      => $rows->sum('km'),
            'pending' => $rows->sum(fn ($r) => $r['s']['pending']),
        ];

        if ($request->query('export') === 'csv') {
            return response()->streamDownload(function () use ($rows) {
                $out = fopen('php://output', 'w');
                fputcsv($out, ['Trip', 'Route', 'Truck', 'Driver', 'Customer', 'Status', 'Km', 'Revenue', 'Cost', 'Profit', 'Margin %', 'Cost per km']);
                foreach ($rows as $r) {
                    $cells = [
                        $r['number'], $r['route'], $r['truck'], $r['driver'], $r['customer'], $r['status'], $r['km'],
                        $r['s']['revenue'], $r['s']['total_cost'], $r['s']['profit'], $r['s']['margin'], $r['s']['cost_per_km']
                    ];
                    // SECURITY: Neutralize spreadsheet formula injection (CSV Injection)
                    $cells = array_map(fn ($c) => is_string($c) && preg_match('/^[=+\-@\t\r]/', $c) ? "'" . $c : $c, $cells);
                    fputcsv($out, $cells);
                }
                fclose($out);
            }, 'trip-profitability-' . $from . '-to-' . $to . '.csv', ['Content-Type' => 'text/csv']);
        }

        return view('logistics.reports.profitability', [
            'rows' => $rows, 
            'total' => $total, 
            'from' => $from, 
            'to' => $to, 
            'status' => $status, 
            'base' => CurrencyService::base(),
        ]);
    }
}