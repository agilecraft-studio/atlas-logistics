<?php

namespace App\Services;

use App\Model\ComplianceDocument;
use App\Model\Trailer;
use App\Model\TrailerAssignment;
use App\Model\Trip;
use App\Model\VehicleModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

/** Shared calculations for the truck / driver / trailer profile hubs. */
class FleetHubService
{
    /** Expiry state used by badges and filters: archived | expired | due_soon | valid | no_expiry. */
    public function expiryState(?Carbon $date, int $alertDays = 30, bool $archived = false): string
    {
        if ($archived) {
            return 'archived';
        }
        if (!$date) {
            return 'no_expiry';
        }

        $days = (int) now()->startOfDay()->diffInDays($date->copy()->startOfDay(), false);
        if ($days < 0) {
            return 'expired';
        }

        return $days <= $alertDays ? 'due_soon' : 'valid';
    }

    /** Apply the shared type/status/date filters used by every compliance tab. */
    public function filterDocuments(Builder $query, Request $request, string $prefix = 'doc'): Builder
    {
        $type = $request->input($prefix . '_type');
        $status = $request->input($prefix . '_status');
        $from = $request->input($prefix . '_from');
        $to = $request->input($prefix . '_to');

        $query->when($type, fn ($q) => $q->where('document_type_id', $type))
            ->when($from, fn ($q) => $q->whereDate('expiry_date', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('expiry_date', '<=', $to));

        return match ($status) {
            'archived' => $query->whereNotNull('archived_at'),
            'expired' => $query->whereNull('archived_at')->whereNotNull('expiry_date')->whereDate('expiry_date', '<', now()->toDateString()),
            'due_soon' => $query->whereNull('archived_at')->whereNotNull('expiry_date')
                ->whereDate('expiry_date', '>=', now()->toDateString())
                ->whereDate('expiry_date', '<=', now()->addDays(30)->toDateString()),
            'valid' => $query->whereNull('archived_at')->whereNotNull('expiry_date')->whereDate('expiry_date', '>', now()->addDays(30)->toDateString()),
            'no_expiry' => $query->whereNull('archived_at')->whereNull('expiry_date'),
            default => $query->whereNull('archived_at'),
        };
    }

    /** Headline compliance numbers for KPI cards. */
    public function documentCounts(string $ownerType, $ownerId): array
    {
        $base = ComplianceDocument::forOwner($ownerType, $ownerId)->whereNull('archived_at');
        $today = now()->toDateString();

        return [
            'total' => (clone $base)->count(),
            'expired' => (clone $base)->whereNotNull('expiry_date')->whereDate('expiry_date', '<', $today)->count(),
            'due_soon' => (clone $base)->whereNotNull('expiry_date')
                ->whereDate('expiry_date', '>=', $today)
                ->whereDate('expiry_date', '<=', now()->addDays(30)->toDateString())->count(),
        ];
    }

    /** Operational status shown on the truck profile. */
    public function vehicleStatus(VehicleModel $vehicle): string
    {
        if ((int) $vehicle->in_service !== 1) {
            return 'out_of_service';
        }

        $onTrip = Trip::where('vehicle_id', $vehicle->id)
            ->whereIn('status', ['planned', 'loaded', 'dispatched', 'in_transit', 'at_border'])
            ->exists();
        if ($onTrip) {
            return 'on_trip';
        }

        return $vehicle->logisticsProfile?->logistics_status ?: 'available';
    }

    /** Driver status: on a trip beats the employment status. */
    public function driverStatus($driver): string
    {
        $onTrip = Trip::where(fn ($q) => $q->where('driver_id', $driver->id)->orWhere('co_driver_id', $driver->id))
            ->whereIn('status', ['planned', 'loaded', 'dispatched', 'in_transit', 'at_border'])
            ->exists();

        return $onTrip ? 'on_trip' : ($driver->driverComplianceProfile?->driver_status ?: 'active');
    }

    /** Active vs idle time for a trailer over the last N days based on pairing periods. */
    public function trailerUtilization(Trailer $trailer, int $days = 90): array
    {
        $start = now()->subDays($days)->startOfDay();
        $end = now()->endOfDay();

        $activeDays = 0.0;
        foreach ($trailer->assignments()->withTrashed()->get() as $assignment) {
            $from = $assignment->assigned_at?->copy()->startOfDay();
            $to = $assignment->released_at?->copy()->endOfDay() ?: $end;
            if (!$from) {
                continue;
            }
            $from = $from->lt($start) ? $start->copy() : $from;
            $to = $to->gt($end) ? $end->copy() : $to;
            if ($to->gt($from)) {
                $activeDays += $from->diffInHours($to) / 24;
            }
        }

        // No pairing history yet: fall back to actual trip days.
        if ($activeDays <= 0) {
            foreach ($trailer->trips()->whereNotNull('actual_start_at')->get() as $trip) {
                $from = $trip->actual_start_at->copy()->startOfDay();
                $to = ($trip->actual_end_at ?: now())->copy()->endOfDay();
                $from = $from->lt($start) ? $start->copy() : $from;
                $to = $to->gt($end) ? $end->copy() : $to;
                if ($to->gt($from)) {
                    $activeDays += $from->diffInHours($to) / 24;
                }
            }
        }

        $activeDays = min($days, round($activeDays, 1));

        return [
            'period_days' => $days,
            'active_days' => $activeDays,
            'idle_days' => max(0, round($days - $activeDays, 1)),
            'percent' => $days > 0 ? (int) round($activeDays / $days * 100) : 0,
        ];
    }

    public function tripQueryFor(string $ownerType, $ownerId): Builder
    {
        $column = match ($ownerType) {
            'driver' => 'driver_id',
            'trailer' => 'trailer_id',
            default => 'vehicle_id',
        };

        return Trip::query()->where($column, $ownerId);
    }
}
