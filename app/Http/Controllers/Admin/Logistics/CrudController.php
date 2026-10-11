<?php

namespace App\Http\Controllers\Admin\Logistics;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * Small generic CRUD base for the logistics modules.
 * A subclass supplies: permission module name, route name prefix, model class, field specs and list columns.
 * Routes must use the parameter name {id} for the record (see routes/logistics.php).
 *
 * Field spec keys: name, label, type (text|number|date|datetime|textarea|select|multiselect|checkbox|file|email|tel),
 *   rules (validation string; "{id}" is replaced for unique-ignore), options (value => label), step, help, col (1-12), placeholder.
 * Column spec keys: label, field | value (callable), type (date|datetime|badge|bool|money|number).
 */
abstract class CrudController extends Controller
{
    protected string $permission = '';
    protected string $routePrefix = '';
    protected string $title = '';
    protected string $singular = '';
    protected array $with = [];

    abstract protected function model(): string;
    abstract protected function fields(): array;
    abstract protected function columns(): array;

    public function __construct()
    {
        $p = $this->permission;
        $this->middleware("permission:{$p} list");
        $this->middleware("permission:{$p} add", ['only' => ['create', 'store']]);
        $this->middleware("permission:{$p} edit", ['only' => ['edit', 'update']]);
        $this->middleware("permission:{$p} delete", ['only' => ['destroy']]);
    }

    // ---- hooks -----------------------------------------------------------------------------
    protected function routeParams(): array { return []; }
    protected function query() { return ($this->model())::query()->with($this->with)->orderByDesc('id'); }
    protected function prepare(array $data, Request $request, $id): array { return $data; }
    protected function afterSave($model, Request $request, bool $created): void {}
    protected function defaults(): array { return []; }
    protected function rowLinks($row): array { return []; }
    protected function headerLinks(): array { return []; }
    protected function canDelete($row): bool { return true; }
    protected function redirectAfterSave($model): string { return route($this->routePrefix . '.index', $this->routeParams()); }
    protected function indexIntro(): ?string { return null; }
    protected function formIntro(): ?string { return null; }

    protected function rules(Request $request, $id = null): array
    {
        $rules = [];
        foreach ($this->fields() as $f) {
            if (!empty($f['rules'])) {
                $rules[$f['name']] = str_replace('{id}', $id ?: 'NULL', $f['rules']);
            }
        }
        return $rules;
    }

    protected function id()
    {
        return request()->route('id');
    }

    // ---- actions ---------------------------------------------------------------------------
    public function index()
    {
        return view('logistics.crud.index', [
            'title'       => $this->title,
            'singular'    => $this->singular,
            'columns'     => $this->columns(),
            'rows'        => $this->query()->get(),
            'prefix'      => $this->routePrefix,
            'params'      => $this->routeParams(),
            'permission'  => $this->permission,
            'rowLinks'    => fn ($row) => $this->rowLinks($row),
            'headerLinks' => $this->headerLinks(),
            'canDelete'   => fn ($row) => $this->canDelete($row),
            'intro'       => $this->indexIntro(),
        ]);
    }

    public function create()
    {
        $model = new ($this->model())();
        foreach ($this->defaults() as $k => $v) {
            $model->$k = $v;
        }
        return $this->form($model, route($this->routePrefix . '.store', $this->routeParams()), 'POST');
    }

    public function edit()
    {
        $model = ($this->model())::findOrFail($this->id());
        return $this->form($model, route($this->routePrefix . '.update', array_merge($this->routeParams(), ['id' => $model->id])), 'PUT');
    }

    protected function form($model, string $action, string $method, ?string $heading = null)
    {
        return view('logistics.crud.form', [
            'title'  => $heading ?: (($model->exists ? 'Edit ' : 'Add ') . $this->singular),
            'fields' => $this->fields(),
            'model'  => $model,
            'action' => $action,
            'method' => $method,
            'back'   => route($this->routePrefix . '.index', $this->routeParams()),
            'intro'  => $this->formIntro(),
        ]);
    }

    public function store(Request $request)
    {
        return $this->save($request, null);
    }

