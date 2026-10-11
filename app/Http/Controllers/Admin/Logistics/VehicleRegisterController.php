<?php

namespace App\Http\Controllers\Admin\Logistics;

use App\Http\Controllers\Controller;
use App\Model\ComplianceDocument;
use App\Model\VehicleComponent;
use App\Model\VehicleLogisticsProfile;
use App\Model\VehicleModel;
use App\Services\ComplianceService;
use Illuminate\Http\Request;

/** Fleet register: one row per truck with VIN, plate, logistics status, service due, documents and tyres. */
class VehicleRegisterController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:FleetRegister list');
        $this->middleware('permission:FleetRegister edit', ['only' => ['edit', 'update']]);
    }

    public function index()
    {
        $today = now()->toDateString();
        $profiles = VehicleLogisticsProfile::all()->keyBy('vehicle_id');
        $docs = ComplianceDocument::where('owner_type', 'vehicle')->get()->groupBy('owner_id');
        $parts = VehicleComponent::where('owner_type', 'vehicle')->where('status', 'fitted')->get()->groupBy('owner_id');

        $rows = [];
        foreach (VehicleModel::orderBy('license_plate')->get() as $v) {
            $p = $profiles->get($v->id);
            $d = $docs->get($v->id, collect());
            $expired = $d->filter(fn ($x) => $x->expiry_date && $x->expiry_date->lt(now()->startOfDay()))->count();
            $tyres = $parts->get($v->id, collect())->where('component_type', 'tyre')->count();

            $rows[] = [
                'cells' => [
                    $v->license_plate ?: '—',
                    trim(($v->make_name ?? '') . ' ' . ($v->model_name ?? '') . ' ' . ($v->year ?? '')),
                    $v->vin ?: '—',
                    $p ? ['badge' => 'secondary', 'text' => ucfirst(str_replace('_', ' ', $p->logistics_status))] : ['badge' => 'light', 'text' => 'Not set'],
                    $p && $p->depot ? $p->depot : '—',
                    $p && $p->next_service_date ? $p->next_service_date->format('d M Y') : '—',
                    $expired > 0 ? ['badge' => 'danger', 'text' => $d->count() . ' docs / ' . $expired . ' expired'] : ($d->count() . ' docs'),
                    (string) $tyres,
                ],
                'links' => [
                    ['label' => 'Logistics details', 'url' => route('logistics.register.vehicles.edit', ['id' => $v->id]), 'class' => 'btn-info'],
                    ['label' => 'Documents', 'url' => route('logistics.documents.index', ['owner' => 'vehicle:' . $v->id]), 'class' => 'btn-light'],
                    ['label' => 'Tyres/parts', 'url' => route('logistics.components.index', ['owner' => 'vehicle:' . $v->id]), 'class' => 'btn-light'],
                ],
            ];
        }

        return view('logistics.registers.list', [
            'title'   => 'Fleet Register (Trucks)',
            'headers' => ['Plate', 'Make / model', 'VIN', 'Status', 'Depot', 'Next service', 'Documents', 'Tyres fitted'],
            'rows'    => $rows,
            'intro'   => 'Plate, VIN and basic details come from Vehicles. Use "Logistics details" to add engine/chassis numbers, depot, status and service due dates.',
            'extra'   => [['label' => 'Manage vehicles', 'url' => url('admin/vehicles')]],
        ]);
    }

    protected function fields(): array
    {
        return [
            ['name' => 'engine_number', 'label' => 'Engine number', 'rules' => 'nullable|string|max:80', 'col' => 4],
            ['name' => 'chassis_number', 'label' => 'Chassis number', 'rules' => 'nullable|string|max:80', 'col' => 4],
            ['name' => 'axle_configuration', 'label' => 'Axle configuration (e.g. 6x4)', 'rules' => 'nullable|string|max:40', 'col' => 4],
            ['name' => 'fuel_tank_capacity_l', 'label' => 'Fuel tank capacity (litres)', 'type' => 'number', 'step' => '0.1', 'rules' => 'nullable|numeric|min:0', 'col' => 4],
            ['name' => 'title_holder', 'label' => 'Title holder', 'rules' => 'nullable|string|max:100', 'col' => 4],
            ['name' => 'register_number', 'label' => 'Vehicle register number', 'rules' => 'nullable|string|max:60', 'col' => 4],
            ['name' => 'tare_kg', 'label' => 'Tare (kg)', 'type' => 'number', 'rules' => 'nullable|integer|min:0', 'col' => 4],
            ['name' => 'gvm_kg', 'label' => 'GVM (kg)', 'type' => 'number', 'rules' => 'nullable|integer|min:0', 'col' => 4],
            ['name' => 'first_registration', 'label' => 'First registration', 'type' => 'date', 'rules' => 'nullable|date', 'col' => 4],
            ['name' => 'ownership_type', 'label' => 'Ownership', 'type' => 'select', 'rules' => 'nullable|in:owned,leased,hired', 'col' => 4,
                'options' => ['owned' => 'Owned', 'leased' => 'Leased', 'hired' => 'Hired']],
            ['name' => 'depot', 'label' => 'Depot / base', 'rules' => 'nullable|string|max:100', 'col' => 4],
            ['name' => 'logistics_status', 'label' => 'Operational status', 'type' => 'select', 'rules' => 'required|in:available,on_trip,workshop,awaiting_parts,suspended,sold,scrapped', 'col' => 4,
                'options' => ['available' => 'Available', 'on_trip' => 'On trip', 'workshop' => 'In workshop', 'awaiting_parts' => 'Awaiting parts',
                    'suspended' => 'Suspended', 'sold' => 'Sold', 'scrapped' => 'Scrapped']],
            ['name' => 'last_service_odometer', 'label' => 'Last service at (km)', 'type' => 'number', 'step' => '0.1', 'rules' => 'nullable|numeric|min:0', 'col' => 4],
            ['name' => 'next_service_odometer', 'label' => 'Next service at (km)', 'type' => 'number', 'step' => '0.1', 'rules' => 'nullable|numeric|min:0', 'col' => 4],
            ['name' => 'next_service_date', 'label' => 'Next service due date', 'type' => 'date', 'rules' => 'nullable|date', 'col' => 4],
            ['name' => 'notes', 'label' => 'Notes', 'type' => 'textarea', 'rules' => 'nullable|string|max:2000', 'col' => 12],
        ];
    }

    public function edit($id)
    {
        $vehicle = VehicleModel::findOrFail($id);
        $profile = VehicleLogisticsProfile::firstOrNew(['vehicle_id' => $vehicle->id]);
        if (!$profile->exists) {
            $profile->logistics_status = 'available';
        }

        return view('logistics.crud.form', [
            'title'  => 'Logistics details - ' . ComplianceService::vehicleLabel($vehicle),
            'fields' => $this->fields(),
            'model'  => $profile,
            'action' => route('logistics.register.vehicles.update', ['id' => $vehicle->id]),
            'method' => 'PUT',
            'back'   => route('logistics.register.vehicles'),
            'intro'  => 'VIN: ' . ($vehicle->vin ?: 'not recorded') . '  |  Plate: ' . ($vehicle->license_plate ?: 'not recorded') . '. Change those in Vehicles.',
        ]);
    }

    public function update(Request $request, $id)
    {
        $vehicle = VehicleModel::findOrFail($id);
        $rules = [];
        foreach ($this->fields() as $f) {
            $rules[$f['name']] = $f['rules'];
        }
        $data = $request->validate($rules);
        VehicleLogisticsProfile::updateOrCreate(['vehicle_id' => $vehicle->id], $data);

        return redirect()->route('logistics.register.vehicles')->with('success', 'Vehicle details saved.');
    }
}
