<?php

namespace App\Http\Controllers\Admin\Logistics;

use App\Model\VehicleComponent;
use Illuminate\Http\Request;

/** Tyres (with serial numbers), batteries and other parts fitted to a truck or trailer. */
class VehicleComponentsController extends CrudController
{
    protected string $permission = 'FleetRegister';
    protected string $routePrefix = 'logistics.components';
    protected string $title = 'Tyres & Fitted Components';
    protected string $singular = 'Component';

    protected ?array $names = null;

    protected function model(): string { return VehicleComponent::class; }
    protected function defaults(): array { return ['status' => 'fitted']; }

    protected function ownerNames(): array
    {
        if ($this->names === null) {
            $this->names = ['vehicle' => $this->vehicleOptions(), 'trailer' => $this->trailerOptions()];
        }
        return $this->names;
    }

    protected function query()
    {
        $q = VehicleComponent::query()->orderBy('owner_type')->orderBy('owner_id')->orderBy('position');
        if (preg_match('/^(vehicle|trailer):(\d+)$/', (string) request('owner'), $m)) {
            $q->where('owner_type', $m[1])->where('owner_id', (int) $m[2]);
        }
        return $q;
    }

    protected function indexIntro(): ?string
    {
        return 'Every tyre, battery and serial-numbered part, where it is fitted, and when it was fitted/removed.';
    }

    public function create()
    {
        $view = parent::create();
        if (preg_match('/^(vehicle|trailer):(\d+)$/', (string) request('owner'), $m)) {
            $view->getData()['model']->owner_type = $m[1];
            $view->getData()['model']->owner_id = (int) $m[2];
        }
        return $view;
    }

    protected function fields(): array
    {
        $types = ['tyre' => 'Tyre', 'spare_wheel' => 'Spare wheel', 'battery' => 'Battery', 'turbo' => 'Turbocharger', 'ecu' => 'ECU / computer',
            'gps_tracker' => 'GPS tracker', 'fire_extinguisher' => 'Fire extinguisher', 'tool_kit' => 'Tool kit', 'other' => 'Other'];

        return [
            ['name' => 'owner', 'label' => 'Fitted to', 'type' => 'select', 'options' => $this->ownerOptions(['vehicle', 'trailer']), 'rules' => ['required', 'regex:/^(vehicle|trailer):\d+$/'], 'col' => 6],
            ['name' => 'component_type', 'label' => 'Component', 'type' => 'select', 'options' => $types, 'rules' => 'required|in:' . implode(',', array_keys($types)), 'col' => 3],
            ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'rules' => 'required|in:fitted,spare,removed,scrapped', 'col' => 3,
                'options' => ['fitted' => 'Fitted', 'spare' => 'Spare (in stock)', 'removed' => 'Removed', 'scrapped' => 'Scrapped']],
            ['name' => 'serial_number', 'label' => 'Serial number', 'rules' => 'nullable|string|max:100', 'col' => 4],
            ['name' => 'brand', 'label' => 'Brand / model', 'rules' => 'nullable|string|max:100', 'col' => 4],
            ['name' => 'position', 'label' => 'Position (e.g. Steer L, Drive 2 R)', 'rules' => 'nullable|string|max:60', 'col' => 4],
            ['name' => 'fitted_date', 'label' => 'Fitted on', 'type' => 'date', 'rules' => 'nullable|date', 'col' => 3],
            ['name' => 'fitted_odometer', 'label' => 'Odometer when fitted (km)', 'type' => 'number', 'step' => '0.1', 'rules' => 'nullable|numeric|min:0', 'col' => 3],
            ['name' => 'removed_date', 'label' => 'Removed on', 'type' => 'date', 'rules' => 'nullable|date|after_or_equal:fitted_date', 'col' => 3],
            ['name' => 'removed_odometer', 'label' => 'Odometer when removed (km)', 'type' => 'number', 'step' => '0.1', 'rules' => 'nullable|numeric|min:0', 'col' => 3],
            ['name' => 'cost', 'label' => 'Cost', 'type' => 'number', 'step' => '0.01', 'rules' => 'nullable|numeric|min:0', 'col' => 3],
            ['name' => 'supplier', 'label' => 'Supplier', 'rules' => 'nullable|string|max:100', 'col' => 3],
            ['name' => 'notes', 'label' => 'Notes', 'type' => 'textarea', 'rules' => 'nullable|string|max:1000', 'col' => 12],
        ];
    }

    protected function rules(Request $request, $id = null): array
    {
        $rules = [];
        foreach ($this->fields() as $f) {
            if (!empty($f['rules'])) {
                $rules[$f['name']] = is_array($f['rules']) ? $f['rules'] : str_replace('{id}', $id ?: 'NULL', $f['rules']);
            }
        }
        return $rules;
    }

    protected function prepare(array $data, Request $request, $id): array
    {
        return $this->splitOwner($data);
    }

    protected function columns(): array
    {
        return [
            ['label' => 'Fitted to', 'value' => fn ($r) => (['vehicle' => 'Truck', 'trailer' => 'Trailer'][$r->owner_type] ?? '') . ': '
                . ($this->ownerNames()[$r->owner_type][$r->owner_id] ?? '(deleted)')],
            ['label' => 'Component', 'value' => fn ($r) => ucfirst(str_replace('_', ' ', $r->component_type))],
            ['label' => 'Serial', 'field' => 'serial_number'],
            ['label' => 'Brand', 'field' => 'brand'],
            ['label' => 'Position', 'field' => 'position'],
            ['label' => 'Fitted', 'field' => 'fitted_date', 'type' => 'date'],
            ['label' => 'Fitted km', 'field' => 'fitted_odometer', 'type' => 'number'],
            ['label' => 'Status', 'field' => 'status', 'type' => 'badge'],
        ];
    }
}
