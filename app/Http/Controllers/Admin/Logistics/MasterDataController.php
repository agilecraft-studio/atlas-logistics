<?php

namespace App\Http\Controllers\Admin\Logistics;

use App\Model\BorderPost;
use App\Model\Country;
use App\Model\Currency;
use App\Model\DocumentType;
use App\Model\ExchangeRate;
use App\Model\TripExpenseCategory;
use App\Services\CurrencyService;
use Illuminate\Http\Request;

/** One controller for all simple lookup tables: /admin/logistics/master/{entity} */
class MasterDataController extends CrudController
{
    protected string $permission = 'MasterData';
    protected string $routePrefix = 'logistics.master';

    protected function entity(): string
    {
        $e = (string) request()->route('entity');
        abort_unless(array_key_exists($e, $this->config()), 404);
        return $e;
    }

    public static function entities(): array
    {
        return ['countries' => 'Countries', 'currencies' => 'Currencies', 'exchange-rates' => 'Exchange Rates',
            'border-posts' => 'Border Posts', 'expense-categories' => 'Expense Categories', 'document-types' => 'Document Types'];
    }

    protected function config(): array
    {
        $active = ['name' => 'is_active', 'label' => 'Active', 'type' => 'checkbox'];
        return [
            'countries' => [
                'title' => 'Countries', 'singular' => 'Country', 'model' => Country::class,
                'fields' => [
                    ['name' => 'name', 'label' => 'Country name', 'rules' => 'required|string|max:100', 'col' => 6],
                    ['name' => 'iso2', 'label' => 'ISO code (2 letters)', 'rules' => 'required|string|size:2|unique:countries,iso2,{id}', 'col' => 3],
                    ['name' => 'default_currency', 'label' => 'Default currency', 'type' => 'select', 'options' => $this->currencyOptions(), 'rules' => 'nullable|string|size:3', 'col' => 3],
                    $active,
                ],
                'columns' => [['label' => 'Country', 'field' => 'name'], ['label' => 'ISO', 'field' => 'iso2'],
                    ['label' => 'Currency', 'field' => 'default_currency'], ['label' => 'Active', 'field' => 'is_active', 'type' => 'bool']],
            ],
            'currencies' => [
                'title' => 'Currencies', 'singular' => 'Currency', 'model' => Currency::class,
                'fields' => [
                    ['name' => 'code', 'label' => 'Code (3 letters, e.g. USD)', 'rules' => 'required|string|size:3|unique:currencies,code,{id}', 'col' => 3],
                    ['name' => 'name', 'label' => 'Name', 'rules' => 'required|string|max:100', 'col' => 5],
                    ['name' => 'symbol', 'label' => 'Symbol', 'rules' => 'nullable|string|max:8', 'col' => 2],
                    $active,
                ],
                'columns' => [['label' => 'Code', 'field' => 'code'], ['label' => 'Name', 'field' => 'name'],
                    ['label' => 'Symbol', 'field' => 'symbol'], ['label' => 'Active', 'field' => 'is_active', 'type' => 'bool']],
            ],
            'exchange-rates' => [
                'title' => 'Exchange Rates (to ' . CurrencyService::base() . ')', 'singular' => 'Exchange rate', 'model' => ExchangeRate::class,
                'fields' => [
                    ['name' => 'currency_code', 'label' => 'Currency', 'type' => 'select', 'options' => $this->currencyOptions(), 'rules' => 'required|string|size:3', 'col' => 4],
                    ['name' => 'rate', 'label' => '1 unit of this currency = ? ' . CurrencyService::base(), 'type' => 'number', 'step' => '0.000001', 'rules' => 'required|numeric|gt:0', 'col' => 4],
                    ['name' => 'effective_date', 'label' => 'Effective from', 'type' => 'date', 'rules' => 'required|date', 'col' => 4],
                    ['name' => 'source', 'label' => 'Source (e.g. Reserve Bank, bank quote)', 'rules' => 'nullable|string|max:100', 'col' => 6],
                    ['name' => 'notes', 'label' => 'Notes', 'rules' => 'nullable|string|max:500', 'col' => 6],
                ],
                'columns' => [['label' => 'Currency', 'field' => 'currency_code'], ['label' => 'Rate', 'field' => 'rate', 'type' => 'number'],
                    ['label' => 'Effective', 'field' => 'effective_date', 'type' => 'date'], ['label' => 'Source', 'field' => 'source']],
            ],
            'border-posts' => [
                'title' => 'Border Posts', 'singular' => 'Border post', 'model' => BorderPost::class, 'with' => ['country', 'neighbour'],
                'fields' => [
                    ['name' => 'name', 'label' => 'Border post', 'rules' => 'required|string|max:100', 'col' => 4],
                    ['name' => 'country_id', 'label' => 'On the side of', 'type' => 'select', 'options' => $this->countryOptions(), 'rules' => 'nullable|exists:countries,id', 'col' => 4],
                    ['name' => 'neighbour_country_id', 'label' => 'Leads to', 'type' => 'select', 'options' => $this->countryOptions(), 'rules' => 'nullable|exists:countries,id', 'col' => 4],
                    ['name' => 'corridor', 'label' => 'Corridor / notes', 'rules' => 'nullable|string|max:150', 'col' => 8],
                    $active,
                ],
                'columns' => [['label' => 'Border post', 'field' => 'name'],
                    ['label' => 'From', 'value' => fn ($r) => optional($r->country)->name],
                    ['label' => 'To', 'value' => fn ($r) => optional($r->neighbour)->name],
                    ['label' => 'Active', 'field' => 'is_active', 'type' => 'bool']],
            ],
            'expense-categories' => [
                'title' => 'Trip Expense Categories', 'singular' => 'Expense category', 'model' => TripExpenseCategory::class,
                'fields' => [
                    ['name' => 'name', 'label' => 'Name', 'rules' => 'required|string|max:100', 'col' => 5],
                    ['name' => 'code', 'label' => 'Short code', 'rules' => 'required|string|max:30|unique:trip_expense_categories,code,{id}', 'col' => 3],
                    ['name' => 'requires_receipt', 'label' => 'Receipt required', 'type' => 'checkbox'],
                    $active,
                ],
                'columns' => [['label' => 'Name', 'field' => 'name'], ['label' => 'Code', 'field' => 'code'],
                    ['label' => 'Receipt required', 'field' => 'requires_receipt', 'type' => 'bool'], ['label' => 'Active', 'field' => 'is_active', 'type' => 'bool']],
            ],
            'document-types' => [
                'title' => 'Document Types', 'singular' => 'Document type', 'model' => DocumentType::class,
                'fields' => [
                    ['name' => 'name', 'label' => 'Document name', 'rules' => 'required|string|max:100', 'col' => 5],
                    ['name' => 'applies_to', 'label' => 'Applies to', 'type' => 'select', 'options' => ['vehicle' => 'Truck', 'trailer' => 'Trailer', 'driver' => 'Driver'], 'rules' => 'required|in:vehicle,trailer,driver', 'col' => 3],
                    ['name' => 'default_alert_days', 'label' => 'Warn this many days before expiry', 'type' => 'number', 'rules' => 'required|integer|min:1|max:365', 'col' => 4],
                    ['name' => 'is_critical', 'label' => 'Critical (expired = trip cannot be dispatched)', 'type' => 'checkbox'],
                    $active,
                ],
                'columns' => [['label' => 'Document', 'field' => 'name'], ['label' => 'Applies to', 'field' => 'applies_to', 'type' => 'badge'],
                    ['label' => 'Warn (days)', 'field' => 'default_alert_days'], ['label' => 'Critical', 'field' => 'is_critical', 'type' => 'bool'],
                    ['label' => 'Active', 'field' => 'is_active', 'type' => 'bool']],
            ],
        ];
    }

