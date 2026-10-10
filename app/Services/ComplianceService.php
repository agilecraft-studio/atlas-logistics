<?php

namespace App\Services;

use App\Model\ComplianceDocument;
use App\Model\DriverComplianceProfile;
use App\Model\Trailer;
use App\Model\Trip;
use App\Model\User;
use App\Model\VehicleLogisticsProfile;
use App\Model\VehicleModel;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ComplianceService
{
    /** Human label for a vehicle row. */
    public static function vehicleLabel($v): string
    {
        return trim(($v->license_plate ?: 'No plate') . ' - ' . trim(($v->make_name ?? '') . ' ' . ($v->model_name ?? '')));
    }

    /**
     * Every dated item that needs attention, as plain arrays:
     * owner_type, owner_id, owner, item, number, expiry (Carbon), days_left (int), critical (bool), status.
     * $days = how far ahead to look; expired items are always included.
     */
    public function items(int $days = 30): Collection
    {
        $today = Carbon::today();
        $limit = $today->copy()->addDays($days);
        $rows = collect();

        $vehicles = VehicleModel::all()->keyBy('id');
        $trailers = Trailer::all()->keyBy('id');
        $drivers  = User::where('user_type', 'D')->get()->keyBy('id');

        $push = function ($type, $id, $owner, $item, $number, $expiry, $critical = true) use (&$rows, $today, $limit) {
            if (!$expiry) {
                return;
            }
            try {
                $date = $expiry instanceof Carbon ? $expiry->copy()->startOfDay() : Carbon::parse($expiry)->startOfDay();
            } catch (\Throwable $e) {
                return;
            }
            if ($date->year < 2000 || $date->gt($limit)) {
                return; // ignore blank/zero legacy dates and anything outside the window
            }
            $left = (int) $today->diffInDays($date, false);
            $rows->push([
                'owner_type' => $type, 'owner_id' => $id, 'owner' => $owner, 'item' => $item, 'number' => $number,
                'expiry' => $date, 'days_left' => $left, 'critical' => $critical,
                'status' => $left < 0 ? 'expired' : 'due_soon',
            ]);
        };

        // 1. uploaded / recorded documents
        foreach (ComplianceDocument::with('type')->whereNotNull('expiry_date')->get() as $doc) {
            $owner = $this->ownerName($doc->owner_type, $doc->owner_id, $vehicles, $trailers, $drivers);
            if ($owner === null) {
                continue; // owner was deleted
            }
            $push($doc->owner_type, $doc->owner_id, $owner, optional($doc->type)->name ?: 'Document', $doc->document_number,
                $doc->expiry_date, (bool) optional($doc->type)->is_critical);
        }

        // 2. legacy dates already stored on the vehicle record
        foreach ($vehicles as $v) {
            $label = self::vehicleLabel($v);
            $push('vehicle', $v->id, $label, 'Insurance expiry', $v->insurance_number, $v->exp_date, true);
            $push('vehicle', $v->id, $label, 'Registration expiry', null, $v->reg_exp_date, true);
            $push('vehicle', $v->id, $label, 'Licence expiry', null, $v->lic_exp_date, true);
        }

        // 3. vehicle service due date
        foreach (VehicleLogisticsProfile::whereNotNull('next_service_date')->get() as $p) {
            if (isset($vehicles[$p->vehicle_id])) {
                $push('vehicle', $p->vehicle_id, self::vehicleLabel($vehicles[$p->vehicle_id]), 'Service due', null, $p->next_service_date, false);
            }
        }

        // 4. driver dates
        foreach (DriverComplianceProfile::all() as $p) {
            if (!isset($drivers[$p->user_id])) {
                continue;
            }
            $name = $drivers[$p->user_id]->name;
            $push('driver', $p->user_id, $name, 'Driving licence', $p->licence_number, $p->licence_expiry, true);
            $push('driver', $p->user_id, $name, 'Passport', $p->passport_number, $p->passport_expiry, true);
            $push('driver', $p->user_id, $name, 'Yellow fever card', null, $p->yellow_fever_expiry, false);
            $push('driver', $p->user_id, $name, 'Medical certificate', null, $p->medical_expiry, false);
        }

        return $rows->sortBy('days_left')->values();
    }

    protected function ownerName($type, $id, $vehicles, $trailers, $drivers): ?string
    {
        if ($type === 'vehicle') {
            return isset($vehicles[$id]) ? self::vehicleLabel($vehicles[$id]) : null;
        }
        if ($type === 'trailer') {
            return isset($trailers[$id]) ? $trailers[$id]->registration_number : null;
        }
        if ($type === 'driver') {
            return isset($drivers[$id]) ? $drivers[$id]->name : null;
        }
        return null;
    }

    /**
     * Reasons a trip must NOT be dispatched. Empty array = clear to go.
     * systemDesign.md 7.3.4 / 10.1: expired licence, passport (cross-border), critical documents,
     * unapproved destination country, and double-booked vehicle/driver/trailer.
     */
    public function dispatchBlockers(Trip $trip): array
    {
        $today = Carbon::today();
        $blockers = [];
        $crossBorder = $trip->origin_country_id && $trip->destination_country_id
            && (int) $trip->origin_country_id !== (int) $trip->destination_country_id;

        $driverIds = array_filter([$trip->driver_id, $trip->co_driver_id]);
        foreach ($driverIds as $driverId) {
            $name = optional(User::withTrashed()->find($driverId))->name ?: ('Driver #' . $driverId);
            $profile = DriverComplianceProfile::where('user_id', $driverId)->first();

            if (!$profile) {
                $blockers[] = "{$name}: no compliance profile (licence/passport dates) recorded. Complete it under Fleet Register > Drivers.";
            } else {
                if (!$profile->licence_expiry) {
                    $blockers[] = "{$name}: driving licence expiry date is missing.";
                } elseif ($profile->licence_expiry->lt($today)) {
                    $blockers[] = "{$name}: driving licence expired on " . $profile->licence_expiry->format('d M Y') . '.';
                }
                if ($crossBorder) {
                    if (!$profile->passport_expiry) {
                        $blockers[] = "{$name}: passport expiry date is missing (cross-border trip).";
                    } elseif ($profile->passport_expiry->lt($today)) {
                        $blockers[] = "{$name}: passport expired on " . $profile->passport_expiry->format('d M Y') . '.';
                    }
                    $approved = (array) $profile->approved_country_ids;
                    if (!empty($approved)) {
                        $needed = array_filter(array_merge([$trip->destination_country_id], (array) $trip->route_country_ids));
                        foreach (array_unique($needed) as $cid) {
                            if (!in_array((int) $cid, array_map('intval', $approved), true)) {
                                $c = \App\Model\Country::find($cid);
                                $blockers[] = "{$name}: not approved to enter " . ($c ? $c->name : "country #{$cid}") . '.';
                            }
                        }
                    }
                }
                if (in_array($profile->driver_status, ['suspended', 'terminated'], true)) {
                    $blockers[] = "{$name}: driver status is {$profile->driver_status}.";
                }
            }

            // critical driver documents
            foreach (ComplianceDocument::with('type')->where('owner_type', 'driver')->where('owner_id', $driverId)
                ->whereNotNull('expiry_date')->whereDate('expiry_date', '<', $today)->get() as $doc) {
                if (optional($doc->type)->is_critical) {
                    $blockers[] = "{$name}: {$doc->type->name} expired on " . $doc->expiry_date->format('d M Y') . '.';
                }
            }
        }

        // vehicle + trailer critical documents and legacy vehicle dates
        $vehicle = VehicleModel::withTrashed()->find($trip->vehicle_id);
        $vLabel = $vehicle ? self::vehicleLabel($vehicle) : 'Vehicle #' . $trip->vehicle_id;
        if ($vehicle) {
            foreach (['exp_date' => 'Insurance', 'reg_exp_date' => 'Registration', 'lic_exp_date' => 'Licence'] as $col => $label) {
                if ($vehicle->$col) {
                    try {
                        if (Carbon::parse($vehicle->$col)->startOfDay()->lt($today)) {
                            $blockers[] = "{$vLabel}: {$label} expired on " . Carbon::parse($vehicle->$col)->format('d M Y') . '.';
                        }
                    } catch (\Throwable $e) {
                        // unparseable legacy date: ignore
                    }
                }
            }
        }
        $assets = [['vehicle', $trip->vehicle_id, $vLabel]];
        if ($trip->trailer_id) {
            $trailer = Trailer::withTrashed()->find($trip->trailer_id);
            $assets[] = ['trailer', $trip->trailer_id, $trailer ? $trailer->registration_number : 'Trailer #' . $trip->trailer_id];
        }
        foreach ($assets as [$type, $id, $label]) {
            foreach (ComplianceDocument::with('type')->where('owner_type', $type)->where('owner_id', $id)
                ->whereNotNull('expiry_date')->whereDate('expiry_date', '<', $today)->get() as $doc) {
                if (optional($doc->type)->is_critical) {
                    $blockers[] = "{$label}: {$doc->type->name} expired on " . $doc->expiry_date->format('d M Y') . '.';
                }
            }
        }

        // double booking
        $busy = Trip::where('status', 'in_transit')->where('id', '!=', $trip->id);
        if ((clone $busy)->where('vehicle_id', $trip->vehicle_id)->exists()) {
            $blockers[] = "{$vLabel} is already on another trip that is still in transit.";
        }
        if ((clone $busy)->where('driver_id', $trip->driver_id)->exists()) {
            $blockers[] = 'Driver is already on another trip that is still in transit.';
        }
        if ($trip->trailer_id && (clone $busy)->where('trailer_id', $trip->trailer_id)->exists()) {
            $blockers[] = 'Trailer is already on another trip that is still in transit.';
        }

        return $blockers;
    }
}
