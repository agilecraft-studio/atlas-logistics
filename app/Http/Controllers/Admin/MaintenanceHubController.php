<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Model\ServiceReminderModel;
use App\Model\VehicleModel;
use App\Model\WorkOrders;
use App\Services\FleetHubService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

/**
 * Unified Maintenance hub: active work orders, history and preventative
 * service reminders in one page selected with ?tab=...
 */
class MaintenanceHubController extends Controller
{
    private const TABS = ['active', 'history', 'preventative'];

    public function index(Request $request)
    {
        $user = $request->user();
        abort_unless(
            $user->can('WorkOrders list') || $user->can('ServiceReminders list'),
            403
        );

        $hub = app(FleetHubService::class);
        $tab = in_array($request->query('tab'), self::TABS, true) ? $request->query('tab') : 'active';
        $vehicleIds = $this->vehicleIds($user);

        $activeOrders = $this->activeOrders($request, $vehicleIds);
        $historyOrders = $this->historyOrders($request, $vehicleIds);
        $reminders = $this->preventativeReminders($request, $vehicleIds, $hub);

        $kpis = [
            'active' => (clone $activeOrders['query'])->count(),
            'awaiting_parts' => (clone $activeOrders['query'])
                ->whereIn('status', ['awaiting_parts', 'Hold'])->count(),
            'overdue_services' => $reminders['all']->where('state', 'overdue')->count(),
            'due_soon_services' => $reminders['all']->where('state', 'due_soon')->count(),
            'month_cost' => $this->monthCost($vehicleIds),
        ];

        $vehicles = VehicleModel::whereIn('id', $vehicleIds)
            ->orderBy('license_plate')
            ->get(['id', 'license_plate', 'make_name', 'model_name']);

        $payload = [
            'tab' => $tab,
            'active' => $activeOrders['rows'],
            'history' => $historyOrders['rows'],
            'preventative' => $reminders['rows'],
            'kpis' => $kpis,
            'vehicles' => $vehicles,
            'statuses' => WorkOrders::STATUSES,
            'maintenanceTypes' => WorkOrders::MAINTENANCE_TYPES,
            'filters' => $request->only([
                'mnt_status', 'mnt_type', 'mnt_vehicle', 'mnt_from', 'mnt_to', 'mnt_q',
                'mnt_min_cost', 'mnt_max_cost', 'mnt_state',
            ]),
        ];

        return $request->wantsJson()
            ? response()->json($payload)
            : view('maintenance.index', $payload);
    }

    private function activeOrders(Request $request, array $vehicleIds): array
    {
        $query = WorkOrders::with(['vehicle', 'trailer', 'driver', 'vendor', 'mechanic', 'parts'])
            ->whereIn('vehicle_id', $vehicleIds)
            ->whereIn('status', WorkOrders::activeStatuses());

        if ($request->filled('mnt_status')) {
            $query->whereIn('status', $this->statusValuesFor((string) $request->input('mnt_status')));
        }
        if ($request->filled('mnt_type')) {
            $query->where('maintenance_type', $request->input('mnt_type'));
        }
        if ($request->filled('mnt_vehicle')) {
            $query->where('vehicle_id', $request->input('mnt_vehicle'));
        }
        if ($request->filled('mnt_from')) {
            $query->whereDate('created_at', '>=', $request->input('mnt_from'));
        }
        if ($request->filled('mnt_to')) {
            $query->whereDate('created_at', '<=', $request->input('mnt_to'));
        }
        if ($request->filled('mnt_q')) {
            $term = '%' . $request->input('mnt_q') . '%';
            $query->where(function ($q) use ($term) {
                $q->where('description', 'ilike', $term)
                    ->orWhere('reference', 'ilike', $term)
                    ->orWhereHas('vehicle', fn ($v) => $v->where('license_plate', 'ilike', $term))
                    ->orWhereHas('vendor', fn ($v) => $v->where('name', 'ilike', $term));
            });
        }

        $rows = (clone $query)
            ->orderByRaw("CASE WHEN status IN ('awaiting_parts','Hold') THEN 0 ELSE 1 END")
            ->orderBy('required_by')
            ->orderByDesc('id')
            ->get();

        return ['query' => $query, 'rows' => $rows];
    }

