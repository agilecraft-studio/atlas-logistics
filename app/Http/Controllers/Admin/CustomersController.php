<?php
/*
@copyright
Fleet Manager v7.0
Copyright (C) 2017-2025 Atlas Logistics <https://app.atlaslogistics.co.mw> All rights reserved.
Design and developed by Atlas Logistics <https://app.atlaslogistics.co.mw>
 */
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Http\Requests\Customers as CustomerRequest;
use App\Http\Requests\ImportRequest;
use App\Imports\CustomerImport;
use App\Model\CustomerDocument;
use App\Model\Invoice;
use App\Model\Trip;
use App\Model\User;
use Auth;
use DataTables;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Validator;

use App\Traits\FirebasePassword;

use Illuminate\Support\Facades\Session;

use Maatwebsite\Excel\Validators\ValidationException;


class CustomersController extends Controller {

	use FirebasePassword;

	public function __construct() {
		// $this->middleware(['role:Admin']);
		$this->middleware('permission:Customer add', ['only' => ['create']]);
		$this->middleware('permission:Customer edit', ['only' => ['edit']]);
		$this->middleware('permission:Customer delete', ['only' => ['bulk_delete', 'destroy']]);
		$this->middleware('permission:Customer list');
		$this->middleware('permission:Customer import', ['only' => ['importCutomers']]);
	}
	public function importCutomers(ImportRequest $request) {
		try {
			$file = $request->file('excel');
			$destinationPath = './uploads/xml'; // Upload path
			$extension = $file->getClientOriginalExtension();
			$fileName = Str::uuid() . '.' . $extension;
		

			// Ensure the uploads directory exists and is writable
			if (!is_dir($destinationPath)) {
				mkdir($destinationPath, 0755, true); // Create directory if not exists
			}
			if (!is_writable($destinationPath)) {
				return back()->withErrors(['error' => 'The upload directory is not writable.']);
			}

			$file->move($destinationPath, $fileName);
	
			// Import Excel file
			Excel::import(new CustomerImport, $destinationPath . $fileName);
	
			return back()->with('success', 'Customers imported successfully.');
		} catch (ValidationException $e) {
			$failures = $e->failures();
			$errorMessages = [];
	
			foreach ($failures as $failure) {
				$errorMessages[] = "Row " . $failure->row() . ": " . implode(", ", $failure->errors());
			}
	
			return back()->withErrors($errorMessages);
		} catch (\Exception $e) {
			return back()->withErrors(['error' => 'An error occurred while importing Customers.']);
		}
	}
	public function index() {
		return view("customers.index");
	}
	public function fetch_data(Request $request) {
		if ($request->ajax()) {
			$users = User::select('users.*')->with(['user_data'])->whereUser_type("C")->orderBy('users.id', 'desc')->groupBy('users.id');
			return DataTables::eloquent($users)
				->addColumn('check', function ($user) {
					$tag = '<input type="checkbox" name="ids[]" value="' . $user->id . '" class="checkbox" id="chk' . $user->id . '" onclick=\'checkcheckbox();\'>';
					return $tag;
				})
				->addColumn('mobno', function ($user) {
					return $user->getMeta('mobno');
				})
				->editColumn('name', function ($user) {
					return "<a href=" . route('customers.show', $user->id) . ">$user->name</a>";
				})
				->addColumn('gender', function ($user) {
					return ($user->getMeta('gender')) ? "Male" : "Female";
				})
				->addColumn('address', function ($user) {
					return $user->getMeta('address');
				})
				->addColumn('action', function ($user) {
					return view('customers.list-actions', ['row' => $user]);
				})
				->rawColumns(['action', 'check', 'name'])
				->make(true);
		}
	}
	public function create() {
		return view("customers.create");
	}
	public function store(CustomerRequest $request) {
		$id = User::create([
			"name" => $request->get("first_name") . " " . $request->get("last_name"),
			"email" => $request->get("email"),
			"password" => bcrypt("password"),
			"user_type" => "C",
			"api_token" => Str::random(60),
		])->id;
		$user = User::find($id);
		$user->user_id = Auth::user()->id;
		$user->first_name = $request->get("first_name");
		$user->last_name = $request->get("last_name");
		$user->address = $request->get("address");
		$user->mobno = $request->get("phone");
		$user->gender = $request->get('gender');
		$user->save();
		return redirect()->route("customers.index");
	}
	public function ajax_store(Request $request) {
		$v = Validator::make($request->all(), [
			'first_name' => 'required',
			'last_name' => 'required',
			'email' => 'unique:users,email',
			'phone' => 'required|numeric|digits_between:7,15',
			'gender' => 'required',
			'address' => 'required',
		]);
		if ($v->fails()) {
			$d = ['error' => 'true', 'messages' => $v->errors()];
		} else {
			$id = User::create([
				"name" => $request->get("first_name") . " " . $request->get("last_name"),
				"email" => $request->get("email"),
				"password" => bcrypt("password"),
				"user_type" => "C",
				"api_token" => Str::random(60),
			])->id;
			$user = User::find($id);
			$user->first_name = $request->get("first_name");
			$user->last_name = $request->get("last_name");
			$user->address = $request->get("address");
			$user->mobno = $request->get("phone");
			$user->gender = $request->get('gender');
			$user->save();
			$d = User::whereUser_type("C")->get(["id", "name as text"]);
		}
		return $d;
	}
	public function show(Request $request, $id) {
		$customer = User::with('user_data')->whereUser_type('C')->findOrFail($id);

		$tab = $request->query('tab', 'overview');
		if (!in_array($tab, ['overview', 'shipments', 'invoices', 'documents', 'pods'], true)) {
			$tab = 'overview';
		}

		$date = function ($key) use ($request) {
			$value = $request->query($key);
			return ($value && strtotime((string) $value)) ? $value : null;
		};

		$tripFilters = [
			'status' => $request->query('trip_status'),
			'from'   => $date('trip_from'),
			'to'     => $date('trip_to'),
		];
		$invoiceFilters = [
			'document_type' => $request->query('invoice_type'),
			'status'        => $request->query('invoice_status'),
			'from'          => $date('invoice_from'),
			'to'            => $date('invoice_to'),
		];
		$documentTypes = config('customer_documents.types', []);
		$operationalTypes = array_values(array_diff(array_keys($documentTypes), [CustomerDocument::POD]));
		$documentFilters = [
			'type'   => $request->query('doc_type'),
			'status' => $request->query('doc_status'),
			'from'   => $date('doc_from'),
			'to'     => $date('doc_to'),
		];
		$podFilters = [
			'status' => $request->query('pod_status'),
			'from'   => $date('pod_from'),
			'to'     => $date('pod_to'),
		];

		$shipments = Trip::with(['vehicle', 'trailer', 'driver', 'originCountry', 'destinationCountry'])
			->where('customer_id', $customer->id)
			->when(!empty($tripFilters['status']), fn ($q) => $q->where('status', $tripFilters['status']))
			->when($tripFilters['from'], fn ($q) => $q->whereDate('planned_start_at', '>=', $tripFilters['from']))
			->when($tripFilters['to'], fn ($q) => $q->whereDate('planned_start_at', '<=', $tripFilters['to']))
			->orderByDesc('id')
			->get();

		$invoices = Invoice::with(['sourceInvoice'])
			->where('client_id', $customer->id)
			->when(!empty($invoiceFilters['document_type']), fn ($q) => $q->where('document_type', $invoiceFilters['document_type']))
			->when(!empty($invoiceFilters['status']), fn ($q) => $q->where('status', $invoiceFilters['status']))
			->when($invoiceFilters['from'], fn ($q) => $q->whereDate('issue_date', '>=', $invoiceFilters['from']))
			->when($invoiceFilters['to'], fn ($q) => $q->whereDate('issue_date', '<=', $invoiceFilters['to']))
			->orderByDesc('issue_date')
			->orderByDesc('id')
			->get();

		$documents = CustomerDocument::with(['trip', 'driver', 'vehicle'])
			->where('customer_id', $customer->id)
			->whereIn('type', $operationalTypes)
			->when(!empty($documentFilters['type']) && in_array($documentFilters['type'], $operationalTypes, true), fn ($q) => $q->where('type', $documentFilters['type']))
			->when(!empty($documentFilters['status']), fn ($q) => $q->where('status', $documentFilters['status']))
			->when($documentFilters['from'], fn ($q) => $q->whereDate('issue_date', '>=', $documentFilters['from']))
			->when($documentFilters['to'], fn ($q) => $q->whereDate('issue_date', '<=', $documentFilters['to']))
			->orderByDesc('issue_date')
			->orderByDesc('id')
			->get();

		$pods = CustomerDocument::with(['trip', 'driver', 'vehicle'])
			->where('customer_id', $customer->id)
			->where('type', CustomerDocument::POD)
			->when(!empty($podFilters['status']), fn ($q) => $q->where('status', $podFilters['status']))
			->when($podFilters['from'], fn ($q) => $q->whereDate('issue_date', '>=', $podFilters['from']))
			->when($podFilters['to'], fn ($q) => $q->whereDate('issue_date', '<=', $podFilters['to']))
			->orderByDesc('issue_date')
			->orderByDesc('id')
			->get();

		$invoiceBase = Invoice::where('client_id', $customer->id)->revenueDocuments();
		$totalInvoiced = (clone $invoiceBase)->whereIn('status', Invoice::REVENUE_STATUSES)->sum('total');
		$outstanding = (clone $invoiceBase)->whereIn('status', Invoice::REVENUE_STATUSES)->sum('balance_due');

		$index = [
			'customer'        => $customer,
			'tab'             => $tab,
			'shipments'       => $shipments,
			'invoices'        => $invoices,
			'documents'       => $documents,
			'pods'            => $pods,
			'tripFilters'     => $tripFilters,
			'invoiceFilters'  => $invoiceFilters,
			'documentFilters' => $documentFilters,
			'podFilters'      => $podFilters,
			'tripStatuses'    => config('logistics.trip_statuses'),
			'invoiceStatuses' => config('invoicing.statuses'),
			'invoiceTypes'    => config('invoicing.document_types'),
			'documentTypes'   => $documentTypes,
			'documentStatuses'=> config('customer_documents.statuses'),
			'kpis'            => [
				'total_invoiced' => $totalInvoiced,
				'outstanding'    => $outstanding,
				'shipments'      => Trip::where('customer_id', $customer->id)->count(),
				'invoices'       => Invoice::where('client_id', $customer->id)->count(),
				'documents'      => CustomerDocument::where('customer_id', $customer->id)->count(),
				'pods'           => CustomerDocument::where('customer_id', $customer->id)->where('type', CustomerDocument::POD)->count(),
			],
		];

		return view('customers.show', $index);
	}
	public function destroy(Request $request) {


		$u=User::find($request->get('id'));

		$this->deleteUser($u->email);

		User::find($request->get('id'))->user_data()->delete();
		$user = User::find($request->get('id'));
		$user->update([
			'email' => time() . "_deleted" . $user->email,
		]);
		$user->delete();
		return redirect()->route('customers.index');
	}
	public function edit($id) {
		$index['data'] = User::whereId($id)->first();
		return view("customers.edit", $index);
	}
	public function update(CustomerRequest $request) {
		$user = User::find($request->id);
		$user->name = $request->get("first_name") . " " . $request->get("last_name");
		$user->email = $request->get('email');
		$user->first_name = $request->get("first_name");
		$user->last_name = $request->get("last_name");
		$user->address = $request->get("address");
		$user->mobno = $request->get("phone");
		$user->gender = $request->get('gender');
		$user->save();
		return redirect()->route("customers.index");
	}
	public function bulk_delete(Request $request) {
		$users = User::whereIn('id', $request->ids)->get();
		foreach ($users as $user) {
			$this->deleteUser($user->email);

			$user->update([
				'email' => time() . "_deleted" . $user->email,
			]);
			$user->delete();
		}
		return back();
	}
}
