<?php

namespace App\Http\Controllers\Admin\Fleet;

use App\Http\Controllers\Controller;
use App\Model\ComplianceDocument;
use App\Model\DocumentType;
use App\Model\FuelModel;
use App\Model\TrailerAssignment;
use App\Model\Trip;
use App\Model\VehicleModel;
use App\Model\VehicleTelematics;
use App\Model\WorkOrders;
use App\Services\FleetHubService;
use Illuminate\Http\Request;

class VehicleHubController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:Vehicles list');
    }

    public function show(Request $request, $id)
    {
        $hub = app(FleetHubService::class);

        $vehicle = VehicleModel::with(['group', 'types', 'drivers', 'logisticsProfile'])->findOrFail($id);
        $profile = $vehicle->logisticsProfile;

        $tabs = ['overview', 'compliance', 'maintenance', 'fuel', 'trips'];
        $tab = in_array($request->query('tab'), $tabs, true) ? $request->query('tab') : 'overview';

        $documentsBase = ComplianceDocument::with('type')->forOwner('vehicle', $vehicle->id);
        $docCounts = $hub->documentCounts('vehicle', $vehicle->id);
        $documents = $hub->filterDocuments(clone $documentsBase, $request)
            ->orderByRaw('expiry_date IS NULL, expiry_date')->orderByDesc('id')->get();

        $maintenanceBase = WorkOrders::with(['vendor', 'mechanic', 'parts.part'])
            ->where('vehicle_id', $vehicle->id);
        $maintenance = (clone $maintenanceBase)
            ->when($request->input('mnt_type'), fn ($q) => $q->where('maintenance_type', $request->input('mnt_type')))
            ->when($request->input('mnt_status'), fn ($q) => $q->where('status', $request->input('mnt_status')))
            ->when($request->input('mnt_from'), fn ($q) => $q->whereDate('created_on', '>=', $request->input('mnt_from')))
            ->when($request->input('mnt_to'), fn ($q) => $q->whereDate('created_on', '<=', $request->input('mnt_to')))
            ->orderByDesc('created_on')->orderByDesc('id')->get();

        $maintenanceTypes = ['scheduled' => 'Scheduled service', 'repair' => 'Repair', 'breakdown' => 'Breakdown',
            'tyre' => 'Tyres', 'brake' => 'Brakes', 'inspection' => 'Inspection', 'other' => 'Other'];
        $maintenanceStatuses = (clone $maintenanceBase)->whereNotNull('status')->distinct()->orderBy('status')->pluck('status')
            ->mapWithKeys(fn ($s) => [$s => ucfirst(str_replace('_', ' ', $s))])->all();
        if (!$maintenanceStatuses) {
            $maintenanceStatuses = ['pending' => 'Pending', 'in_progress' => 'In progress', 'completed' => 'Completed'];
        }

        $fuelBase = FuelModel::where('vehicle_id', $vehicle->id);
        $fuelLogs = (clone $fuelBase)
            ->when($request->input('fuel_from'), fn ($q) => $q->whereDate('date', '>=', $request->input('fuel_from')))
            ->when($request->input('fuel_to'), fn ($q) => $q->whereDate('date', '<=', $request->input('fuel_to')))
            ->orderByDesc('date')->orderByDesc('id')->get();

        $telematicsBase = VehicleTelematics::where('vehicle_id', $vehicle->id);
        $telematics = (clone $telematicsBase)
            ->when($request->input('tel_from'), fn ($q) => $q->whereDate('recorded_at', '>=', $request->input('tel_from')))
            ->when($request->input('tel_to'), fn ($q) => $q->whereDate('recorded_at', '<=', $request->input('tel_to')))
            ->orderByDesc('recorded_at')->limit(100)->get();
        $lastPing = (clone $telematicsBase)->orderByDesc('recorded_at')->first();

        $tripBase = Trip::with(['driver', 'customer', 'trailer', 'originCountry', 'destinationCountry'])
            ->where('vehicle_id', $vehicle->id);
        $trips = (clone $tripBase)
            ->when($request->input('trip_status'), fn ($q) => $q->where('status', $request->input('trip_status')))
            ->when($request->input('trip_from'), fn ($q) => $q->whereDate('planned_start_at', '>=', $request->input('trip_from')))
            ->when($request->input('trip_to'), fn ($q) => $q->whereDate('planned_start_at', '<=', $request->input('trip_to')))
            ->orderByDesc('planned_start_at')->orderByDesc('id')->get();

        $activeTrip = Trip::with(['driver', 'customer', 'trailer'])
            ->where('vehicle_id', $vehicle->id)
            ->whereIn('status', ['planned', 'loaded', 'dispatched', 'in_transit', 'at_border'])
            ->orderByDesc('planned_start_at')->first();

        $currentTrailer = TrailerAssignment::with('trailer')
            ->where('vehicle_id', $vehicle->id)->whereNull('released_at')->latest('assigned_at')->first();

        $kpis = [
            'trips' => (clone $tripBase)->count(),
            'completed_trips' => (clone $tripBase)->whereIn('status', ['completed', 'closed'])->count(),
            'distance' => (float) (clone $tripBase)->sum('distance_km'),
            'fuel_qty' => (float) (clone $fuelBase)->sum('qty'),
            'compliance_alerts' => $docCounts['expired'] + $docCounts['due_soon'],
            'odometer' => $vehicle->mileage ?: $vehicle->int_mileage,
        ];

        $payload = [
            'vehicle' => $vehicle,
            'profile' => $profile,
            'tab' => $tab,
            'vehicleStatus' => $hub->vehicleStatus($vehicle),
            'activeTrip' => $activeTrip,
            'currentTrailer' => $currentTrailer,
            'documents' => $documents,
            'docCounts' => $docCounts,
            'documentTypes' => DocumentType::where('applies_to', 'vehicle')->where('is_active', true)->orderBy('name')->get(),
            'maintenance' => $maintenance,
            'maintenanceTypes' => $maintenanceTypes,
            'maintenanceStatuses' => $maintenanceStatuses,
            'fuelLogs' => $fuelLogs,
            'telematics' => $telematics,
            'lastPing' => $lastPing,
            'trips' => $trips,
            'kpis' => $kpis,
            'tripStatuses' => config('logistics.trip_statuses', []),
        ];

        return $request->wantsJson()
            ? response()->json($payload)
            : view('vehicles.show', $payload);
    }
}