    protected function cfg(): array { return $this->config()[$this->entity()]; }
    protected function model(): string { return $this->cfg()['model']; }
    protected function fields(): array { return $this->cfg()['fields']; }
    protected function columns(): array { return $this->cfg()['columns']; }
    protected function routeParams(): array { return ['entity' => $this->entity()]; }

    protected function query()
    {
        $q = ($this->model())::query()->with($this->cfg()['with'] ?? []);
        return $this->entity() === 'exchange-rates' ? $q->orderByDesc('effective_date')->orderByDesc('id') : $q->orderBy('id');
    }

    public function index()
    {
        $this->title = $this->cfg()['title'];
        $this->singular = $this->cfg()['singular'];
        $view = parent::index();
        return $view->with('entities', self::entities())->with('entity', $this->entity());
    }

    public function create()
    {
        $this->singular = $this->cfg()['singular'];
        return parent::create();
    }

    public function edit()
    {
        $this->singular = $this->cfg()['singular'];
        return parent::edit();
    }

    protected function prepare(array $data, Request $request, $id): array
    {
        $this->singular = $this->cfg()['singular'];
        if ($this->entity() === 'currencies' || $this->entity() === 'countries') {
            foreach (['code', 'iso2', 'default_currency'] as $k) {
                if (!empty($data[$k])) { $data[$k] = strtoupper($data[$k]); }
            }
        }
        if ($this->entity() === 'exchange-rates') {
            $data['base_currency_code'] = CurrencyService::base();
            if (!$id) { $data['created_by'] = auth()->id(); }
        }
        return $data;
    }

    protected function save(Request $request, $id)
    {
        $this->singular = $this->cfg()['singular'];
        return parent::save($request, $id);
    }

    public function destroy()
    {
        $this->singular = $this->cfg()['singular'];
        return parent::destroy();
    }
}
