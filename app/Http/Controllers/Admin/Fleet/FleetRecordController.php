<?php

namespace App\Http\Controllers\Admin\Fleet;

use App\Http\Controllers\Controller;
use App\Model\AtlasLogistics;
use App\Model\ComplianceDocument;
use App\Model\Country;
use App\Model\DriverAdvance;
use App\Model\DriverDeduction;
use App\Model\DriverIncident;
use App\Model\DriverRating;
use App\Model\FuelModel;
use App\Model\Trailer;
use App\Model\TrailerAssignment;
use App\Model\Trip;
use App\Model\User;
use App\Model\VehicleComponent;
use App\Model\VehicleModel;
use App\Model\VehicleTelematics;
use App\Model\WorkOrders;
use App\Services\FleetHubService;
use App\Support\Money;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

/**
 * One printer-friendly record renderer shared by every hub row (and the hub header).
 * Route types: vehicle, driver, trailer, compliance, work_order, fuel, trip, incident,
 * deduction, advance, assignment, telematics, component, rating.
 */
class FleetRecordController extends Controller
{
    public function print($type, $id)
    {
        $payload = $this->payload($type, $id, true);

        return view('fleet.records.print', $payload);
    }

    public function pdf($type, $id)
    {
        $payload = $this->payload($type, $id, false);
        $pdf = Pdf::loadView('fleet.records.print', $payload)->setPaper('a4');

        return $pdf->download($payload['filename'] . '.pdf');
    }

    public function archive(Request $request, $type, $id)
    {
        $this->assertFleetUser($request);
        $user = $request->user();

        switch ($type) {
            case 'compliance':
                $this->requireAny($user, ['Compliance delete', 'Compliance edit']);
                $record = ComplianceDocument::findOrFail($id);
                $record->update(['archived_at' => now()]);
                $message = 'Document archived.';
                break;

            case 'incident':
                $this->requireAny($user, ['Drivers edit']);
                $record = DriverIncident::findOrFail($id);
                $record->update(['archived_at' => now()]);
                $message = 'Incident archived.';
                break;

            case 'deduction':
                $this->requireAny($user, ['Drivers edit']);
                $record = DriverDeduction::findOrFail($id);
                $record->update(['archived_at' => now()]);
                $message = 'Deduction archived.';
                break;

            case 'work_order':
                $this->requireAny($user, ['Vehicles edit', 'Trailers edit', 'FleetRegister edit']);
                WorkOrders::findOrFail($id)->delete();
                $message = 'Maintenance record archived.';
                break;

            case 'fuel':
                $this->requireAny($user, ['Vehicles edit']);
                FuelModel::findOrFail($id)->delete();
                $message = 'Fuel log archived.';
                break;

            case 'advance':
                $this->requireAny($user, ['Drivers edit', 'Advances delete']);
                DriverAdvance::findOrFail($id)->delete();
                $message = 'Advance archived.';
                break;

            case 'assignment':
                $this->requireAny($user, ['Trailers edit']);
                $record = TrailerAssignment::findOrFail($id);
                if ($record->released_at) {
                    $record->delete();
                    $message = 'Pairing archived.';
                } else {
                    $record->update(['released_at' => now()->toDateString()]);
                    $message = 'Pairing released.';
                }
                break;

            default:
                abort(403, 'This record type cannot be archived.');
        }

        return back()->with('success', $message);
    }

