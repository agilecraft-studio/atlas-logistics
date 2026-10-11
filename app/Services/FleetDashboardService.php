<?php

namespace App\Services;

use App\Model\ComplianceDocument;
use App\Model\DriverComplianceProfile;
use App\Model\Trip;
use App\Model\TripExpense;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Fleet Operations Dashboard data provider.
 *
 * All numbers are computed from the live schema (trips, vehicle_logistics_profiles,
 * driver_compliance_profiles, compliance_documents, trip_expenses, trip_expense_categories).
 * No hardcoded business numbers.
 */
class FleetDashboardService
{
    protected array $tripStatusOrder;
    protected array $tripStatusLabels;

    public function __construct()
    {
        $this->tripStatusOrder = array_keys(config('logistics.trip_statuses'));
        $this->tripStatusLabels = array_values(config('logistics.trip_statuses'));
    }

    /**
     * Build the full dashboard payload for a period.
     *
     * @param string $period Whitelisted: month|week|year. Default month.
     * @return array The exact keys consumed by the dashboard blade.
     */
    public function data(string $period = 'month'): array
    {
        $period = strtolower($period);
        if (! in_array($period, ['month', 'week', 'year'], true)) {
            $period = 'month';
        }

        return Cache::remember("fleet-dashboard:{$period}", now()->addMinutes(5), function () use ($period) {
            return $this->compute($period);
        });
    }

    /** @param string $period */
    protected function compute(string $period): array
    {
        [$start, $end] = $this->periodBounds($period);

        // ---- Trip status distribution (ordered by config trip_statuses) ----
        $statusCounts = $this->tripStatusCounts();
        $statusValues = array_map(
            fn ($k) => (int) ($statusCounts[$k] ?? 0),
            $this->tripStatusOrder
        );

        // ---- Truck availability (vehicle_logistics_profiles.logistics_status) ----
        $truckCounts = DB::table('vehicle_logistics_profiles')
            ->select('logistics_status', DB::raw('count(*) as total'))
            ->groupBy('logistics_status')
            ->pluck('total', 'logistics_status')
            ->toArray();

        $availableTrucks = (int) ($truckCounts['available'] ?? 0);
        $trucksOnTrip = (int) ($truckCounts['on_trip'] ?? 0);
        $trucksInWorkshop = (int) ($truckCounts['workshop'] ?? 0);

        // ---- Drivers on duty (driver_compliance_profiles.driver_status) ----
        $driversOnDuty = (int) DriverComplianceProfile::where('driver_status', 'active')->count();

        // ---- Financial KPIs (period-aware) ----
        $revenue = (float) $this->sumRevenue($start, $end);
        $tripCosts = (float) $this->sumApprovedTripCosts($start, $end);
        $netProfit = $revenue - $tripCosts;

        // ---- Recent trips (latest 8) ----
        $recentTrips = $this->latestTrips(8);

        // ---- Action required counts ----
        $expiringDocuments = $this->countExpiringDocuments(config('logistics.default_alert_days', 30));
        $servicesDue = $this->countServicesDue();
        $fuelVarianceAlerts = 0; // TODO(source): no fuel-variance model in current schema.
        $pendingExpenseApprovals = (int) TripExpense::where('status', 'pending')->count();
        $missingPods = $this->countMissingPods();

        // ---- Top trip cost categories (period-aware) ----
        $costCategories = $this->topExpenseCategories($start, $end, 5);

        // ---- Historical chart series (rolling 10 months) ----
        $chartMonths = $this->monthlyLabels(10);
        $revenueChart = $this->monthlySeries('revenue', 10);
        $costChart = $this->monthlySeries('cost', 10);

        return [
            'active_trips' => $this->countTripsByStatus('in_transit'),
            'available_trucks' => $availableTrucks,
            'trucks_on_trip' => $trucksOnTrip,
            'trucks_in_workshop' => $trucksInWorkshop,
            'drivers_on_duty' => $driversOnDuty,
            'revenue_this_month' => round($revenue, 2),
            'trip_costs_this_month' => round($tripCosts, 2),
            'net_profit_this_month' => round($netProfit, 2),
            'recent_trips' => $recentTrips,
            'expiring_documents' => $expiringDocuments,
            'services_due' => $servicesDue,
            'fuel_variance_alerts' => $fuelVarianceAlerts,
            'pending_expense_approvals' => $pendingExpenseApprovals,
            'missing_pods' => $missingPods,
            'cost_categories' => $costCategories,
            'revenue_chart' => $revenueChart,
            'cost_chart' => $costChart,
            'chart_months' => $chartMonths,
            'trip_status_values' => $statusValues,
            'trip_status_labels' => $this->tripStatusLabels,
        ];
    }

    // ---- Period helpers ----

    /** @return array{0:string,1:string} */
    protected function periodBounds(string $period): array
    {
        $now = now();
        switch ($period) {
            case 'week':
                $start = $now->copy()->subDays((int) $now->dayOfWeekLocal)->startOfDay();
                $end = $now->copy()->endOfDay();
                break;
            case 'year':
                $start = $now->copy()->startOfYear();
                $end = $now->copy()->endOfYear();
                break;
            default:
                $start = $now->copy()->startOfMonth();
                $end = $now->copy()->endOfMonth();
        }
        return [$start, $end];
    }

    // ---- Period aggregation queries ----

