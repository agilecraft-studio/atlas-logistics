<?php
declare(strict_types=1);

namespace App\Http\Controllers\Admin\Logistics;

use App\Http\Controllers\Controller;
use App\Model\Trip;
use App\Model\TripExpense;
use App\Model\TripExpenseCategory;
use App\Services\CurrencyService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class TripExpensesController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:TripExpenses list', ['only' => ['index']]);
        $this->middleware('permission:TripExpenses add', ['only' => ['store']]);
        $this->middleware('permission:TripExpenses approve', ['only' => ['approve', 'reject']]);
        $this->middleware('permission:TripExpenses delete', ['only' => ['destroy']]);
    }

    public function index(Request $request)
    {
        $status = in_array($request->query('status'), ['pending', 'approved', 'rejected', 'all'], true) ? $request->query('status') : 'pending';

        $q = TripExpense::with(['trip.driver', 'trip.vehicle', 'category', 'country'])->orderByDesc('expense_date')->orderByDesc('id');
        if ($status !== 'all') {
            $q->where('status', $status);
        }
        $rows = $q->limit(1000)->get();

        return view('logistics.expenses.index', [
            'rows'   => $rows,
            'status' => $status,
            'total'  => (float) $rows->sum('amount_base'),
            'base'   => CurrencyService::base(),
        ]);
    }

    public function store(Request $request, $tripId)
    {
        $trip = Trip::findOrFail($tripId);
        abort_unless(!in_array($trip->status, ['closed', 'cancelled']), 422, 'This trip is closed.');

        $data = $request->validate([
            'category_id'   => 'required|exists:trip_expense_categories,id',
            'expense_date'  => 'required|date',
            'country_id'    => 'nullable|exists:countries,id',
            'amount'        => 'required|numeric|gt:0',
            'currency'      => 'required|string|size:3|exists:currencies,code',
            'exchange_rate' => 'nullable|numeric|gt:0',
            'paid_by'       => 'required|in:driver_cash,company',
            'litres'        => 'nullable|numeric|min:0',
            'odometer'      => 'nullable|numeric|min:0',
            'reference'     => 'nullable|string|max:100',
            'receipt'       => 'nullable|file|max:5120|mimes:pdf,jpg,jpeg,png',
            'notes'         => 'nullable|string|max:500',
        ]);

        $category = TripExpenseCategory::findOrFail($data['category_id']);
        if ($category->requires_receipt && !$request->hasFile('receipt')) {
            throw ValidationException::withMessages(['receipt' => "A receipt is required for {$category->name}."]);
        }

        [$rate, $base] = app(CurrencyService::class)->toBase((float) $data['amount'], $data['currency'], $data['exchange_rate'] ?? null, $data['expense_date']);

        $data['exchange_rate'] = $rate;
        $data['amount_base'] = $base;
        $data['status'] = 'pending';
        $data['created_by'] = auth()->id();
        
        if ($request->hasFile('receipt')) {
            $data['receipt_path'] = $request->file('receipt')->store('logistics/receipts', 'public');
        }

        $trip->expenses()->create($data);
        return back()->with('success', 'Expense added and waiting for approval.');
    }

    public function approve($id)
    {
        $e = TripExpense::with('trip')->findOrFail($id);
        abort_unless(optional($e->trip)->status !== 'closed', 422, 'The trip is closed.');
        abort_unless($e->status === 'pending', 422, 'Only pending expenses can be approved.');
        
        $e->update([
            'status' => 'approved', 
            'approved_by' => auth()->id(), 
            'approved_at' => now(), 
            'rejection_reason' => null
        ]);

        return back()->with('success', 'Expense approved.');
    }

    public function reject(Request $request, $id)
    {
        $e = TripExpense::with('trip')->findOrFail($id);
        abort_unless(optional($e->trip)->status !== 'closed', 422, 'The trip is closed.');
        abort_unless($e->status === 'pending', 422, 'Only pending expenses can be rejected.');
        
        $data = $request->validate(['rejection_reason' => 'required|string|max:255']);
        
        $e->update([
            'status' => 'rejected', 
            'approved_by' => auth()->id(), 
            'approved_at' => now(), 
            'rejection_reason' => $data['rejection_reason']
        ]);

        return back()->with('success', 'Expense rejected.');
    }

    public function destroy($id)
    {
        $e = TripExpense::with('trip')->findOrFail($id);
        abort_unless(optional($e->trip)->status !== 'closed', 422, 'The trip is closed.');
        abort_unless($e->status !== 'approved', 422, 'Approved expenses cannot be deleted. Reject first.');
        
        $e->delete();
        return back()->with('success', 'Expense deleted.');
    }
}