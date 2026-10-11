<?php

namespace App\Http\Controllers\Admin\Logistics;

use App\Model\ComplianceDocument;
use App\Model\DocumentType;
use App\Services\ComplianceService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ComplianceDocumentsController extends CrudController
{
    protected string $permission = 'Compliance';
    protected string $routePrefix = 'logistics.documents';
    protected string $title = 'Compliance Documents';
    protected string $singular = 'Document';
    protected array $with = ['type'];

    protected ?array $names = null;

    protected function model(): string { return ComplianceDocument::class; }

    protected function ownerNames(): array
    {
        if ($this->names === null) {
            $this->names = ['vehicle' => $this->vehicleOptions(), 'trailer' => $this->trailerOptions(), 'driver' => $this->driverOptions(),
                'company' => [0 => 'Atlas General Dealers (company)']];
        }
        return $this->names;
    }

    protected function query()
    {
        $q = ComplianceDocument::query()->with('type')->orderBy('expiry_date')->orderByDesc('id');
        if (preg_match('/^(vehicle|trailer|driver):(\d+)$/', (string) request('owner'), $m)) {
            $q->where('owner_type', $m[1])->where('owner_id', (int) $m[2]);
        }
        return $q;
    }

    protected function indexIntro(): ?string
    {
        return request('owner') ? 'Filtered to one truck / trailer / driver. Open "Documents" in the menu to see everything.' : 'Insurance, permits, licences and certificates with expiry dates. Expired critical documents block trip dispatch.';
    }

    public function create()
    {
        $view = parent::create();
        if (preg_match('/^(vehicle|trailer|driver):(\d+)$/', (string) request('owner'), $m)) {
            $view->getData()['model']->owner_type = $m[1];
            $view->getData()['model']->owner_id = (int) $m[2];
        }
        return $view;
    }

    protected function fields(): array
    {
        $types = DocumentType::where('is_active', true)->orderBy('applies_to')->orderBy('name')->get()
            ->mapWithKeys(fn ($t) => [$t->id => $t->name . ' (' . ['vehicle' => 'Truck', 'trailer' => 'Trailer', 'driver' => 'Driver', 'company' => 'Company'][$t->applies_to] ?? $t->applies_to . ')'])->all();

        return [
            ['name' => 'owner', 'label' => 'Belongs to', 'type' => 'select', 'options' => $this->ownerOptions(), 'rules' => ['required', 'regex:/^(vehicle|trailer|driver):\d+$/'], 'col' => 6],
            ['name' => 'document_type_id', 'label' => 'Document type', 'type' => 'select', 'options' => $types, 'rules' => 'required|exists:document_types,id', 'col' => 4],
            ['name' => 'coverage_country_ids', 'label' => 'Permit covers (blank = unrestricted)', 'type' => 'multiselect', 'options' => $this->countryOptions(), 'rules' => 'nullable|array', 'col' => 4, 'help' => 'Only for cross-border permits. Leave blank for unrestricted.'],
            ['name' => 'document_number', 'label' => 'Document / policy number', 'rules' => 'nullable|string|max:100', 'col' => 4],
            ['name' => 'issue_date', 'label' => 'Issue date', 'type' => 'date', 'rules' => 'nullable|date', 'col' => 4],
            ['name' => 'expiry_date', 'label' => 'Expiry date', 'type' => 'date', 'rules' => 'nullable|date|after_or_equal:issue_date', 'col' => 4],
            ['name' => 'alert_days', 'label' => 'Warn this many days before expiry (blank = use type default)', 'type' => 'number', 'rules' => 'nullable|integer|min:1|max:365', 'col' => 6],
            ['name' => 'file_path', 'label' => 'Scan / photo (PDF, JPG, PNG, max 5 MB)', 'type' => 'file', 'rules' => 'nullable|file|max:5120|mimes:pdf,jpg,jpeg,png', 'col' => 6],
            ['name' => 'first_registration_date', 'label' => 'First registration (registration certificates have no printed expiry)', 'type' => 'date', 'rules' => 'nullable|date', 'col' => 6],
            ['name' => 'notes', 'label' => 'Notes', 'type' => 'textarea', 'rules' => 'nullable|string|max:1000', 'col' => 6],
        ];
    }

    /** Rules may be an array here (regex rule), so override the string-only default. */
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
        $data = $this->splitOwner($data);
        $type = DocumentType::find($data['document_type_id'] ?? 0);
        if ($type && $type->applies_to !== ($data['owner_type'] ?? null)) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'document_type_id' => 'This document type is for ' . $type->applies_to . 's, but the selected owner is a ' . ($data['owner_type'] ?? '?') . '.',
            ]);
        }
        return $data;
    }

    protected function columns(): array
    {
        return [
            ['label' => 'Belongs to', 'value' => fn ($r) => (['vehicle' => 'Truck', 'trailer' => 'Trailer', 'driver' => 'Driver', 'company' => 'Company'][$r->owner_type] ?? '') . ': '
                . ($this->ownerNames()[$r->owner_type][$r->owner_id] ?? '(deleted)')],
            ['label' => 'Document', 'value' => fn ($r) => optional($r->type)->name],
            ['label' => 'Number', 'field' => 'document_number'],
            ['label' => 'Expires', 'field' => 'expiry_date', 'type' => 'date'],
            ['label' => 'Status', 'type' => 'badge', 'value' => function ($r) {
                if (!$r->expiry_date) { return 'no expiry'; }
                $left = (int) Carbon::today()->diffInDays($r->expiry_date->copy()->startOfDay(), false);
                $alert = $r->alert_days ?: (optional($r->type)->default_alert_days ?: 30);
                return $left < 0 ? 'expired' : ($left <= $alert ? 'due soon' : 'valid');
            }],
            ['label' => 'File', 'value' => fn ($r) => $r->file_path ? 'Yes' : '—'],
        ];
    }

    protected function rowLinks($row): array
    {
        return $row->file_path ? [['label' => 'View file', 'url' => \Illuminate\Support\Facades\Storage::url($row->file_path)]] : [];
    }
}
