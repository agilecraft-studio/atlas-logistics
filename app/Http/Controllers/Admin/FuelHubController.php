<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Model\FuelCard;
use App\Model\FuelModel;
use App\Model\User;
use App\Model\VehicleModel;
use App\Model\Vendor;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Fuel Management hub: logs, fuel-card accounts and efficiency analytics
 * on one route, selected with ?tab=...
 */
class FuelHubController extends Controller
{
    private const TABS = ['logs', 'cards', 'analytics'];

    public function index(Request $request)
    {
        $user = $request->user();
        abort_unless($user->can('Fuel list'), 403);

        $isDriver = $user->user_type === 'D';
        $tab = in_array($request->query('tab'), self::TABS, true) ? $request->query('tab') : 'logs';
        if ($isDriver) {
            $tab = 'logs';
        }

        $vehicleIds = $this->vehicleIds($user);
        $logsQuery = $this->logsQuery($request, $user, $vehicleIds);
        $logs = (clone $logsQuery)->orderByDesc('date')->orderByDesc('id')->get();

        $cards = collect();
        $cardStats = ['active' => 0, 'suspended' => 0, 'expired' => 0, 'balance' => 0];
        if (!$isDriver) {
            $cardsQuery = FuelCard::with(['vendor', 'vehicle', 'driver']);
            if ($request->filled('fc_status')) {
                $cardsQuery->where('status', $request->input('fc_status'));
            }
            $cards = $cardsQuery->orderBy('label')->get();
            $allCards = FuelCard::all();
            $cardStats = [
                'active' => $allCards->where('status', 'active')->count(),
                'suspended' => $allCards->where('status', 'suspended')->count(),
                'expired' => $allCards->where('status', 'expired')->count(),
                'balance' => (float) $allCards->sum('balance'),
            ];
        }

        $analytics = $isDriver ? $this->emptyAnalytics() : $this->analytics($request, $vehicleIds);

        $payload = [
            'tab' => $tab,
            'isDriver' => $isDriver,
            'logs' => $logs,
            'cards' => $cards,
            'cardStats' => $cardStats,
            'analytics' => $analytics,
            'vehicles' => VehicleModel::whereIn('id', $vehicleIds)->orderBy('license_plate')->get(['id', 'license_plate']),
            'drivers' => User::where('user_type', 'D')->orderBy('name')->get(['id', 'name']),
            'vendors' => Vendor::orderBy('name')->get(['id', 'name', 'type']),
            'currencies' => \App\Model\Currency::where('is_active', true)->orderBy('code')->get(['code', 'name']),
            'cardStatuses' => FuelCard::STATUSES,
            'filters' => $request->only(['fl_from', 'fl_to', 'fl_vehicle', 'fl_driver', 'fl_station', 'fl_q', 'fc_status']),
        ];

        return $request->wantsJson()
            ? response()->json($payload)
            : view('fuel.index', $payload);
    }

    public function storeCard(Request $request)
    {
        abort_unless($request->user()->can('Fuel add'), 403);
        $data = $this->validateCard($request);
        $data['created_by'] = $request->user()->id;
        $data['currency_code'] = $data['currency_code'] ?: config('logistics.base_currency', 'MWK');
        $data['monthly_limit'] = $data['monthly_limit'] ?? 0;
        $data['balance'] = $data['balance'] ?? 0;

        FuelCard::create($data);

        return redirect()->route('fuel.index', ['tab' => 'cards'])->with('success', 'Fuel card added.');
    }

    public function updateCard(Request $request, $id)
    {
        abort_unless($request->user()->can('Fuel edit'), 403);
        $card = FuelCard::findOrFail($id);
        $data = $this->validateCard($request, $card->id);
        $data['currency_code'] = $data['currency_code'] ?: $card->currency_code;
        $card->update($data);

        return redirect()->route('fuel.index', ['tab' => 'cards'])->with('success', 'Fuel card updated.');
    }

    public function archiveCard(Request $request, $id)
    {
        abort_unless($request->user()->can('Fuel delete'), 403);
        FuelCard::findOrFail($id)->delete();

        return redirect()->route('fuel.index', ['tab' => 'cards'])->with('success', 'Fuel card archived.');
    }

