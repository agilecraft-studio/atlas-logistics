<?php
declare(strict_types=1);

namespace App\Http\Controllers\Admin\Logistics;

use App\Model\BorderPost;
use App\Model\Country;
use App\Model\Currency;
use App\Model\Trip;
use App\Model\TripCheckpoint;
use App\Model\TripExpenseCategory;
use App\Model\TripIncident;
use App\Model\Trailer;
use App\Model\VehicleLogisticsProfile;
use App\Model\VehicleModel;
use App\Model\User;
use App\Services\ComplianceService;
use App\Services\CurrencyService;
use App\Services\TripFinanceService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class TripsController extends CrudController
{
    protected string $permission = 'Trips';
    protected string $routePrefix = 'logistics.trips';
    protected string $title = 'Trips';
    protected string $singular = 'Trip';
    protected array $with = ['vehicle', 'driver', 'customer', 'originCountry', 'destinationCountry'];

    public function __construct()
    {
        parent::__construct();
        $this->middleware('permission:Trips start', ['only' => ['start']]);
        $this->middleware('permission:Trips edit', ['only' => ['complete', 'close', 'cancel', 'addCheckpoint', 'deleteCheckpoint', 'addIncident', 'deleteIncident']]);
    }

    protected function model(): string { return Trip::class; }

    protected function query()
    {
        $q = Trip::query()->with($this->with)->orderByDesc('id');
        if (request()->has('status') && array_key_exists(request('status'), config('logistics.trip_statuses'))) {
            $q->where('status', request('status'));
        }
        return $q;
    }

    protected function fields(): array
    {
        $co = Country::where('is_active', true)->pluck('name', 'id')->toArray();
        return [
            ['name' => 'customer_id', 'label' => 'Customer', 'type' => 'select', 'options' => User::where('user_type', 'C')->pluck('name', 'id')->toArray(), 'rules' => 'nullable|exists:users,id', 'col' => 4],
            ['name' => 'trip_type', 'label' => 'Trip type', 'type' => 'select', 'options' => config('logistics.trip_types'), 'rules' => 'nullable|in:' . implode(',', array_keys(config('logistics.trip_types'))), 'col' => 4],
            ['name' => 'vehicle_id', 'label' => 'Truck', 'type' => 'select', 'options' => $this->vehicleOptions(), 'rules' => 'required|exists:vehicles,id', 'col' => 4],
            ['name' => 'trailer_id', 'label' => 'Trailer', 'type' => 'select', 'options' => Trailer::pluck('registration_number', 'id')->toArray(), 'rules' => 'nullable|exists:trailers,id', 'col' => 4],
            ['name' => 'driver_id', 'label' => 'Driver', 'type' => 'select', 'options' => User::where('user_type', 'D')->pluck('name', 'id')->toArray(), 'rules' => 'required|exists:users,id', 'col' => 4],
            ['name' => 'co_driver_id', 'label' => 'Co-driver (optional)', 'type' => 'select', 'options' => User::where('user_type', 'D')->pluck('name', 'id')->toArray(), 'rules' => 'nullable|exists:users,id|different:driver_id', 'col' => 4],
            ['name' => 'origin_country_id', 'label' => 'From (country)', 'type' => 'select', 'options' => $co, 'rules' => 'nullable|exists:countries,id', 'col' => 4],
            ['name' => 'origin_city', 'label' => 'From (city / depot)', 'rules' => 'nullable|string|max:100', 'col' => 4],
            ['name' => 'destination_country_id', 'label' => 'To (country)', 'type' => 'select', 'options' => $co, 'rules' => 'nullable|exists:countries,id', 'col' => 4],
            ['name' => 'destination_city', 'label' => 'To (city / depot)', 'rules' => 'nullable|string|max:100', 'col' => 4],
            ['name' => 'planned_start_at', 'label' => 'Planned departure', 'type' => 'datetime', 'rules' => 'nullable|date', 'col' => 4],
            ['name' => 'planned_end_at', 'label' => 'Planned arrival', 'type' => 'datetime', 'rules' => 'nullable|date|after_or_equal:planned_start_at', 'col' => 4],
            ['name' => 'revenue_amount', 'label' => 'Revenue (agreed price)', 'type' => 'number', 'step' => '0.01', 'rules' => 'required|numeric|min:0', 'col' => 4],
            ['name' => 'revenue_currency', 'label' => 'Revenue currency', 'type' => 'select', 'options' => Currency::pluck('code', 'code')->toArray(), 'rules' => 'required|string|size:3|exists:currencies,code', 'col' => 4],
            ['name' => 'revenue_rate', 'label' => 'Exchange rate to ' . CurrencyService::base(), 'type' => 'number', 'step' => '0.000001', 'rules' => 'nullable|numeric|gt:0', 'col' => 4],
        ];
    }

    protected function prepare(array $data, Request $request, $id): array
    {
        [$rate, $base] = app(CurrencyService::class)->toBase(
            (float) $data['revenue_amount'], $data['revenue_currency'], $data['revenue_rate'] ?? null,
            $data['planned_start_at'] ?? null, 'revenue_rate'
        );
        $data['revenue_rate'] = $rate;
        $data['revenue_amount_base'] = $base;

        if (!$id) {
            $data['trip_number'] = $this->nextNumber();
            $data['status'] = 'planned';
            $data['created_by'] = auth()->id();
        }
        return $data;
    }

    protected function columns(): array
    {
        return [
            ['label' => 'Trip no', 'field' => 'trip_number'],
            ['label' => 'Route', 'value' => fn ($r) => $r->route_label],
            ['label' => 'Truck', 'value' => fn ($r) => optional($r->vehicle)->license_plate],
            ['label' => 'Driver', 'value' => fn ($r) => optional($r->driver)->name],
            ['label' => 'Customer', 'value' => fn ($r) => optional($r->customer)->name],
            ['label' => 'Status', 'value' => fn ($r) => $r->status_label, 'type' => 'badge'],
        ];
    }

    protected function rowLinks($row): array
    {
        return [
            ['label' => 'Open', 'url' => route('logistics.trips.show', $row->id), 'class' => 'btn-info'],
        ];
    }

    protected function headerLinks(): array
    {
        $out = [];
        foreach (config('logistics.trip_statuses') as $key => $label) {
            $out[] = ['label' => $label, 'url' => route('logistics.trips.index', ['status' => $key])];
        }
        return $out;
    }

    protected function canDelete($row): bool
    {
        return $row->status === 'planned';
    }

    public function show($id, TripFinanceService $finance)
    {
        $trip = Trip::with([
            'vehicle', 'trailer', 'driver', 'coDriver', 'customer',
            'originCountry', 'destinationCountry',
            'checkpoints.borderPost', 'expenses.category', 'expenses.country',
            'advances.driver', 'incidents.country',
        ])->findOrFail($id);

        return view('logistics.trips.show', [
            'trip'       => $trip,
            'summary'    => $finance->summary($trip),
            'blockers'   => $trip->status === 'planned' ? app(ComplianceService::class)->dispatchBlockers($trip) : [],
            'currencies' => Currency::where('is_active', true)->orderBy('code')->get(),
            'categories' => TripExpenseCategory::where('is_active', true)->orderBy('name')->get(),
            'countries'  => Country::where('is_active', true)->orderBy('name')->get(),
            'borders'    => BorderPost::orderBy('name')->get(),
            'base'       => CurrencyService::base(),
        ]);
    }

    // ---- Only planned trips may be edited (a started trip must not change truck, driver or price) ----
    public function edit()
    {
        $trip = Trip::findOrFail($this->id());
        if ($trip->status !== 'planned') {
            return redirect()->route('logistics.trips.show', ['id' => $trip->id])->with('error', 'Only planned trips can be edited.');
        }
        return parent::edit();
    }

    public function update(Request $request)
    {
        $trip = Trip::findOrFail($this->id());
        if ($trip->status !== 'planned') {
            return redirect()->route('logistics.trips.show', ['id' => $trip->id])->with('error', 'Only planned trips can be edited.');
        }
        return parent::update($request);
    }

    protected function nextNumber(): string
    {
        $n = Trip::withTrashed()->whereYear('created_at', now()->year)->count() + 1;
        do {
            $number = 'TRP-' . now()->year . '-' . str_pad((string) $n, 4, '0', STR_PAD_LEFT);
            $n++;
        } while (Trip::withTrashed()->where('trip_number', $number)->exists());
        return $number;
    }

    /** Mark truck + trailer on_trip, or release them to available (only if they were on_trip). */
    protected function setAssets(Trip $trip, string $status): void
    {
        $p = VehicleLogisticsProfile::firstOrNew(['vehicle_id' => $trip->vehicle_id]);
        if ($status === 'on_trip' || $p->logistics_status === 'on_trip') {
            $p->logistics_status = $status;
            $p->save();
        }
        if ($trip->trailer_id) {
            Trailer::where('id', $trip->trailer_id)
                ->when($status === 'available', fn ($q) => $q->where('status', 'on_trip'))
                ->update(['status' => $status]);
        }
    }

    // ---- Lifecycle Actions ----

    public function start(Request $request, $id)
    {
        $trip = Trip::findOrFail($id);
        abort_unless($trip->status === 'planned', 422, 'Only a planned trip can be started.');

        $data = $request->validate([
            'start_odometer'  => 'required|numeric|min:0',
            'actual_start_at' => 'nullable|date',
            'override'        => 'nullable|boolean',
            'override_reason' => 'nullable|string|max:500',
        ]);

        $blockers = app(ComplianceService::class)->dispatchBlockers($trip);
        if ($blockers && (!$request->boolean('override') || !auth()->user()->can('Trips override'))) {
            return back()->withInput()->with('blockers', $blockers)->with('error', 'Dispatch checks failed. Fix issues or override with permission.');
        }

        if ($request->boolean('override') && empty($data['override_reason'])) {
            throw ValidationException::withMessages(['override_reason' => 'Reason required for override.']);
        }

        $trip->update([
            'status' => 'in_transit',
            'start_odometer' => $data['start_odometer'],
            'actual_start_at' => $data['actual_start_at'] ?? now(),
            'override_reason' => $request->boolean('override') ? $data['override_reason'] . ' [Overridden by ' . auth()->user()->name . ']' : null,
        ]);

        // Operational status lives on the logistics profile (vehicles has no status column).
        $this->setAssets($trip, 'on_trip');

        return back()->with('success', 'Trip dispatched successfully.');
    }

    public function complete(Request $request, $id)
    {
        $trip = Trip::findOrFail($id);
        abort_unless($trip->status === 'in_transit', 422, 'Only a trip in transit can be marked delivered.');

        $data = $request->validate([
            'end_odometer'  => 'required|numeric|gte:' . (float) $trip->start_odometer,
            'actual_end_at' => 'nullable|date|after_or_equal:' . optional($trip->actual_start_at)->toDateTimeString(),
            'pod'           => 'nullable|file|max:5120|mimes:pdf,jpg,jpeg,png',
        ]);

        $updateData = [
            'status' => 'completed',
            'end_odometer' => $data['end_odometer'],
            'distance_km' => round((float) $data['end_odometer'] - (float) $trip->start_odometer, 1),
            'actual_end_at' => $data['actual_end_at'] ?? now(),
        ];

        if ($request->hasFile('pod')) {
            $updateData['pod_path'] = $request->file('pod')->store('logistics/trips', 'public');
        }

        $trip->update($updateData);

        $this->setAssets($trip, 'available');

        return back()->with('success', 'Trip marked as delivered. Approve expenses to close.');
    }

    public function close($id)
    {
        $trip = Trip::withCount(['expenses as pending_expenses_count' => fn ($q) => $q->where('status', 'pending')])->findOrFail($id);
        abort_unless($trip->status === 'completed', 422, 'Only a delivered trip can be closed.');

        if ($trip->pending_expenses_count > 0) {
            return back()->with('error', "{$trip->pending_expenses_count} expense(s) pending approval.");
        }

        $trip->update(['status' => 'closed', 'closed_at' => now()]);
        return back()->with('success', 'Trip closed. Figures are final.');
    }

    public function cancel(Request $request, $id)
    {
        $trip = Trip::findOrFail($id);
        abort_unless($trip->status === 'planned', 422, 'Only a planned trip can be cancelled.');
        $data = $request->validate(['reason' => 'required|string|max:500']);

        $trip->update([
            'status' => 'cancelled',
            'notes' => trim(($trip->notes ? $trip->notes . "\n" : '') . 'Cancelled: ' . $data['reason'])
        ]);

        return redirect()->route('logistics.trips.index')->with('success', 'Trip cancelled.');
    }

    // ---- Checkpoints & Incidents ----

    public function addCheckpoint(Request $request, $id)
    {
        $trip = Trip::findOrFail($id);
        abort_unless(!in_array($trip->status, ['closed', 'cancelled']), 422, 'Trip is closed.');
        
        $data = $request->validate([
            'border_post_id' => 'nullable|exists:border_posts,id',
            'location'       => 'nullable|string|max:150',
            'arrived_at'     => 'required|date',
            'departed_at'    => 'nullable|date|after_or_equal:arrived_at',
            'notes'          => 'nullable|string|max:500',
        ]);

        if (empty($data['border_post_id']) && empty($data['location'])) {
            throw ValidationException::withMessages(['location' => 'Choose a border post or type a location.']);
        }

        $trip->checkpoints()->create($data);
        return back()->with('success', 'Checkpoint recorded.');
    }

    public function deleteCheckpoint($id, $rowId)
    {
        $trip = Trip::findOrFail($id);
        abort_unless(!in_array($trip->status, ['closed', 'cancelled']), 422, 'Trip is closed.');
        TripCheckpoint::where('trip_id', $trip->id)->where('id', $rowId)->delete();
        return back()->with('success', 'Checkpoint removed.');
    }

    public function addIncident(Request $request, $id)
    {
        $trip = Trip::findOrFail($id);
        abort_unless(!in_array($trip->status, ['closed', 'cancelled']), 422, 'Trip is closed.');

        $data = $request->validate([
            'type'           => 'required|in:breakdown,accident,delay,theft,other',
            'occurred_at'    => 'required|date',
            'location'       => 'nullable|string|max:150',
            'country_id'     => 'nullable|exists:countries,id',
            'description'    => 'nullable|string|max:1000',
            'cost'           => 'nullable|numeric|min:0',
            'currency'       => 'required|string|size:3|exists:currencies,code',
            'exchange_rate'  => 'nullable|numeric|gt:0',
            'resolved_at'    => 'nullable|date|after_or_equal:occurred_at',
            'downtime_hours' => 'nullable|numeric|min:0',
        ]);

        $cost = (float) ($data['cost'] ?? 0);
        [$rate, $base] = app(CurrencyService::class)->toBase($cost, $data['currency'], $data['exchange_rate'] ?? null, $data['occurred_at']);
        
        $data['exchange_rate'] = $rate;
        $data['cost_base'] = $base;
        $data['created_by'] = auth()->id();
        
        $trip->incidents()->create($data);
        return back()->with('success', 'Incident recorded.');
    }

    public function deleteIncident($id, $rowId)
    {
        $trip = Trip::findOrFail($id);
        abort_unless(!in_array($trip->status, ['closed', 'cancelled']), 422, 'Trip is closed.');
        TripIncident::where('trip_id', $trip->id)->where('id', $rowId)->delete();
        return back()->with('success', 'Incident removed.');
    }
}