    private function historyOrders(Request $request, array $vehicleIds): array
    {
        $query = WorkOrders::withTrashed()
            ->with(['vehicle', 'trailer', 'driver', 'vendor', 'mechanic', 'parts'])
            ->whereIn('vehicle_id', $vehicleIds);

        $status = (string) $request->input('mnt_status', '');
        if ($status === 'archived') {
            $query->onlyTrashed();
        } elseif ($status === 'completed' || $status === 'cancelled') {
            $query->whereNull('work_orders.deleted_at')
                ->whereIn('status', $this->statusValuesFor($status));
        } else {
            $query->whereNull('work_orders.deleted_at')
                ->whereIn('status', WorkOrders::historyStatuses());
        }

        if ($request->filled('mnt_type')) {
            $query->where('maintenance_type', $request->input('mnt_type'));
        }
        if ($request->filled('mnt_vehicle')) {
            $query->where('vehicle_id', $request->input('mnt_vehicle'));
        }
        if ($request->filled('mnt_from')) {
            $query->whereDate('created_at', '>=', $request->input('mnt_from'));
        }
        if ($request->filled('mnt_to')) {
            $query->whereDate('created_at', '<=', $request->input('mnt_to'));
        }
        if ($request->filled('mnt_q')) {
            $term = '%' . $request->input('mnt_q') . '%';
            $query->where(function ($q) use ($term) {
                $q->where('description', 'ilike', $term)
                    ->orWhere('reference', 'ilike', $term)
                    ->orWhereHas('vehicle', fn ($v) => $v->where('license_plate', 'ilike', $term))
                    ->orWhereHas('vendor', fn ($v) => $v->where('name', 'ilike', $term));
            });
        }

        $costSql = '(COALESCE(work_orders.price,0) + COALESCE((
            SELECT SUM(pu.total) FROM parts_used pu
            WHERE pu.work_id = work_orders.id AND pu.deleted_at IS NULL AND pu.restored_at IS NULL
        ),0))';
        if ($request->filled('mnt_min_cost')) {
            $query->whereRaw($costSql . ' >= ?', [(float) $request->input('mnt_min_cost')]);
        }
        if ($request->filled('mnt_max_cost')) {
            $query->whereRaw($costSql . ' <= ?', [(float) $request->input('mnt_max_cost')]);
        }

        $rows = (clone $query)->orderByDesc('created_at')->orderByDesc('id')->get();

        return ['query' => $query, 'rows' => $rows];
    }

    private function preventativeReminders(Request $request, array $vehicleIds, FleetHubService $hub): array
    {
        $reminders = ServiceReminderModel::with(['vehicle', 'services'])
            ->whereIn('vehicle_id', $vehicleIds)
            ->get();

        $all = $reminders->map(function (ServiceReminderModel $reminder) use ($hub) {
            $service = $reminder->services;
            $vehicle = $reminder->vehicle;
            $today = Carbon::today();
            $currentMeter = (int) ($reminder->last_meter ?: ($vehicle?->int_mileage ?: 0));

            $nextDue = null;
            if ($service && (int) $service->overdue_time > 0) {
                $base = ($reminder->last_date && $reminder->last_date !== 'N/D')
                    ? Carbon::parse($reminder->last_date)
                    : $today->copy();
                $nextDue = $this->addInterval($base, (int) $service->overdue_time, (string) $service->overdue_unit);
            }

            $days = $nextDue ? (int) $today->diffInDays($nextDue->copy()->startOfDay(), false) : null;
            $dueSoonDays = max(1, (int) ($service?->duesoon_time ?: $hub->alertDays()));
            $dateState = $days === null ? 'upcoming' : ($days < 0 ? 'overdue' : ($days <= $dueSoonDays ? 'due_soon' : 'upcoming'));

            $meterInterval = (int) ($service?->overdue_meter ?: 0);
            $nextMeter = $meterInterval > 0 ? $currentMeter + $meterInterval : null;
            $meterSoon = max(0, (int) ($service?->duesoon_meter ?: 0));
            $meterState = 'upcoming';
            if ($nextMeter !== null) {
                $remaining = $nextMeter - $currentMeter;
                $meterState = $remaining <= 0 ? 'overdue' : ($meterSoon > 0 && $remaining <= $meterSoon ? 'due_soon' : 'upcoming');
            }

            $state = $this->worstState($dateState, $meterState);

            return (object) [
                'reminder' => $reminder,
                'vehicle' => $vehicle,
                'service' => $service,
                'last_date' => $reminder->last_date,
                'last_meter' => (int) $reminder->last_meter,
                'next_due' => $nextDue,
                'days' => $days,
                'due_soon_days' => $dueSoonDays,
                'next_meter' => $nextMeter,
                'current_meter' => $currentMeter,
                'meter_remaining' => $nextMeter !== null ? $nextMeter - $currentMeter : null,
                'state' => $state,
                'date_state' => $dateState,
                'meter_state' => $meterState,
                'interval' => $service ? trim(($service->overdue_time ?: 0) . ' ' . ($service->overdue_unit ?: '')) : '—',
            ];
        });

        $filtered = $all;
        if ($request->filled('mnt_state')) {
            $filtered = $filtered->where('state', $request->input('mnt_state'));
        }
        if ($request->filled('mnt_vehicle')) {
            $filtered = $filtered->where('reminder.vehicle_id', $request->input('mnt_vehicle'));
        }

        $weight = ['overdue' => 0, 'due_soon' => 1, 'upcoming' => 2];
        $rows = $filtered->sortBy([
            fn ($a, $b) => $weight[$a->state] <=> $weight[$b->state],
            fn ($a, $b) => ($a->next_due?->timestamp ?? PHP_INT_MAX) <=> ($b->next_due?->timestamp ?? PHP_INT_MAX),
        ])->values();

        return ['all' => $all, 'rows' => $rows];
    }

    private function worstState(string $dateState, string $meterState): string
    {
        foreach (['overdue', 'due_soon'] as $state) {
            if ($dateState === $state || $meterState === $state) {
                return $state;
            }
        }
        return 'upcoming';
    }

    private function addInterval(Carbon $date, int $value, string $unit): Carbon
    {
        $unit = strtolower(trim($unit));
        if (str_contains($unit, 'week')) {
            return $date->addWeeks($value);
        }
        if (str_contains($unit, 'month')) {
            return $date->addMonths($value);
        }
        if (str_contains($unit, 'year')) {
            return $date->addYears($value);
        }
        if (str_contains($unit, 'hour')) {
            return $date->addHours($value);
        }
        return $date->addDays($value);
    }

    private function statusValuesFor(string $status): array
    {
        $legacy = array_keys(array_filter(WorkOrders::LEGACY_STATUSES, fn ($key) => $key === $status));
        return array_values(array_unique(array_merge([$status], $legacy)));
    }

    private function monthCost(array $vehicleIds): float
    {
        return WorkOrders::with('parts')
            ->whereIn('vehicle_id', $vehicleIds)
            ->whereNotNull('completed_on')
            ->whereDate('completed_on', '>=', now()->startOfMonth()->toDateString())
            ->whereDate('completed_on', '<=', now()->endOfMonth()->toDateString())
            ->get()
            ->sum(fn (WorkOrders $order) => $order->total_cost);
    }

    private function vehicleIds($user): array
    {
        if ($user->group_id === null || $user->user_type === 'S') {
            // Work orders outlive disposed trucks, so keep their history visible.
            return VehicleModel::withTrashed()->pluck('id')->all();
        }

        return VehicleModel::where('group_id', $user->group_id)->pluck('id')->all();
    }
}
