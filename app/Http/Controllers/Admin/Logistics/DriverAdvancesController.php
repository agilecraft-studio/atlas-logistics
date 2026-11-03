<?php
declare(strict_types=1);

namespace App\Http\Controllers\Admin\Logistics;

use App\Http\Controllers\Controller;
use App\Model\DriverAdvance;
use App\Model\Trip;
use App\Services\CurrencyService;
use Illuminate\Http\Request;

class DriverAdvancesController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:Advances list', ['only' => ['index']]);
        $this->middleware('permission:Advances add', ['only' => ['store']]);
        $this->middleware('permission:Advances delete', ['only' => ['destroy']]);
    }

    public function index()
    {
        $rows = DriverAdvance::with(['trip', 'driver', 'issuedBy'])->orderByDesc('issued_at')->orderByDesc('id')->limit(1000)->get();

        $byDriver = $rows->groupBy('driver_id')->map(fn ($g) => [
            'name'  => optional($g->first()->driver)->name ?: 'Unknown',
            'count' => $g->count(),
            'total' => (float) $g->sum('amount_base'),
        ])->sortByDesc('total');

        return view('logistics.advances.index', [
            'rows' => $rows, 
            'byDriver' => $byDriver, 
            'base' => CurrencyService::base()
        ]);
    }

    public function store(Request $request, $tripId)
    {
        $trip = Trip::findOrFail($tripId);
        abort_unless(!in_array($trip->status, ['closed', 'cancelled']), 422, 'This trip is closed.');

        $data = $request->validate([
            'amount'        => 'required|numeric|gt:0',
            'currency'      => 'required|string|size:3|exists:currencies,code',
            'exchange_rate' => 'nullable|numeric|gt:0',
            'issued_at'     => 'required|date',
            'purpose'       => 'required|in:allowance,fuel,border,other',
            'method'        => 'nullable|in:' . implode(',', array_keys(config('logistics.payment_methods'))),
            'reference'     => 'nullable|string|max:100',
            'notes'         => 'nullable|string|max:500',
        ]);

        [$rate, $base] = app(CurrencyService::class)->toBase((float) $data['amount'], $data['currency'], $data['exchange_rate'] ?? null, $data['issued_at']);
        
        $data['exchange_rate'] = $rate;
        $data['amount_base'] = $base;
        $data['driver_id'] = $trip->driver_id;
        $data['issued_by'] = auth()->id();

        $trip->advances()->create($data);
        $trip->unsetRelation('advances');

        return back()->with('success', 'Advance recorded for ' . optional($trip->driver)->name . '.');
    }

    public function destroy($id)
    {
        $a = DriverAdvance::with('trip')->findOrFail($id);
        abort_unless(in_array(optional($a->trip)->status, ['planned', 'in_transit', 'completed'], true), 422, 'This trip is closed.');

        $a->deleted_by = auth()->id();
        $a->save();
        $a->delete();

        return back()->with('success', 'Advance removed; the audit trail is kept.');
    }
}