    protected function sumRevenue($start, $end): float
    {
        return (float) Trip::whereBetween('planned_start_at', [$start, $end])
            ->whereNotNull('planned_start_at')
            ->sum('revenue_amount_base');
    }

    protected function sumApprovedTripCosts($start, $end): float
    {
        return (float) TripExpense::where('status', 'approved')
            ->whereBetween('expense_date', [$start, $end])
            ->sum('amount_base');
    }

    // ---- Counters ----

    protected function countTripsByStatus(string $status): int
    {
        return (int) Trip::where('status', $status)->count();
    }

    protected function countExpiringDocuments(int $alertDays): int
    {
        return (int) ComplianceDocument::where('expiry_date', '>=', now())
            ->whereRaw('expiry_date <= ?', [now()->addDays($alertDays)])
            ->count();
    }

    /** @return array<string,int> */
    protected function tripStatusCounts(): array
    {
        return Trip::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();
    }

    protected function countServicesDue(): int
    {
        // Real source: vehicle_logistics_profiles.next_service_date within the alert window.
        return (int) DB::table('vehicle_logistics_profiles')
            ->whereNotNull('next_service_date')
            ->where('next_service_date', '>=', now()->startOfDay())
            ->whereRaw('next_service_date <= ?', [now()->addDays(config('logistics.default_alert_days', 30))])
            ->count();
    }

    protected function countMissingPods(): int
    {
        // Real source: delivered (completed) trips with no POD uploaded (pod_path null).
        return (int) Trip::where('status', 'completed')
            ->whereNull('pod_path')
            ->count();
    }

    // ---- Collections ----



    /**
     * @param int $limit
     * @return array<int,array<string,mixed>>
     */
    protected function latestTrips(int $limit): array
    {
        $statusMap = config('logistics.trip_statuses');
        $trips = Trip::with(['vehicle', 'driver', 'originCountry', 'destinationCountry', 'checkpoints' => function ($q) {
            $q->orderBy('departed_at', 'asc');
        }])
            ->orderByDesc('id')
            ->limit($limit)
            ->get();

        $rows = [];
        foreach ($trips as $trip) {
            $checkpoints = $trip->checkpoints->toArray();
            $total = count($checkpoints);
            $departed = 0;
            foreach ($checkpoints as $cp) {
                if (! empty($cp['departed_at'])) {
                    $departed++;
                }
            }
            $progress = $total > 0 ? round($departed / $total * 100) : 0;

            $origin = $trip->origin_city ?: (optional($trip->originCountry)->name ?: '—');
            $destination = $trip->destination_city ?: (optional($trip->destinationCountry)->name ?: '—');
            $route = "{$origin} → {$destination}";

            $eta = $trip->planned_start_at
                ? $trip->planned_start_at->format('M d, H:i')
                : (isset($trip->actual_start_at) ? $trip->actual_start_at->format('M d, H:i') : '—');

            $rows[] = [
                'ref' => $trip->trip_number ?: 'TBD-' . $trip->id,
                'vehicle' => $trip->vehicle->license_plate ?: '—',
                'driver' => $trip->driver->name ?: '—',
                'route' => $route,
                'status' => $statusMap[$trip->status] ?? ucfirst($trip->status),
                'eta' => $eta,
                'progress' => max(0, min(100, $progress)),
            ];
        }
        return $rows;
    }

    /**
     * @param int $limit
     * @return array<int,array<string,mixed>>
     */
    protected function topExpenseCategories($start, $end, int $limit): array
    {
        $rows = DB::table('trip_expenses')
            ->join('trip_expense_categories', 'trip_expenses.category_id', '=', 'trip_expense_categories.id')
            ->selectRaw('trip_expense_categories.name as category_name, SUM(trip_expenses.amount_base) as total')
            ->where('trip_expenses.status', 'approved')
            ->whereBetween('trip_expenses.expense_date', [$start, $end])
            ->groupBy('trip_expense_categories.name')
            ->orderByDesc('total')
            ->limit($limit)
            ->get();

        $total = array_sum($rows->pluck('total')->toArray());

        $palette = ['#10b981', '#1688e8', '#f2b544', '#ef5b83', '#9561e8', '#7c5cbf', '#3b82f6', '#f59e0b', '#ef4444', '#16a875'];
        $categories = [];
        foreach ($rows as $index => $r) {
            $name = $r->category_name;
            $amount = (float) $r->total;
            $percent = $total > 0 ? round($amount / $total * 100) : 0;
            $color = $palette[$index % count($palette)];
            $categories[] = [
                'name' => $name,
                'amount' => round($amount, 2),
                'percent' => $percent,
                'color' => $color,
            ];
        }
        return $categories;
    }

    // ---- Rolling monthly chart helpers (10 months, oldest -> newest) ----

    /** @return array<int,string> */
    protected function monthlyLabels(int $months): array
    {
        $labels = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $labels[] = now()->copy()->subMonths($i)->format('M');
        }
        return $labels;
    }

    /** @return array<int,float> */
    protected function monthlySeries(string $kind, int $months): array
    {
        $series = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $start = now()->copy()->subMonths($i)->startOfMonth();
            $end = now()->copy()->subMonths($i)->endOfMonth();
            $series[] = round(
                $kind === 'cost' ? $this->sumApprovedTripCosts($start, $end) : $this->sumRevenue($start, $end),
                2
            );
        }
        return $series;
    }
}