    private function payload(string $type, $id, bool $print): array
    {
        $this->assertFleetUser(request());
        $hub = app(FleetHubService::class);

        $title = 'Fleet record';
        $subtitle = null;
        $fields = [];
        $lines = null;
        $notes = null;
        $filename = 'fleet-record-' . $id;

        switch ($type) {
            case 'vehicle':
                $v = VehicleModel::with(['group', 'types', 'logisticsProfile', 'drivers'])->findOrFail($id);
                $title = 'Truck profile · ' . ($v->license_plate ?: '#' . $v->id);
                $subtitle = trim(($v->make_name ?? '') . ' ' . ($v->model_name ?? '') . ' ' . ($v->year ?? ''));
                $fields = [
                    'Registration / plate' => $v->license_plate ?: '—',
                    'Make / model' => trim(($v->make_name ?? '') . ' ' . ($v->model_name ?? '')) ?: '—',
                    'Year' => $v->year ?: '—',
                    'VIN' => $v->vin ?: '—',
                    'Fuel / engine type' => $v->engine_type ?: '—',
                    'Odometer' => number_format((float) ($v->mileage ?: $v->int_mileage), 0) . ' km',
                    'Group / type' => trim(optional($v->group)->name . ' · ' . optional($v->types)->displayname, ' ·') ?: '—',
                    'Operational status' => ucfirst(str_replace('_', ' ', $hub->vehicleStatus($v))),
                    'Depot' => $v->logisticsProfile?->depot ?: '—',
                    'Next service' => $v->logisticsProfile?->next_service_date?->format('d M Y') ?: '—',
                    'Title holder' => $v->logisticsProfile?->title_holder ?: '—',
                    'Assigned drivers' => $v->drivers->pluck('name')->implode(', ') ?: '—',
                ];
                $filename = 'truck-' . ($v->license_plate ?: $v->id);
                break;

            case 'driver':
                $d = User::where('user_type', 'D')->with(['driverComplianceProfile', 'vehicles'])->findOrFail($id);
                $p = $d->driverComplianceProfile;
                $title = 'Driver profile · ' . $d->name;
                $subtitle = $p?->employee_number ? 'Employee ' . $p->employee_number : $d->email;
                $fields = [
                    'Name' => $d->name,
                    'Email' => $d->email ?: '—',
                    'Phone' => $d->getMeta('phone') ?: $d->getMeta('mobno') ?: '—',
                    'Employment status' => ucfirst(str_replace('_', ' ', $hub->driverStatus($d))),
                    'Employee number' => $p?->employee_number ?: '—',
                    'National ID' => $p?->national_id ?: '—',
                    'Licence' => trim(($p?->licence_number ?: '') . ' ' . ($p?->licence_class ? '(' . $p->licence_class . ')' : '')),
                    'Licence expiry' => $p?->licence_expiry?->format('d M Y') ?: '—',
                    'Medical expiry' => $p?->medical_expiry?->format('d M Y') ?: '—',
                    'Passport expiry' => $p?->passport_expiry?->format('d M Y') ?: '—',
                    'Home depot' => $p?->home_depot ?: '—',
                    'Assigned trucks' => $d->vehicles->pluck('license_plate')->implode(', ') ?: '—',
                ];
                $filename = 'driver-' . $d->id;
                break;

            case 'trailer':
                $t = Trailer::with(['currentAssignment.vehicle'])->findOrFail($id);
                $title = 'Trailer profile · ' . $t->registration_number;
                $subtitle = trim(($t->trailer_type ?? '') . ' ' . ($t->make ?? '') . ' ' . ($t->model ?? ''));
                $fields = [
                    'Trailer ID' => $t->registration_number,
                    'Type' => $t->trailer_type ?: '—',
                    'Make / model' => trim(($t->make ?? '') . ' ' . ($t->model ?? '')) ?: '—',
                    'Year' => $t->year ?: '—',
                    'VIN / chassis' => $t->chassis_number ?: '—',
                    'Axles' => $t->axle_configuration ?: '—',
                    'Payload capacity' => $t->payload_capacity_kg ? number_format($t->payload_capacity_kg) . ' kg' : '—',
                    'Tare / GVM' => trim(($t->tare_kg ? $t->tare_kg . ' kg' : '') . ' / ' . ($t->gvm_kg ? $t->gvm_kg . ' kg' : ''), ' /') ?: '—',
                    'Status' => ucfirst(str_replace('_', ' ', $t->status)),
                    'Depot' => $t->depot ?: '—',
                    'Currently attached to' => $t->currentAssignment?->vehicle?->license_plate ?: '—',
                ];
                $filename = 'trailer-' . $t->registration_number;
                break;

            case 'compliance':
                $doc = ComplianceDocument::with('type')->findOrFail($id);
                $owner = $this->ownerLabel($doc->owner_type, $doc->owner_id);
                $title = 'Compliance document · ' . (optional($doc->type)->name ?: 'Document');
                $subtitle = $owner;
                $countries = Country::whereIn('id', (array) $doc->coverage_country_ids)->pluck('name')->implode(', ');
                $fields = [
                    'Belongs to' => $owner,
                    'Document type' => optional($doc->type)->name ?: '—',
                    'Document / policy number' => $doc->document_number ?: '—',
                    'Issue date' => $doc->issue_date?->format('d M Y') ?: '—',
                    'First registration' => $doc->first_registration_date?->format('d M Y') ?: '—',
                    'Expiry date' => $doc->expiry_date?->format('d M Y') ?: 'No expiry',
                    'Status' => ucfirst(str_replace('_', ' ', $hub->expiryState($doc->expiry_date, $doc->alert_days ?: 30, (bool) $doc->archived_at))),
                    'Coverage' => $countries ?: 'Unrestricted',
                    'File on record' => $doc->file_path ? 'Yes' : 'No',
                ];
                $notes = $doc->notes;
                $filename = 'compliance-' . $doc->id;
                break;

            case 'work_order':
                $wo = WorkOrders::with(['vehicle', 'trailer', 'vendor', 'mechanic', 'parts.part'])->withTrashed()->findOrFail($id);
                $title = 'Maintenance record · #' . $wo->id;
                $subtitle = $wo->vehicle?->license_plate ?: $wo->trailer?->registration_number;
                $fields = [
                    'Asset' => $wo->vehicle?->license_plate ?: ($wo->trailer?->registration_number ?: '—'),
                    'Type' => ucfirst(str_replace('_', ' ', $wo->maintenance_type ?: 'repair')),
                    'Status' => ucfirst(str_replace('_', ' ', $wo->status ?: '—')),
                    'Required by' => $wo->required_by?->format('d M Y') ?: '—',
                    'Completed on' => $wo->completed_on?->format('d M Y') ?: '—',
                    'Vendor' => optional($wo->vendor)->name ?: '—',
                    'Mechanic' => optional($wo->mechanic)->name ?: '—',
                    'Odometer' => $wo->meter ? number_format((float) $wo->meter) . ' km' : '—',
                    'Cost' => $wo->price !== null ? Money::format(Money::toMinor($wo->price)) : '—',
                ];
                $notes = trim(($wo->description ?: '') . "\n" . ($wo->note ?: '') . "\n" . ($wo->resolution ?: ''));
                if ($wo->parts->isNotEmpty()) {
                    $lines = [
                        'headers' => ['Part', 'Qty', 'Unit price', 'Total'],
                        'rows' => $wo->parts->map(fn ($used) => [
                            optional($used->part)->title ?: optional($used->part)->description ?: 'Part #' . $used->part_id,
                            $used->qty,
                            $used->price !== null ? Money::format(Money::toMinor($used->price)) : '—',
                            $used->total !== null ? Money::format(Money::toMinor($used->total)) : '—',
                        ])->all(),
                    ];
                }
                $filename = 'maintenance-' . $wo->id;
                break;

            case 'fuel':
                $fuel = FuelModel::withTrashed()->with('vehicle_data')->findOrFail($id);
                $title = 'Fuel log · #' . $fuel->id;
                $subtitle = optional($fuel->vehicle_data)->license_plate ?: ('Vehicle #' . $fuel->vehicle_id);
                $fields = [
                    'Vehicle' => optional($fuel->vehicle_data)->license_plate ?: '—',
                    'Date' => $fuel->date ? \Carbon\Carbon::parse($fuel->date)->format('d M Y') : '—',
                    'Quantity' => $fuel->qty !== null ? $fuel->qty . ' L' : '—',
                    'Consumption' => $fuel->consumption !== null ? $fuel->consumption . ' km/L' : '—',
                    'Complete fill' => $fuel->complete ? 'Yes' : 'No',
                ];
                $notes = $fuel->note;
                $filename = 'fuel-' . $fuel->id;
                break;

            case 'trip':
                $trip = Trip::with(['vehicle', 'trailer', 'driver', 'customer', 'originCountry', 'destinationCountry'])->withTrashed()->findOrFail($id);
                $title = 'Trip · ' . $trip->trip_number;
                $subtitle = $trip->route_label;
                $fields = [
                    'Status' => $trip->status_label,
                    'Customer' => optional($trip->customer)->name ?: '—',
                    'Truck' => optional($trip->vehicle)->license_plate ?: '—',
                    'Trailer' => optional($trip->trailer)->registration_number ?: '—',
                    'Driver' => optional($trip->driver)->name ?: '—',
                    'Cargo' => $trip->cargo_description ?: '—',
                    'Cargo weight' => $trip->cargo_weight_kg ? number_format((float) $trip->cargo_weight_kg, 0) . ' kg' : '—',
                    'Planned start' => $trip->planned_start_at?->format('d M Y H:i') ?: '—',
                    'Actual start' => $trip->actual_start_at?->format('d M Y H:i') ?: '—',
                    'Actual end' => $trip->actual_end_at?->format('d M Y H:i') ?: '—',
                    'Distance' => $trip->distance_km ? number_format((float) $trip->distance_km, 1) . ' km' : '—',
                    'Revenue' => $trip->revenue_amount !== null
                        ? Money::format(Money::toMinor($trip->revenue_amount), $trip->revenue_currency) : '—',
                    'Consignee' => trim(($trip->consignee_name ?: '') . ' ' . ($trip->consignee_phone ? '· ' . $trip->consignee_phone : '')) ?: '—',
                ];
                $notes = $trip->notes;
                $filename = 'trip-' . $trip->trip_number;
                break;

            case 'incident':
                $incident = DriverIncident::with(['driver', 'trip'])->findOrFail($id);
                $title = 'Driver incident · ' . $incident->type_label;
                $subtitle = optional($incident->driver)->name;
                $fields = [
                    'Driver' => optional($incident->driver)->name ?: '—',
                    'Type' => $incident->type_label,
                    'Status' => $incident->status_label,
                    'Occurred at' => $incident->occurred_at?->format('d M Y H:i') ?: '—',
                    'Location' => $incident->location ?: '—',
                    'Trip' => optional($incident->trip)->trip_number ?: '—',
                    'Fine' => $incident->fine_amount !== null
                        ? Money::format(Money::toMinor($incident->fine_amount), $incident->currency_code) : '—',
                    'Points' => $incident->points !== null ? (string) $incident->points : '—',
                    'Resolved at' => $incident->resolved_at?->format('d M Y H:i') ?: '—',
                ];
                $notes = trim(($incident->description ?: '') . "\n" . ($incident->action_taken ?: ''));
                $filename = 'incident-' . $incident->id;
                break;

            case 'deduction':
                $deduction = DriverDeduction::with('driver')->findOrFail($id);
                $title = 'Driver deduction · ' . $deduction->type_label;
                $subtitle = optional($deduction->driver)->name;
                $fields = [
                    'Driver' => optional($deduction->driver)->name ?: '—',
                    'Type' => $deduction->type_label,
                    'Description' => $deduction->description,
                    'Amount' => Money::format(Money::toMinor($deduction->amount), $deduction->currency_code),
                    'Outstanding balance' => $deduction->balance !== null
                        ? Money::format(Money::toMinor($deduction->balance), $deduction->currency_code) : '—',
                    'Incurred on' => $deduction->incurred_on?->format('d M Y') ?: '—',
                    'Status' => ucfirst($deduction->status),
                ];
                $filename = 'deduction-' . $deduction->id;
                break;

            case 'advance':
                $advance = DriverAdvance::with(['driver', 'trip'])->withTrashed()->findOrFail($id);
                $title = 'Driver advance · ' . ucfirst($advance->purpose ?: 'advance');
                $subtitle = optional($advance->driver)->name;
                $fields = [
                    'Driver' => optional($advance->driver)->name ?: '—',
                    'Trip' => optional($advance->trip)->trip_number ?: '—',
                    'Amount' => Money::format(Money::toMinor($advance->amount), $advance->currency),
                    'Base amount' => Money::format(Money::toMinor($advance->amount_base)),
                    'Purpose' => ucfirst($advance->purpose ?: '—'),
                    'Method' => ucfirst($advance->method ?: '—'),
                    'Reference' => $advance->reference ?: '—',
                    'Issued at' => $advance->issued_at?->format('d M Y') ?: '—',
                ];
                $notes = $advance->notes;
                $filename = 'advance-' . $advance->id;
                break;

            case 'assignment':
                $assignment = TrailerAssignment::with(['trailer', 'vehicle', 'driver'])->withTrashed()->findOrFail($id);
                $title = 'Trailer pairing · ' . optional($assignment->trailer)->registration_number;
                $subtitle = optional($assignment->vehicle)->license_plate;
                $fields = [
                    'Trailer' => optional($assignment->trailer)->registration_number ?: '—',
                    'Truck' => optional($assignment->vehicle)->license_plate ?: '—',
                    'Driver' => optional($assignment->driver)->name ?: '—',
                    'Assigned at' => $assignment->assigned_at?->format('d M Y') ?: '—',
                    'Released at' => $assignment->released_at?->format('d M Y') ?: 'Still attached',
                    'Odometer out' => $assignment->odometer_out !== null ? number_format((float) $assignment->odometer_out) . ' km' : '—',
                    'Odometer in' => $assignment->odometer_in !== null ? number_format((float) $assignment->odometer_in) . ' km' : '—',
                ];
                $notes = $assignment->notes;
                $filename = 'pairing-' . $assignment->id;
                break;

            case 'telematics':
                $ping = VehicleTelematics::with('vehicle')->findOrFail($id);
                $title = 'Telematics ping · #' . $ping->id;
                $subtitle = optional($ping->vehicle)->license_plate;
                $fields = [
                    'Vehicle' => optional($ping->vehicle)->license_plate ?: '—',
                    'Recorded at' => $ping->recorded_at?->format('d M Y H:i:s') ?: '—',
                    'Position' => $ping->latitude !== null ? $ping->latitude . ', ' . $ping->longitude : '—',
                    'Speed' => $ping->speed_kph !== null ? $ping->speed_kph . ' km/h' : '—',
                    'Odometer' => $ping->odometer !== null ? number_format((float) $ping->odometer, 1) . ' km' : '—',
                    'Fuel level' => $ping->fuel_level !== null ? $ping->fuel_level . '%' : '—',
                    'Ignition' => $ping->ignition === null ? '—' : ($ping->ignition ? 'On' : 'Off'),
                    'Source' => $ping->source ?: '—',
                ];
                $filename = 'telematics-' . $ping->id;
                break;

            case 'component':
                $component = VehicleComponent::findOrFail($id);
                $title = 'Component · ' . ucfirst(str_replace('_', ' ', $component->component_type));
                $subtitle = $this->ownerLabel($component->owner_type, $component->owner_id);
                $fields = [
                    'Asset' => $this->ownerLabel($component->owner_type, $component->owner_id),
                    'Component' => ucfirst(str_replace('_', ' ', $component->component_type)),
                    'Serial number' => $component->serial_number ?: '—',
                    'Brand' => $component->brand ?: '—',
                    'Position' => $component->position ?: '—',
                    'Status' => ucfirst($component->status),
                    'Fitted' => $component->fitted_date?->format('d M Y') ?: '—',
                    'Fitted odometer' => $component->fitted_odometer !== null ? number_format((float) $component->fitted_odometer) . ' km' : '—',
                    'Removed' => $component->removed_date?->format('d M Y') ?: '—',
                    'Cost' => $component->cost !== null ? Money::format(Money::toMinor($component->cost)) : '—',
                    'Supplier' => $component->supplier ?: '—',
                ];
                $notes = $component->notes;
                $filename = 'component-' . $component->id;
                break;

            case 'rating':
                $rating = DriverRating::with(['driver', 'trip', 'customer'])->findOrFail($id);
                $title = 'Driver rating · ' . $rating->rating . '/5';
                $subtitle = optional($rating->driver)->name;
                $fields = [
                    'Driver' => optional($rating->driver)->name ?: '—',
                    'Customer' => optional($rating->customer)->name ?: '—',
                    'Trip' => optional($rating->trip)->trip_number ?: '—',
                    'Rating' => $rating->rating . ' / 5',
                    'Rated at' => $rating->rated_at?->format('d M Y') ?: '—',
                ];
                $notes = $rating->feedback;
                $filename = 'rating-' . $rating->id;
                break;

            default:
                abort(404);
        }

        return [
            'title' => $title,
            'subtitle' => $subtitle,
            'fields' => array_filter($fields, fn ($v) => $v !== null),
            'lines' => $lines,
            'notes' => $notes,
            'print' => $print,
            'filename' => preg_replace('/[^A-Za-z0-9._-]+/', '-', $filename),
            'company' => [
                'name' => AtlasLogistics::get('app_name') ?: 'Atlas Logistics',
                'address' => trim(implode(', ', array_filter([
                    AtlasLogistics::get('badd1'), AtlasLogistics::get('badd2'),
                    AtlasLogistics::get('city'), AtlasLogistics::get('country'),
                ]))),
                'email' => AtlasLogistics::get('email'),
                'phone' => AtlasLogistics::get('phone'),
            ],
        ];
    }

    private function ownerLabel($type, $id): string
    {
        return match ($type) {
            'vehicle' => 'Truck ' . (optional(VehicleModel::withTrashed()->find($id))->license_plate ?: '#' . $id),
            'trailer' => 'Trailer ' . (optional(Trailer::withTrashed()->find($id))->registration_number ?: '#' . $id),
            'driver' => 'Driver ' . (optional(User::withTrashed()->find($id))->name ?: '#' . $id),
            'company' => 'Company',
            default => ucfirst($type) . ' #' . $id,
        };
    }

    private function assertFleetUser(Request $request): void
    {
        $this->requireAny($request->user(), [
            'Vehicles list', 'Drivers list', 'Trailers list', 'Compliance list',
            'Trips list', 'FleetRegister list', 'Invoices list', 'Customer documents list',
        ]);
    }

    private function requireAny($user, array $permissions): void
    {
        abort_unless($user && collect($permissions)->contains(fn ($p) => $user->can($p)), 403);
    }
}
