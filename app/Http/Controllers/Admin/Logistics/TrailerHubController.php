<?php

namespace App\Http\Controllers\Admin\Logistics;

use App\Http\Controllers\Controller;
use App\Model\ComplianceDocument;
use App\Model\DocumentType;
use App\Model\Trailer;
use App\Model\TrailerAssignment;
use App\Model\Trip;
use App\Model\User;
use App\Model\VehicleComponent;
use App\Model\VehicleModel;
use App\Model\WorkOrders;
use App\Services\FleetHubService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TrailerHubController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:Trailers list');
        $this->middleware('permission:Trailers edit', ['only' => ['storeAssignment', 'releaseAssignment']]);
    }

    public function show(Request $request, $id)
    {
        $hub = app(FleetHubService::class);

        $trailer = Trailer::findOrFail($id);

        $tabs = ['overview', 'compliance', 'maintenance', 'assignments', 'utilization'];
        $tab = in_array($request->query('tab'), $tabs, true) ? $request->query('tab') : 'overview';

        $documentsBase = ComplianceDocument::with('type')->forOwner('trailer', $trailer->id);
        $docCounts = $hub->documentCounts('trailer', $trailer->id);
        $documents = $hub->filterDocuments(clone $documentsBase, $request)
            ->orderByRaw('expiry_date IS NULL, expiry_date')->orderByDesc('id')->get();

        $maintenanceBase = WorkOrders::with(['vehicle', 'vendor', 'mechanic', 'parts.part'])
            ->where('trailer_id', $trailer->id);
        $maintenance = (clone $maintenanceBase)
            ->when($request->input('mnt_type'), fn ($q) => $q->where('maintenance_type', $request->input('mnt_type')))
            ->when($request->input('mnt_status'), fn ($q) => $q->where('status', $request->input('mnt_status')))
            ->when($request->input('mnt_from'), fn ($q) => $q->whereDate('created_on', '>=', $request->input('mnt_from')))
            ->when($request->input('mnt_to'), fn ($q) => $q->whereDate('created_on', '<=', $request->input('mnt_to')))
            ->orderByDesc('created_on')->orderByDesc('id')->get();

        $maintenanceTypes = ['scheduled' => 'Scheduled service', 'repair' => 'Repair', 'breakdown' => 'Breakdown',
            'tyre' => 'Tyre replacement', 'brake' => 'Brake service', 'structural' => 'Structural repair', 'inspection' => 'Inspection', 'other' => 'Other'];
        $maintenanceStatuses = (clone $maintenanceBase)->whereNotNull('status')->distinct()->orderBy('status')->pluck('status')
            ->mapWithKeys(fn ($s) => [$s => ucfirst(str_replace('_', ' ', $s))])->all();
        if (!$maintenanceStatuses) {
            $maintenanceStatuses = ['pending' => 'Pending', 'in_progress' => 'In progress', 'completed' => 'Completed'];
        }

        $components = VehicleComponent::where('owner_type', 'trailer')->where('owner_id', $trailer->id)
            ->orderByDesc('fitted_date')->orderByDesc('id')->get();

        $assignmentsBase = TrailerAssignment::with(['vehicle', 'driver'])->where('trailer_id', $trailer->id);
        $assignments = (clone $assignmentsBase)
            ->when($request->input('asg_status') === 'current', fn ($q) => $q->whereNull('released_at'))
            ->when($request->input('asg_status') === 'released', fn ($q) => $q->whereNotNull('released_at'))
            ->when($request->input('asg_from'), fn ($q) => $q->whereDate('assigned_at', '>=', $request->input('asg_from')))
            ->when($request->input('asg_to'), fn ($q) => $q->whereDate('assigned_at', '<=', $request->input('asg_to')))
            ->orderByDesc('assigned_at')->orderByDesc('id')->get();
        $currentAssignment = (clone $assignmentsBase)->whereNull('released_at')->latest('assigned_at')->first();

        $tripBase = Trip::with(['driver', 'vehicle', 'customer', 'originCountry', 'destinationCountry'])
            ->where('trailer_id', $trailer->id);
        $trips = (clone $tripBase)
            ->when($request->input('trip_status'), fn ($q) => $q->where('status', $request->input('trip_status')))
            ->when($request->input('trip_from'), fn ($q) => $q->whereDate('planned_start_at', '>=', $request->input('trip_from')))
            ->when($request->input('trip_to'), fn ($q) => $q->whereDate('planned_start_at', '<=', $request->input('trip_to')))
            ->orderByDesc('planned_start_at')->orderByDesc('id')->get();

        $utilization = $hub->trailerUtilization($trailer);
        $cargoSummary = (clone $tripBase)->whereNotNull('cargo_description')->get()
            ->groupBy(fn ($trip) => $trip->cargo_description ?: 'Unspecified')
            ->map(fn ($group) => ['trips' => $group->count(), 'weight' => (float) $group->sum('cargo_weight_kg')])
            ->sortByDesc('trips')->take(10);

        $payload = [
            'trailer' => $trailer,
            'tab' => $tab,
            'documents' => $documents,
            'docCounts' => $docCounts,
            'documentTypes' => DocumentType::where('applies_to', 'trailer')->where('is_active', true)->orderBy('name')->get(),
            'maintenance' => $maintenance,
            'maintenanceTypes' => $maintenanceTypes,
            'maintenanceStatuses' => $maintenanceStatuses,
            'components' => $components,
            'assignments' => $assignments,
            'currentAssignment' => $currentAssignment,
            'vehicles' => VehicleModel::where('in_service', 1)->orderBy('license_plate')->get(),
            'drivers' => User::where('user_type', 'D')->orderBy('name')->get(),
            'trips' => $trips,
            'utilization' => $utilization,
            'cargoSummary' => $cargoSummary,
            'tripStatuses' => config('logistics.trip_statuses', []),
            'kpis' => [
                'trips' => (clone $tripBase)->count(),
                'distance' => (float) (clone $tripBase)->sum('distance_km'),
                'utilization' => $utilization['percent'],
                'compliance_alerts' => $docCounts['expired'] + $docCounts['due_soon'],
            ],
        ];

        return $request->wantsJson()
            ? response()->json($payload)
            : view('trailers.show', $payload);
    }

    public function storeAssignment(Request $request, $id)
    {
        $trailer = Trailer::findOrFail($id);
        $data = $request->validate([
            'vehicle_id' => ['nullable', 'integer', Rule::exists('vehicles', 'id')->whereNull('deleted_at')],
            'driver_id' => ['nullable', 'integer', Rule::exists('users', 'id')->where('user_type', 'D')->whereNull('deleted_at')],
            'assigned_at' => ['required', 'date'],
            'odometer_out' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        if (empty($data['vehicle_id']) && empty($data['driver_id'])) {
            return back()->withErrors(['vehicle_id' => 'Choose a truck or a driver for this pairing.'])->withInput();
        }

        // A trailer has one open pairing; a truck pulls one trailer at a time.
        TrailerAssignment::where('trailer_id', $trailer->id)->whereNull('released_at')
            ->update(['released_at' => $data['assigned_at']]);
        if (!empty($data['vehicle_id'])) {
            TrailerAssignment::where('vehicle_id', $data['vehicle_id'])->whereNull('released_at')
                ->update(['released_at' => $data['assigned_at']]);
        }

        TrailerAssignment::create($data + [
            'trailer_id' => $trailer->id,
            'created_by' => $request->user()->id,
        ]);

        return redirect()->route('logistics.trailers.show', ['id' => $trailer->id, 'tab' => 'assignments'])
            ->with('success', 'Trailer pairing recorded.');
    }

    public function releaseAssignment(Request $request, $id, $assignment)
    {
        $trailer = Trailer::findOrFail($id);
        $record = TrailerAssignment::where('trailer_id', $trailer->id)->findOrFail($assignment);

        $data = $request->validate([
            'released_at' => ['nullable', 'date'],
            'odometer_in' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $data['released_at'] = $data['released_at'] ?? now()->toDateString();
        $record->update($data);

        return redirect()->route('logistics.trailers.show', ['id' => $trailer->id, 'tab' => 'assignments'])
            ->with('success', 'Trailer pairing released.');
    }
}