    private function validateCard(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'label' => 'required|string|max:120',
            'card_number' => [
                'required', 'string', 'max:60',
                Rule::unique('fuel_cards', 'card_number')->ignore($ignoreId),
            ],
            'vendor_id' => 'nullable|integer',
            'vehicle_id' => 'nullable|integer',
            'driver_id' => 'nullable|integer|exists:users,id',
            'currency_code' => 'nullable|string|size:3',
            'monthly_limit' => 'nullable|numeric|min:0',
            'balance' => 'nullable|numeric|min:0',
            'status' => 'required|in:' . implode(',', array_keys(FuelCard::STATUSES)),
            'expires_on' => 'nullable|date',
            'note' => 'nullable|string',
        ]);
    }

    private function logsQuery(Request $request, $user, array $vehicleIds)
    {
        $query = FuelModel::with(['vehicle_data', 'driver', 'vendor', 'card']);

        if ($user->user_type === 'D') {
            $query->where('user_id', $user->id);
        } else {
            $query->whereIn('vehicle_id', $vehicleIds);
        }

        if ($request->filled('fl_vehicle')) {
            $query->where('vehicle_id', $request->input('fl_vehicle'));
        }
        if ($request->filled('fl_driver')) {
            $query->where('user_id', $request->input('fl_driver'));
        }
        if ($request->filled('fl_from')) {
            $query->whereDate('date', '>=', $request->input('fl_from'));
        }
        if ($request->filled('fl_to')) {
            $query->whereDate('date', '<=', $request->input('fl_to'));
        }
        if ($request->filled('fl_station')) {
            $station = (string) $request->input('fl_station');
            $query->where(function ($q) use ($station) {
                if (ctype_digit($station)) {
                    $q->where('vendor_id', (int) $station);
                }
                $q->orWhereHas('vendor', fn ($v) => $v->where('name', 'ilike', '%' . $station . '%'))
                    ->orWhere('vendor_name', 'ilike', '%' . $station . '%');
            });
        }
        if ($request->filled('fl_q')) {
            $term = '%' . $request->input('fl_q') . '%';
            $query->where(function ($q) use ($term) {
                $q->where('reference', 'ilike', $term)
                    ->orWhere('note', 'ilike', $term)
                    ->orWhere('vendor_name', 'ilike', $term)
                    ->orWhereHas('vehicle_data', fn ($v) => $v->where('license_plate', 'ilike', $term));
            });
        }

        return $query;
    }

    /** Efficiency per vehicle/driver plus anomalies against each vehicle's rolling average. */
    private function analytics(Request $request, array $vehicleIds): array
    {
        $query = FuelModel::with(['vehicle_data', 'driver'])
            ->whereIn('vehicle_id', $vehicleIds);
        if ($request->filled('fl_from')) {
            $query->whereDate('date', '>=', $request->input('fl_from'));
        }
        if ($request->filled('fl_to')) {
            $query->whereDate('date', '<=', $request->input('fl_to'));
        }

        $logs = $query->orderBy('vehicle_id')->orderBy('date')->orderBy('id')->get();

        $vehicles = [];
        $anomalies = [];
        $totalLitres = 0.0;
        $totalCost = 0.0;
        $totalDistance = 0.0;

        foreach ($logs->groupBy('vehicle_id') as $vehicleId => $vehicleLogs) {
            $previousMeter = null;
            $distance = 0.0;
            $litres = 0.0;
            $cost = 0.0;
            $fills = 0;
            $efficiencies = [];

            foreach ($vehicleLogs as $log) {
                $meter = (float) $log->start_meter;
                $qty = (float) $log->qty;
                $logCost = $qty * (float) $log->cost_per_unit;
                $litres += $qty;
                $cost += $logCost;
                $fills++;
                $gap = null;
                if ($previousMeter !== null) {
                    $gap = $meter - $previousMeter;
                    if ($gap > 0) {
                        $distance += $gap;
                        if ($qty > 0) {
                            $efficiencies[] = $gap / $qty;
                        }
                    } elseif ($gap < 0) {
                        $anomalies[] = $this->anomaly($log, $gap, null, 'Odometer decreased');
                    }
                }
                $log->setAttribute('odometer_gap', $gap);
                $log->setAttribute('km_per_litre', $gap !== null && $gap > 0 && $qty > 0 ? round($gap / $qty, 2) : null);
                $previousMeter = $meter;
            }

            $average = count($efficiencies) ? array_sum($efficiencies) / count($efficiencies) : null;

            if ($average !== null) {
                $average = round($average, 2);
                foreach ($vehicleLogs as $log) {
                    $kmPerL = $log->getAttribute('km_per_litre');
                    if ($kmPerL === null) {
                        continue;
                    }
                    if ($kmPerL < $average * 0.6) {
                        $anomalies[] = $this->anomaly($log, $log->odometer_gap, $kmPerL, 'Lower than usual consumption');
                    } elseif ($kmPerL > $average * 1.6) {
                        $anomalies[] = $this->anomaly($log, $log->odometer_gap, $kmPerL, 'Higher than usual consumption');
                    }
                }
            }

            $vehicles[] = (object) [
                'vehicle_id' => $vehicleId,
                'vehicle' => $vehicleLogs->first()->vehicle_data,
                'fills' => $fills,
                'litres' => round($litres, 2),
                'cost' => round($cost, 2),
                'distance' => round($distance, 1),
                'km_per_litre' => $average,
                'cost_per_km' => $distance > 0 ? round($cost / $distance, 2) : null,
            ];

            $totalLitres += $litres;
            $totalCost += $cost;
            $totalDistance += $distance;
        }

        $drivers = $logs->filter(fn ($log) => $log->user_id !== null)
            ->groupBy('user_id')
            ->map(function ($driverLogs) {
                $first = $driverLogs->first();
                return (object) [
                    'driver_id' => $first->user_id,
                    'driver' => $first->driver,
                    'fills' => $driverLogs->count(),
                    'litres' => round($driverLogs->sum(fn ($l) => (float) $l->qty), 2),
                    'cost' => round($driverLogs->sum(fn ($l) => (float) $l->qty * (float) $l->cost_per_unit), 2),
                ];
            })->values();

        usort($vehicles, fn ($a, $b) => $b->litres <=> $a->litres);
        usort($anomalies, fn ($a, $b) => strcmp((string) $b->date, (string) $a->date));

        return [
            'logs' => $logs,
            'vehicles' => $vehicles,
            'drivers' => $drivers,
            'anomalies' => $anomalies,
            'totals' => [
                'litres' => round($totalLitres, 2),
                'cost' => round($totalCost, 2),
                'distance' => round($totalDistance, 1),
                'km_per_litre' => $totalLitres > 0 && $totalDistance > 0 ? round($totalDistance / $totalLitres, 2) : null,
            ],
        ];
    }

    private function anomaly(FuelModel $log, float $gap, ?float $kmPerL, string $reason): object
    {
        return (object) [
            'id' => $log->id,
            'date' => $log->date,
            'vehicle' => $log->vehicle_data,
            'driver' => $log->driver,
            'litres' => (float) $log->qty,
            'odometer' => (float) $log->start_meter,
            'odometer_gap' => round($gap, 1),
            'km_per_litre' => $kmPerL,
            'reason' => $reason,
        ];
    }

    private function emptyAnalytics(): array
    {
        return [
            'logs' => collect(),
            'vehicles' => [],
            'drivers' => [],
            'anomalies' => [],
            'totals' => ['litres' => 0, 'cost' => 0, 'distance' => 0, 'km_per_litre' => null],
        ];
    }

    private function vehicleIds($user): array
    {
        if ($user->group_id === null || $user->user_type === 'S') {
            // Historical fuel logs can belong to trucks that were later disposed.
            return VehicleModel::withTrashed()->pluck('id')->all();
        }

        return VehicleModel::where('group_id', $user->group_id)->pluck('id')->all();
    }
}
