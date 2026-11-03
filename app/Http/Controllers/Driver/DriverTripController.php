<?php
declare(strict_types=1);

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Model\Trailer;
use App\Model\Trip;
use App\Model\TripCheckpoint;
use App\Model\VehicleLogisticsProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Mobile-first checkpoint capture. The driver records only the odometer and fuel gauge; the
 * server stamps the time. Submissions are idempotent via client_uuid so weak-signal retries
 * cannot duplicate a checkpoint.
 */
class DriverTripController extends Controller
{
    public function index()
    {
        $trips = Trip::with(['vehicle', 'originCountry', 'destinationCountry'])
            ->where(fn ($q) => $q->where('driver_id', auth()->id())->orWhere('co_driver_id', auth()->id()))
            ->whereIn('status', ['planned', 'in_transit'])
            ->orderByRaw("case when status = 'in_transit' then 0 else 1 end")
            ->orderBy('planned_start_at')
            ->get();

        return view('driver.index', ['trips' => $trips]);
    }

    public function show(Trip $trip)
    {
        $this->authorizeTrip($trip);

        $trip->load(['vehicle', 'trailer', 'originCountry', 'destinationCountry']);

        return view('driver.show', [
            'trip'         => $trip,
            'lastOdometer' => $this->lastOdometer($trip),
            'lastFuel'     => optional($trip->checkpoints()->whereNotNull('fuel_level')->reorder('arrived_at', 'desc')->first())->fuel_level,
            'checkpoints'  => $trip->checkpoints()->with('recordedBy')->reorder('arrived_at', 'desc')->limit(10)->get(),
            'fuelLevels'   => config('logistics.fuel_levels'),
        ]);
    }

    public function store(Request $request, Trip $trip)
    {
        $this->authorizeTrip($trip);

        $data = $request->validate([
            'kind'        => 'required|in:' . implode(',', array_keys(config('logistics.checkpoint_kinds'))),
            'odometer'    => 'required|numeric|min:0|max:9999999',
            'fuel_level'  => 'required|in:' . implode(',', array_keys(config('logistics.fuel_levels'))),
            'location'    => 'nullable|string|max:150',
            'notes'       => 'nullable|string|max:255',
            'client_uuid' => 'nullable|uuid',
        ]);

        if (!empty($data['client_uuid'])) {
            $existing = TripCheckpoint::where('client_uuid', $data['client_uuid'])->first();
            if ($existing) {
                return $this->saved($request, $trip, $existing, 'Already recorded.');
            }
        }

        $checkpoint = DB::transaction(function () use ($trip, $data) {
            $trip = Trip::whereKey($trip->id)->lockForUpdate()->firstOrFail();
            $odometer = (float) $data['odometer'];

            if ($data['kind'] === 'departure') {
                if ($trip->status !== 'planned') {
                    throw ValidationException::withMessages(['kind' => 'This trip has already departed.']);
                }
            } elseif ($trip->status !== 'in_transit') {
                throw ValidationException::withMessages(['kind' => 'This trip is not in transit.']);
            }

            $last = $this->lastOdometer($trip);
            if ($data['kind'] !== 'departure' && $odometer < $last) {
                throw ValidationException::withMessages([
                    'odometer' => 'Odometer cannot be lower than the last reading (' . number_format($last, 1) . ').',
                ]);
            }

            $checkpoint = $trip->checkpoints()->create([
                'kind'        => $data['kind'],
                'odometer'    => $odometer,
                'fuel_level'  => $data['fuel_level'],
                'location'    => $data['location'] ?? null,
                'notes'       => $data['notes'] ?? null,
                'arrived_at'  => now(),
                'recorded_by' => auth()->id(),
                'client_uuid' => $data['client_uuid'] ?? null,
            ]);

            if ($data['kind'] === 'departure') {
                $trip->update([
                    'status'          => 'in_transit',
                    'start_odometer'  => $odometer,
                    'actual_start_at' => now(),
                ]);
                $this->setAssets($trip, 'on_trip');
            } elseif ($data['kind'] === 'arrival') {
                $start = (float) ($trip->start_odometer ?? $odometer);
                $trip->update([
                    'status'        => 'completed',
                    'end_odometer'  => $odometer,
                    'distance_km'   => round($odometer - $start, 1),
                    'actual_end_at' => now(),
                ]);
                $this->setAssets($trip, 'available');
            }

            return $checkpoint;
        });

        return $this->saved($request, $trip, $checkpoint, 'Checkpoint saved.');
    }

    private function saved(Request $request, Trip $trip, TripCheckpoint $checkpoint, string $message)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'ok'         => true,
                'message'    => $message,
                'checkpoint' => [
                    'id'         => $checkpoint->id,
                    'kind'       => $checkpoint->kind,
                    'odometer'   => (float) $checkpoint->odometer,
                    'fuel_level' => $checkpoint->fuel_level,
                    'recorded_at' => optional($checkpoint->arrived_at)->toIso8601String(),
                ],
            ]);
        }

        return redirect()->route('driver.trips.show', $trip->id)->with('success', $message);
    }

    private function authorizeTrip(Trip $trip): void
    {
        $assigned = array_filter([(int) $trip->driver_id, (int) $trip->co_driver_id]);
        abort_unless(in_array((int) auth()->id(), $assigned, true), 403, 'This trip is not assigned to you.');
        abort_if(in_array($trip->status, ['closed', 'cancelled'], true), 403, 'This trip is closed.');
    }

    private function lastOdometer(Trip $trip): float
    {
        return max(
            (float) ($trip->start_odometer ?? 0),
            (float) ($trip->checkpoints()->max('odometer') ?? 0),
            (float) ($trip->end_odometer ?? 0)
        );
    }

    private function setAssets(Trip $trip, string $status): void
    {
        $profile = VehicleLogisticsProfile::firstOrNew(['vehicle_id' => $trip->vehicle_id]);
        if ($status === 'on_trip' || $profile->logistics_status === 'on_trip') {
            $profile->logistics_status = $status;
            $profile->save();
        }

        if ($trip->trailer_id) {
            Trailer::where('id', $trip->trailer_id)
                ->when($status === 'available', fn ($q) => $q->where('status', 'on_trip'))
                ->update(['status' => $status]);
        }
    }
}
