<?php

namespace App\Http\Controllers\Admin\Logistics;

use App\Model\Trailer;

class TrailersController extends CrudController
{
    protected string $permission = 'Trailers';
    protected string $routePrefix = 'logistics.trailers';
    protected string $title = 'Trailers';
    protected string $singular = 'Trailer';

    protected function model(): string { return Trailer::class; }
    protected function query() { return Trailer::query()->orderBy('registration_number'); }
    protected function defaults(): array { return ['status' => 'available']; }

    protected function fields(): array
    {
        return [
            ['name' => 'registration_number', 'label' => 'Registration number', 'rules' => 'required|string|max:50|unique:trailers,registration_number,{id}', 'col' => 4],
            ['name' => 'chassis_number', 'label' => 'Chassis / VIN', 'rules' => 'nullable|string|max:80', 'col' => 4],
            ['name' => 'trailer_type', 'label' => 'Type', 'type' => 'select', 'rules' => 'nullable|string|max:50', 'col' => 4,
                'options' => array_combine($t = ['Flatbed', 'Tipper', 'Tanker', 'Container / Skeleton', 'Curtain-side', 'Refrigerated', 'Lowbed', 'Other'], $t)],
            ['name' => 'make', 'label' => 'Make', 'rules' => 'nullable|string|max:60', 'col' => 3],
            ['name' => 'model', 'label' => 'Model', 'rules' => 'nullable|string|max:60', 'col' => 3],
            ['name' => 'year', 'label' => 'Year', 'type' => 'number', 'rules' => 'nullable|integer|min:1980|max:2100', 'col' => 2],
            ['name' => 'axle_configuration', 'label' => 'Axle configuration', 'rules' => 'nullable|string|max:40', 'col' => 2],
            ['name' => 'payload_capacity_kg', 'label' => 'Payload (kg)', 'type' => 'number', 'rules' => 'nullable|integer|min:0', 'col' => 2],
            ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'rules' => 'required|in:available,on_trip,workshop,retired', 'col' => 3,
                'options' => ['available' => 'Available', 'on_trip' => 'On trip', 'workshop' => 'In workshop', 'retired' => 'Retired']],
            ['name' => 'depot', 'label' => 'Depot / location', 'rules' => 'nullable|string|max:100', 'col' => 3],
            ['name' => 'notes', 'label' => 'Notes', 'type' => 'textarea', 'rules' => 'nullable|string|max:2000', 'col' => 12],
        ];
    }

    protected function columns(): array
    {
        return [
            ['label' => 'Registration', 'field' => 'registration_number'],
            ['label' => 'Type', 'field' => 'trailer_type'],
            ['label' => 'Make / model', 'value' => fn ($r) => trim($r->make . ' ' . $r->model)],
            ['label' => 'Axles', 'field' => 'axle_configuration'],
            ['label' => 'Payload kg', 'field' => 'payload_capacity_kg'],
            ['label' => 'Status', 'field' => 'status', 'type' => 'badge'],
        ];
    }

    protected function rowLinks($row): array
    {
        return [
            ['label' => 'Documents', 'url' => route('logistics.documents.index', ['owner' => 'trailer:' . $row->id])],
            ['label' => 'Tyres/parts', 'url' => route('logistics.components.index', ['owner' => 'trailer:' . $row->id])],
        ];
    }
}
