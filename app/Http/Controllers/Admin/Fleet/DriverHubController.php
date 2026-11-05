<?php

namespace App\Http\Controllers\Admin\Fleet;

use App\Http\Controllers\Controller;
use App\Model\ComplianceDocument;
use App\Model\DriverAdvance;
use App\Model\DriverDeduction;
use App\Model\DriverIncident;
use App\Model\DriverPayrollProfile;
use App\Model\DriverRating;
use App\Model\DocumentType;
use App\Model\Trip;
use App\Model\User;
use App\Services\FleetHubService;
use App\Support\Money;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class DriverHubController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:Drivers list');
        $this->middleware('permission:Drivers edit', ['only' => [
            'storeIncident', 'updateIncident', 'archiveIncident', 'storeRating',
            'updatePayroll', 'storeDeduction', 'updateDeduction', 'archiveDeduction',
        ]]);
    }

    public function show(Request $request, $id)
    {
        $hub = app(FleetHubService::class);

        $driver = User::where('user_type', 'D')
            ->with(['driverComplianceProfile', 'payrollProfile', 'vehicles'])
            ->findOrFail($id);
        $profile = $driver->driverComplianceProfile;

        $tabs = ['overview', 'compliance', 'performance', 'incidents', 'financials'];
        $tab = in_array($request->query('tab'), $tabs, true) ? $request->query('tab') : 'overview';

        $documentsBase = ComplianceDocument::with('type')->forOwner('driver', $driver->id);
        $docCounts = $hub->documentCounts('driver', $driver->id);
        $documents = $hub->filterDocuments(clone $documentsBase, $request)
            ->orderByRaw('expiry_date IS NULL, expiry_date')->orderByDesc('id')->get();

        $tripBase = Trip::with(['vehicle', 'trailer', 'customer', 'originCountry', 'destinationCountry'])
            ->where(fn ($q) => $q->where('driver_id', $driver->id)->orWhere('co_driver_id', $driver->id));
        $trips = (clone $tripBase)
            ->when($request->input('trip_status'), fn ($q) => $q->where('status', $request->input('trip_status')))
            ->when($request->input('trip_from'), fn ($q) => $q->whereDate('planned_start_at', '>=', $request->input('trip_from')))
            ->when($request->input('trip_to'), fn ($q) => $q->whereDate('planned_start_at', '<=', $request->input('trip_to')))
            ->orderByDesc('planned_start_at')->orderByDesc('id')->get();

        $ratings = DriverRating::with(['trip', 'customer'])
            ->where('driver_id', $driver->id)->orderByDesc('rated_at')->orderByDesc('id')->get();
        $averageRating = $ratings->count() ? round($ratings->avg('rating'), 1) : null;

        $incidentsBase = DriverIncident::with('trip')->where('driver_id', $driver->id);
        $incidents = (clone $incidentsBase)
            ->when($request->input('inc_type'), fn ($q) => $q->where('type', $request->input('inc_type')))
            ->when($request->input('inc_status') === 'archived', fn ($q) => $q->whereNotNull('archived_at'))
            ->when(!$request->input('inc_status'), fn ($q) => $q->whereNull('archived_at'))
            ->when($request->input('inc_status') && $request->input('inc_status') !== 'archived',
                fn ($q) => $q->whereNull('archived_at')->where('status', $request->input('inc_status')))
            ->when($request->input('inc_from'), fn ($q) => $q->whereDate('occurred_at', '>=', $request->input('inc_from')))
            ->when($request->input('inc_to'), fn ($q) => $q->whereDate('occurred_at', '<=', $request->input('inc_to')))
            ->orderByDesc('occurred_at')->orderByDesc('id')->get();
        $openIncidents = (clone $incidentsBase)->whereNull('archived_at')->whereIn('status', ['open', 'under_review', 'contested'])->count();

        $advances = DriverAdvance::with('trip')
            ->where('driver_id', $driver->id)
            ->when($request->input('fin_from'), fn ($q) => $q->whereDate('issued_at', '>=', $request->input('fin_from')))
            ->when($request->input('fin_to'), fn ($q) => $q->whereDate('issued_at', '<=', $request->input('fin_to')))
            ->orderByDesc('issued_at')->get();

        $deductionsBase = DriverDeduction::where('driver_id', $driver->id);
        $deductions = (clone $deductionsBase)
            ->when($request->input('ded_type'), fn ($q) => $q->where('type', $request->input('ded_type')))
            ->when($request->input('ded_status') === 'archived', fn ($q) => $q->whereNotNull('archived_at'))
            ->when(!$request->input('ded_status'), fn ($q) => $q->whereNull('archived_at'))
            ->when($request->input('ded_status') && $request->input('ded_status') !== 'archived',
                fn ($q) => $q->whereNull('archived_at')->where('status', $request->input('ded_status')))
            ->when($request->input('fin_from'), fn ($q) => $q->whereDate('incurred_on', '>=', $request->input('fin_from')))
            ->when($request->input('fin_to'), fn ($q) => $q->whereDate('incurred_on', '<=', $request->input('fin_to')))
            ->orderByDesc('incurred_on')->orderByDesc('id')->get();

        $kpis = [
            'trips' => (clone $tripBase)->count(),
            'completed_trips' => (clone $tripBase)->whereIn('status', ['completed', 'closed'])->count(),
            'distance' => (float) (clone $tripBase)->sum('distance_km'),
            'rating' => $averageRating,
            'open_incidents' => $openIncidents,
            'compliance_alerts' => $docCounts['expired'] + $docCounts['due_soon'],
            'advances_total' => (float) $advances->sum('amount_base'),
            'deductions_total' => (float) $deductions->whereNull('archived_at')->where('status', 'active')->sum('amount_base'),
        ];

        $activeTrip = Trip::with(['vehicle', 'trailer', 'customer'])
            ->where(fn ($q) => $q->where('driver_id', $driver->id)->orWhere('co_driver_id', $driver->id))
            ->whereIn('status', ['planned', 'loaded', 'dispatched', 'in_transit', 'at_border'])
            ->orderByDesc('planned_start_at')->first();

        $payload = [
            'driver' => $driver,
            'profile' => $profile,
            'payroll' => $driver->payrollProfile,
            'tab' => $tab,
            'driverStatus' => $hub->driverStatus($driver),
            'activeTrip' => $activeTrip,
            'documents' => $documents,
            'docCounts' => $docCounts,
            'documentTypes' => DocumentType::where('applies_to', 'driver')->where('is_active', true)->orderBy('name')->get(),
            'trips' => $trips,
            'tripOptions' => (clone $tripBase)->orderByDesc('planned_start_at')->get(),
            'ratings' => $ratings,
            'averageRating' => $averageRating,
            'incidents' => $incidents,
            'advances' => $advances,
            'deductions' => $deductions,
            'kpis' => $kpis,
            'tripStatuses' => config('logistics.trip_statuses', []),
            'incidentTypes' => DriverIncident::TYPES,
            'incidentStatuses' => DriverIncident::STATUSES,
            'deductionTypes' => DriverDeduction::TYPES,
            'deductionStatuses' => DriverDeduction::STATUSES,
            'customers' => User::where('user_type', 'C')->orderBy('name')->get(['id', 'name']),
            'currencies' => \App\Model\Currency::where('is_active', true)->orderBy('code')->get(['code', 'name']),
        ];

        return $request->wantsJson()
            ? response()->json($payload)
            : view('drivers.show', $payload);
    }

    public function storeIncident(Request $request, $id)
    {
        $driver = $this->driver($id);
        $data = $this->validateIncident($request, $driver->id);

        $data['driver_id'] = $driver->id;
        $data['created_by'] = $request->user()->id;
        $data['fine_amount_base'] = $this->baseAmount($data['fine_amount'] ?? null, $data['exchange_rate'] ?? 1);
        if (($data['status'] ?? '') === 'resolved' && empty($data['resolved_at'])) {
            $data['resolved_at'] = now();
        }

        DriverIncident::create($data);

        return redirect()->route('drivers.show', ['id' => $driver->id, 'tab' => 'incidents'])
            ->with('success', 'Incident logged.');
    }

    public function updateIncident(Request $request, $id, $incident)
    {
        $driver = $this->driver($id);
        $record = DriverIncident::where('driver_id', $driver->id)->findOrFail($incident);
        $data = $this->validateIncident($request, $driver->id);

        $data['fine_amount_base'] = $this->baseAmount($data['fine_amount'] ?? null, $data['exchange_rate'] ?? 1);
        if (($data['status'] ?? '') === 'resolved' && empty($data['resolved_at'])) {
            $data['resolved_at'] = now();
        }

        $record->update($data);

        return redirect()->route('drivers.show', ['id' => $driver->id, 'tab' => 'incidents'])
            ->with('success', 'Incident updated.');
    }

    public function archiveIncident($id, $incident)
    {
        $driver = $this->driver($id);
        DriverIncident::where('driver_id', $driver->id)->findOrFail($incident)
            ->update(['archived_at' => now()]);

        return back()->with('success', 'Incident archived.');
    }

    public function storeRating(Request $request, $id)
    {
        $driver = $this->driver($id);
        $data = $request->validate([
            'trip_id' => ['nullable', 'integer', Rule::exists('trips', 'id')->whereNull('deleted_at')],
            'customer_id' => ['nullable', 'integer', Rule::exists('users', 'id')->where('user_type', 'C')],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'feedback' => ['nullable', 'string', 'max:4000'],
            'rated_at' => ['nullable', 'date'],
        ]);

        if (!empty($data['trip_id'])) {
            $this->assertDriverTrip($data['trip_id'], $driver->id);
        }

        $data['driver_id'] = $driver->id;
        $data['rated_at'] = $data['rated_at'] ?? now()->toDateString();
        $data['created_by'] = $request->user()->id;
        DriverRating::create($data);

        return redirect()->route('drivers.show', ['id' => $driver->id, 'tab' => 'performance'])
            ->with('success', 'Rating recorded.');
    }

    public function updatePayroll(Request $request, $id)
    {
        $driver = $this->driver($id);
        $data = $request->validate([
            'salary_amount' => ['nullable', 'numeric', 'min:0'],
            'currency_code' => ['required', 'string', 'size:3', Rule::exists('currencies', 'code')->where('is_active', true)],
            'pay_frequency' => ['required', Rule::in(['monthly', 'fortnightly', 'weekly', 'per_trip'])],
            'daily_allowance' => ['nullable', 'numeric', 'min:0'],
            'fuel_allowance' => ['nullable', 'numeric', 'min:0'],
            'bank_name' => ['nullable', 'string', 'max:100'],
            'bank_account' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        DriverPayrollProfile::updateOrCreate(['user_id' => $driver->id], $data);

        return redirect()->route('drivers.show', ['id' => $driver->id, 'tab' => 'financials'])
            ->with('success', 'Payroll profile saved.');
    }

    public function storeDeduction(Request $request, $id)
    {
        $driver = $this->driver($id);
        $data = $this->validateDeduction($request);

        $data['driver_id'] = $driver->id;
        $data['created_by'] = $request->user()->id;
        $data['amount_base'] = $this->baseAmount($data['amount'], $data['exchange_rate'] ?? 1);
        DriverDeduction::create($data);

        return redirect()->route('drivers.show', ['id' => $driver->id, 'tab' => 'financials'])
            ->with('success', 'Deduction recorded.');
    }

    public function updateDeduction(Request $request, $id, $deduction)
    {
        $driver = $this->driver($id);
        $record = DriverDeduction::where('driver_id', $driver->id)->findOrFail($deduction);
        $data = $this->validateDeduction($request);
        $data['amount_base'] = $this->baseAmount($data['amount'], $data['exchange_rate'] ?? 1);
        $record->update($data);

        return redirect()->route('drivers.show', ['id' => $driver->id, 'tab' => 'financials'])
            ->with('success', 'Deduction updated.');
    }

    public function archiveDeduction($id, $deduction)
    {
        $driver = $this->driver($id);
        DriverDeduction::where('driver_id', $driver->id)->findOrFail($deduction)
            ->update(['archived_at' => now()]);

        return back()->with('success', 'Deduction archived.');
    }

    private function driver($id): User
    {
        return User::where('user_type', 'D')->findOrFail($id);
    }

    private function validateIncident(Request $request, $driverId): array
    {
        $data = $request->validate([
            'trip_id' => ['nullable', 'integer', Rule::exists('trips', 'id')->whereNull('deleted_at')],
            'type' => ['required', Rule::in(array_keys(DriverIncident::TYPES))],
            'occurred_at' => ['required', 'date'],
            'location' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:4000'],
            'fine_amount' => ['nullable', 'numeric', 'min:0'],
            'currency_code' => ['required', 'string', 'size:3', Rule::exists('currencies', 'code')->where('is_active', true)],
            'exchange_rate' => ['nullable', 'numeric', 'gt:0'],
            'points' => ['nullable', 'integer', 'min:0', 'max:100'],
            'status' => ['required', Rule::in(array_keys(DriverIncident::STATUSES))],
            'action_taken' => ['nullable', 'string', 'max:4000'],
            'resolved_at' => ['nullable', 'date'],
        ]);

        if (!empty($data['trip_id'])) {
            $this->assertDriverTrip($data['trip_id'], $driverId);
        }

        $data['exchange_rate'] = $data['exchange_rate'] ?? 1;

        return $data;
    }

    private function validateDeduction(Request $request): array
    {
        $data = $request->validate([
            'type' => ['required', Rule::in(array_keys(DriverDeduction::TYPES))],
            'description' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'currency_code' => ['required', 'string', 'size:3', Rule::exists('currencies', 'code')->where('is_active', true)],
            'exchange_rate' => ['nullable', 'numeric', 'gt:0'],
            'balance' => ['nullable', 'numeric', 'min:0'],
            'incurred_on' => ['required', 'date'],
            'status' => ['required', Rule::in(array_keys(DriverDeduction::STATUSES))],
        ]);

        $data['exchange_rate'] = $data['exchange_rate'] ?? 1;

        return $data;
    }

    private function assertDriverTrip($tripId, $driverId): void
    {
        $belongs = Trip::where('id', $tripId)
            ->where(fn ($q) => $q->where('driver_id', $driverId)->orWhere('co_driver_id', $driverId))
            ->exists();

        if (!$belongs) {
            throw ValidationException::withMessages(['trip_id' => 'Choose a trip assigned to this driver.']);
        }
    }

    private function baseAmount($amount, $rate): ?string
    {
        if ($amount === null || $amount === '') {
            return null;
        }

        return Money::fromMinor(Money::applyRate(Money::toMinor($amount), (string) ($rate ?: 1)));
    }
}