    public function update(Request $request)
    {
        return $this->save($request, $this->id());
    }

    protected function save(Request $request, $id)
    {
        $data = $request->validate($this->rules($request, $id));

        foreach ($this->fields() as $f) {
            if (($f['type'] ?? '') === 'checkbox') {
                $data[$f['name']] = $request->boolean($f['name']);
            }
            if (($f['type'] ?? '') === 'multiselect') {
                $data[$f['name']] = array_values(array_filter((array) $request->input($f['name'], [])));
            }
        }

        $data = $this->prepare($data, $request, $id);

        $class = $this->model();
        $model = $id ? $class::findOrFail($id) : new $class();

        foreach ($this->fields() as $f) {
            if (($f['type'] ?? '') === 'file') {
                if ($request->hasFile($f['name'])) {
                    $data[$f['name']] = $request->file($f['name'])->store('logistics/' . $model->getTable(), 'public');
                } else {
                    unset($data[$f['name']]);
                }
            }
        }

        $model->fill($data)->save();
        $this->afterSave($model, $request, !$id);

        return redirect($this->redirectAfterSave($model))->with('success', $this->singular . ($id ? ' updated.' : ' created.'));
    }

    public function destroy()
    {
        $row = ($this->model())::findOrFail($this->id());
        if (!$this->canDelete($row)) {
            return back()->with('error', 'This record cannot be deleted.');
        }
        try {
            $row->delete();
        } catch (\Illuminate\Database\QueryException $e) {
            return back()->with('error', 'This record is in use and cannot be deleted. Mark it inactive instead.');
        }
        return back()->with('success', $this->singular . ' deleted.');
    }

    // ---- helpers shared by subclasses ------------------------------------------------------
    protected function vehicleOptions(): array
    {
        $out = [];
        foreach (\App\Model\VehicleModel::orderBy('license_plate')->get() as $v) {
            $out[$v->id] = \App\Services\ComplianceService::vehicleLabel($v);
        }
        return $out;
    }

    protected function trailerOptions(): array
    {
        return \App\Model\Trailer::orderBy('registration_number')->pluck('registration_number', 'id')->all();
    }

    protected function driverOptions(): array
    {
        return \App\Model\User::where('user_type', 'D')->orderBy('name')->pluck('name', 'id')->all();
    }

    protected function customerOptions(): array
    {
        return \App\Model\User::where('user_type', 'C')->orderBy('name')->pluck('name', 'id')->all();
    }

    protected function countryOptions(): array
    {
        return \App\Model\Country::where('is_active', true)->orderBy('name')->pluck('name', 'id')->all();
    }

    protected function currencyOptions(): array
    {
        return \App\Model\Currency::where('is_active', true)->orderBy('code')->get()
            ->mapWithKeys(fn ($c) => [$c->code => $c->code . ' - ' . $c->name])->all();
    }

    /** "vehicle:12" / "trailer:3" / "driver:7" keyed options for the owner select. */
    protected function ownerOptions(array $types = ['vehicle', 'trailer', 'driver', 'company']): array
    {
        $out = [];
        if (in_array('vehicle', $types)) {
            foreach ($this->vehicleOptions() as $id => $l) { $out["vehicle:$id"] = "[Truck] $l"; }
        }
        if (in_array('trailer', $types)) {
            foreach ($this->trailerOptions() as $id => $l) { $out["trailer:$id"] = "[Trailer] $l"; }
        }
        if (in_array('driver', $types)) {
            foreach ($this->driverOptions() as $id => $l) { $out["driver:$id"] = "[Driver] $l"; }
        }
        if (in_array('company', $types)) {
            $out['company:0'] = '[Company] Atlas General Dealers (company)';
        }
        return $out;
    }

    /** Split an "type:id" owner value into owner_type / owner_id inside $data. */
    protected function splitOwner(array $data): array
    {
        if (isset($data['owner'])) {
            [$type, $oid] = array_pad(explode(':', $data['owner'], 2), 2, null);
            $data['owner_type'] = $type;
            $data['owner_id'] = (int) $oid;
            unset($data['owner']);
        }
        return $data;
    }
}
