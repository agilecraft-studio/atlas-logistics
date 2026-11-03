<?php
/*
@copyright
Fleet Manager v7.0
Copyright (C) 2017-2025 Atlas Logistics <https://app.atlaslogistics.co.mw> All rights reserved.
Design and developed by Atlas Logistics <https://app.atlaslogistics.co.mw>
 */
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Http\Requests\DriverRequest;
use App\Http\Requests\ImportRequest;
use App\Imports\DriverImport;
use App\Model\DriverLogsModel;
use App\Model\DriverVehicleModel;
use App\Model\ExpCats;
use App\Model\Expense;
use App\Model\AtlasLogistics;
use App\Model\IncCats;
use App\Model\IncomeModel;
use App\Model\ServiceItemsModel;
use App\Model\User;
use App\Model\VehicleModel;
use App\Model\VehicleTypeModel;
use Carbon\Carbon;
use DataTables;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Kreait\Laravel\Firebase\Facades\Firebase;
use Maatwebsite\Excel\Facades\Excel;
use Redirect;

use Illuminate\Support\Facades\Validator;

use App\Traits\FirebasePassword;


use Maatwebsite\Excel\Validators\ValidationException;
use Illuminate\Support\Facades\Session;



class DriversController extends Controller {

	use FirebasePassword;

	public function verify_driver($id)

	{

		$driver=User::find($id);

		$vehicle=VehicleModel::find($driver->getMeta('vehicle_id'));



		return view("drivers.verifydriver",compact('driver','vehicle'));

	}



	public function update_verify_driver(Request $request)

	{

		$validator = Validator::make($request->all(), [

            'status' => 'required',

		]);

		if ($validator->fails()) {

            return back()->withErrors($validator)->withInput();

        }



		$driver=User::find($request->d_id);

		$driver->is_verified=$request->status;

		$driver->save();



		return redirect('/admin/drivers');



	}




	public function __construct() {
		$this->middleware('permission:Drivers add', ['only' => ['create']]);
		$this->middleware('permission:Drivers edit', ['only' => ['edit']]);
		$this->middleware('permission:Drivers delete', ['only' => ['bulk_delete', 'destroy']]);
		$this->middleware('permission:Drivers list');
		$this->middleware('permission:Drivers import', ['only' => ['importDrivers']]);
		$this->phone_code = config("phone_codes.codes");
	}
	public function importDrivers(ImportRequest $request) {
		try {
			// Check if the file is uploaded and valid
			if (!$request->hasFile('excel') || !$request->file('excel')->isValid()) {
				return back()->withErrors(['error' => 'The uploaded file is not valid.']);
			}
	
			$file = $request->file('excel');
			$destinationPath = './uploads/xml';
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
			Excel::import(new DriverImport, $destinationPath . '/' . $fileName);
	
			return back()->with('success', 'Drivers imported successfully.');
		} catch (ValidationException $e) {
			// Handle validation exceptions (if any validation rules fail in the import process)
			$failures = $e->failures();
			$errorMessages = [];
	
			foreach ($failures as $failure) {
				$errorMessages[] = "Row " . $failure->row() . ": " . implode(", ", $failure->errors());
			}
	
			return back()->withErrors($errorMessages);
		} catch (\Exception $e) {
			// Log the exact error to the Laravel log file for debugging
			\Log::error('Error importing drivers: ' . $e->getMessage());
			
			// Return a more informative error to the user
			return back()->withErrors(['error' => 'An error occurred while importing drivers. Please check the server logs for more details.']);
		}
	}
	
	public function index() {
		return view("drivers.index");
	}
	public function fetch_data(Request $request) {
		if ($request->ajax()) {
			$users = User::select('users.*')
				->leftJoin('users_meta', 'users_meta.user_id', '=', 'users.id')
				->leftJoin('driver_vehicle', 'driver_vehicle.driver_id', '=', 'users.id')
				->leftJoin('vehicles', 'driver_vehicle.vehicle_id', '=', 'vehicles.id')
				->with(['metas'])->whereUser_type("D")->groupBy('users.id');
			return DataTables::eloquent($users)
				->addColumn('check', function ($user) {
					return '<input type="checkbox" name="ids[]" value="' . $user->id . '" class="checkbox" id="chk' . $user->id . '" onclick=\'checkcheckbox();\'>';
				})
				->editColumn('name', function ($user) {
					return "<a href=" . route('drivers.show', $user->id) . ">$user->name</a>";
				})
				->addColumn('driver_image', function ($user) {
					$src = ($user->driver_image != null) ? asset('./uploads/' . $user->driver_image) : asset('assets/images/no-user.jpg');
					return '<img src="' . $src . '" height="70px" width="70px">';
				})
				->addColumn('is_active', function ($user) {
					return ($user->is_active == 1) ? "YES" : "NO";
				})
				->addColumn('phone', function ($user) {
					return $user->phone_code . ' ' . $user->phone;
				})
				->addColumn('start_date', function ($user) {
					return $user->start_date;
				})
				->addColumn('action', function ($user) {
					return view('drivers.list-actions', ['row' => $user]);
				})
				->filterColumn('is_active', function ($query, $keyword) {
					$query->whereHas("metas", function ($q) use ($keyword) {
						$q->where('key', 'is_active');
						$q->whereRaw("CASE WHEN value = '1' THEN 'YES' ELSE 'NO' END ilike ? ", ["%{$keyword}%"]);
					});
					return $query;
				})
				->filterColumn('phone', function ($query, $keyword) {
					$query->whereHas("metas", function ($q) use ($keyword) {
						$q->where(function ($q) use ($keyword) {
							$q->where('key', 'phone');
							$q->where("value", 'like', "%$keyword%");
						})->orWhere(function ($q) use ($keyword) {
							$q->where('key', 'phone_code');
							$q->where("value", 'like', "%$keyword%");
						});
					});
					return $query;
				})
				->filterColumn('start_date', function ($query, $keyword) {
					$query->whereHas("metas", function ($q) use ($keyword) {
						$q->where('key', 'start_date');
						$q->where("value", 'like', "%$keyword%");
					});
					return $query;
				})
				->rawColumns(['driver_image', 'action', 'check', 'name'])
				->make(true);
			//return datatables(User::all())->toJson();
		}
	}
	public function show($id) {
		$index['driver'] = User::find($id);
		return view('drivers.show', $index);
	}
	public function destroy(Request $request) {

		$u=User::find($request->get('id'));

		$this->deleteUser($u->email);

		$driver = User::find($request->id);
		if ($driver->vehicles->count()) {
			$driver->vehicles()->detach($driver->vehicles->pluck('id')->toArray());
		}
		DriverVehicleModel::where('driver_id', $request->id)->delete();
		if (file_exists('./uploads/' . $driver->driver_image) && !is_dir('./uploads/' . $driver->driver_image)) {
			unlink('./uploads/' . $driver->driver_image);
		}
		if (file_exists('./uploads/' . $driver->license_image) && !is_dir('./uploads/' . $driver->license_image)) {
			unlink('./uploads/' . $driver->license_image);
		}
		if (file_exists('./uploads/' . $driver->documents) && !is_dir('./uploads/' . $driver->documents)) {
			unlink('./uploads/' . $driver->documents);
		}
		User::find($request->get('id'))->user_data()->delete();
		//User::find($request->get('id'))->delete();
		$driver->update([
			'email' => time() . "_deleted" . $driver->email,
		]);
		$driver->delete();
		return redirect()->route('drivers.index');
	}
	public function bulk_delete(Request $request) {
		$drivers = User::whereIn('id', $request->ids)->get();
		foreach ($drivers as $driver) {

			$this->deleteUser($driver->email);


			if ($driver->vehicles->count()) {
				$driver->vehicles()->detach($driver->vehicles->pluck('id')->toArray());
			}
			$driver->update([
				'email' => time() . "_deleted" . $driver->email,
			]);
			if (file_exists('./uploads/' . $driver->driver_image) && !is_dir('./uploads/' . $driver->driver_image)) {
				unlink('./uploads/' . $driver->driver_image);
			}
			if (file_exists('./uploads/' . $driver->license_image) && !is_dir('./uploads/' . $driver->license_image)) {
				unlink('./uploads/' . $driver->license_image);
			}
			if (file_exists('./uploads/' . $driver->documents) && !is_dir('./uploads/' . $driver->documents)) {
				unlink('./uploads/' . $driver->documents);
			}
			$driver->delete();
		}
		DriverVehicleModel::whereIn('driver_id', $request->ids)->delete();
		//User::whereIn('id', $request->ids)->delete();
		// return redirect('admin/customers');
		return back();
	}
	public function create() {
		$data['vehicles'] = VehicleModel::get();
		$data['phone_code'] = $this->phone_code;
		return view("drivers.create", $data);
	}
	public function edit(User $driver) {
		if ($driver->user_type != "D") {
			return redirect("admin/drivers");
		}
		$driver->load('vehicles');
		if (Auth::user()->group_id == null || Auth::user()->user_type == "S") {
			$vehicles = VehicleModel::get();
		} else {
			$vehicles = VehicleModel::where('group_id', Auth::user()->group_id)
				->get();
		}
		$phone_code = $this->phone_code;
		return view("drivers.edit", compact("driver", "phone_code", 'vehicles'));
	}
	private function upload_file($file, $field, $id) {
		$destinationPath = './uploads'; // upload path
		$extension = $file->getClientOriginalExtension();
		$fileName1 = Str::uuid() . '.' . $extension;
		$file->move($destinationPath, $fileName1);
		$user = User::find($id);
		$user->setMeta([$field => $fileName1]);
		$user->save();
	}
	public function update(DriverRequest $request) {
		$id = $request->get('id');
		$user = User::find($id);
		if ($request->file('driver_image') && $request->file('driver_image')->isValid()) {
			if (file_exists('./uploads/' . $user->driver_image) && !is_dir('./uploads/' . $user->driver_image)) {
				unlink('./uploads/' . $user->driver_image);
			}
			$this->upload_file($request->file('driver_image'), "driver_image", $id);
		}
		if ($request->file('license_image') && $request->file('license_image')->isValid()) {
			if (file_exists('./uploads/' . $user->license_image) && !is_dir('./uploads/' . $user->license_image)) {
				unlink('./uploads/' . $user->license_image);
			}
			$this->upload_file($request->file('license_image'), "license_image", $id);
			$user->id_proof_type = "License";
			$user->save();
		}
		if ($request->file('documents')) {
			if (file_exists('./uploads/' . $user->documents) && !is_dir('./uploads/' . $user->documents)) {
				unlink('./uploads/' . $user->documents);
			}
			$this->upload_file($request->file('documents'), "documents", $id);
		}
		// dd($request->all());
		$user->name = $request->get("first_name") . " " . $request->get("last_name");
		$name = explode(' ', $request->name);
		$user->first_name = $name[0] ?? '';
		$user->middle_name = $name[1] ?? '';
		$user->last_name = $name[2] ?? '';
		$user->email = $request->get('email');
		$user->save();
		// $user->driver_image = $request->get('driver_image');
		$form_data = $request->all();
		unset($form_data['driver_image']);
		unset($form_data['documents']);
		unset($form_data['license_image']);
		$user->setMeta($form_data);
		$to = \Carbon\Carbon::now();
		$from = \Carbon\Carbon::createFromFormat('Y-m-d', $request->get('exp_date'));
		$diff_in_days = $to->diffInDays($from);
		if ($diff_in_days > 20) {
			$t = DB::table('notifications')
				->where('type', 'like', '%RenewDriverLicence%')
				->where('data', 'like', '%"vid":' . $user->id . '%')
				->delete();
		}
		$user->save();
		return redirect()->route("drivers.index");
	}
	public function store(DriverRequest $request) {
		$id = User::create([
			"name" => $request->get("first_name") . " " . $request->get("last_name"),
			"email" => $request->get("email"),
			"password" => bcrypt($request->get("password")),
			"user_type" => "D",
			'api_token' => Str::random(60),
		])->id;
		$user = User::find($id);
		$user->user_id = Auth::user()->id;
		if ($request->file('driver_image') && $request->file('driver_image')->isValid()) {
			$this->upload_file($request->file('driver_image'), "driver_image", $id);
		}
		if ($request->file('license_image') && $request->file('license_image')->isValid()) {
			$this->upload_file($request->file('license_image'), "license_image", $id);
			$user->id_proof_type = "License";
			$user->save();
		}
		if ($request->file('documents')) {
			$this->upload_file($request->file('documents'), "documents", $id);
		}
		$form_data = $request->all();
		unset($form_data['driver_image']);
		unset($form_data['documents']);
		unset($form_data['license_image']);
		$user->first_name = $request->get('first_name');
		$user->last_name = $request->get('last_name');
		$user->setMeta($form_data);
		$user->save();
		$user->givePermissionTo(['Notes add', 'Notes edit', 'Notes delete', 'Notes list', 'Drivers list', 'Fuel add', 'Fuel edit', 'Fuel delete', 'Fuel list', 'VehicleInspection add', 'Transactions list', 'Transactions add', 'Transactions edit', 'Transactions delete']);
		return redirect()->route("drivers.index");
	}
	public function enable($id) {
		$driver = User::find($id);
		$driver->is_active = 1;
		$driver->save();
		return redirect()->route("drivers.index");
	}
	public function disable($id) {
		$driver = User::find($id);
		$driver->is_active = 0;
		$driver->save();
		return redirect()->route('drivers.index');
	}
	public function yearly() {
		$bookings = DriverLogsModel::where('driver_id', Auth::user()->id)->get();
		$v_id = array('0');
		$c = array();
		foreach ($bookings as $key) {
			if ($key->vehicle_id != null) {
				$v_id[] = $key->vehicle_id;
			}
		}
		$years = DB::select("select distinct extract(year from date)::int as years from income union select distinct extract(year from date)::int as years from expense order by years desc");
		$y = array();
		foreach ($years as $year) {
			$y[$year->years] = $year->years;
		}
		if ($years == null) {
			$y[date('Y')] = date('Y');
		}
		$data['vehicles'] = VehicleModel::whereIn('id', $v_id)->get();
		$data['year_select'] = date("Y");
		$data['vehicle_select'] = null;
		$data['years'] = $y;
		$in = join(",", $v_id);
		$data['income'] = IncomeModel::select(DB::raw("sum(COALESCE(driver_amount,amount)) as income"))->whereYear('date', date('Y'))->whereIn('vehicle_id', $v_id)->get();
		$data['expenses'] = Expense::select(DB::raw('sum(COALESCE(driver_amount,amount)) as expense'))->whereYear('date', date('Y'))->whereIn('vehicle_id', $v_id)->get();
		$data['expense_by_cat'] = Expense::select('type', 'expense_type', DB::raw('sum(amount) as expense'))->whereYear('date', date('Y'))->whereIn('vehicle_id', $v_id)->groupBy(['expense_type', 'type'])->get();
		$ss = ServiceItemsModel::get();
		foreach ($ss as $s) {
			$c[$s->id] = $s->description;
		}
		$kk = ExpCats::get();
		foreach ($kk as $k) {
			$b[$k->id] = $k->name;
		}
		$hh = IncCats::get();
		foreach ($hh as $k) {
			$i[$k->id] = $k->name;
		}
		$data['service'] = $c;
		$data['expense_cats'] = $b;
		$data['income_cats'] = $i;
		$data['result'] = "";
		$data['yearly_income'] = $this->yearly_income();
		$data['yearly_expense'] = $this->yearly_expense();
		return view('drivers.yearly', $data);
	}
	public function yearly_post(Request $request) {
		$bookings = DriverLogsModel::where('driver_id', Auth::user()->id)->get();
		$v_id = array();
		foreach ($bookings as $key) {
			$v_id[] = $key->vehicle_id;
		}
		$years = DB::select("select distinct extract(year from date)::int as years from income union select distinct extract(year from date)::int as years from expense order by years desc");
		$y = array();
		$b = array();
		$i = array();
		foreach ($years as $year) {
			$y[$year->years] = $year->years;
		}
		if ($years == null) {
			$y[date('Y')] = date('Y');
		}
		$data['vehicles'] = VehicleModel::whereIn('id', $v_id)->get();
		$data['year_select'] = $request->get("year");
		$data['vehicle_select'] = $request->get("vehicle_id");
		$data['yearly_income'] = $this->yearly_income();
		$data['yearly_expense'] = $this->yearly_expense();
		$income1 = IncomeModel::select(DB::raw("sum(amount) as income"))->whereYear('date', $data['year_select']);
		$expense1 = Expense::select(DB::raw("sum(amount) as expense"))->whereYear('date', $data['year_select']);
		$expense2 = Expense::select('type', 'expense_type', DB::raw("sum(amount) as expense"))->whereYear('date', $data['year_select'])->groupBy(['expense_type', 'type']);
		if ($data['vehicle_select'] != "") {
			$data['income'] = $income1->where('vehicle_id', $data['vehicle_select'])->get();
			$data['expenses'] = $expense1->where('vehicle_id', $data['vehicle_select'])->get();
			$data['expense_by_cat'] = $expense2->where('vehicle_id', $data['vehicle_select'])->get();
		} else {
			$data['income'] = $income1->whereIn('vehicle_id', $v_id)->get();
			$data['expenses'] = $expense1->whereIn('vehicle_id', $v_id)->get();
			$data['expense_by_cat'] = $expense2->whereIn('vehicle_id', $v_id)->get();
		}
		$ss = ServiceItemsModel::get();
		foreach ($ss as $s) {
			$c[$s->id] = $s->description;
		}
		$kk = ExpCats::get();
		foreach ($kk as $k) {
			$b[$k->id] = $k->name;
		}
		$hh = IncCats::get();
		foreach ($hh as $k) {
			$i[$k->id] = $k->name;
		}
		$data['service'] = $c;
		$data['expense_cats'] = $b;
		$data['income_cats'] = $i;
		$data['years'] = $y;
		$data['result'] = "";
		return view('drivers.yearly', $data);
	}
	public function monthly() {
		$bookings = DriverLogsModel::where('driver_id', Auth::user()->id)->get();
		$v_id = array('0');
		foreach ($bookings as $key) {
			if ($key->vehicle_id != null) {
				$v_id[] = $key->vehicle_id;
			}
		}
		$years = DB::select("select distinct extract(year from date)::int as years from income union select distinct extract(year from date)::int as years from expense order by years desc");
		$y = array();
		foreach ($years as $year) {
			$y[$year->years] = $year->years;
		}
		if ($years == null) {
			$y[date('Y')] = date('Y');
		}
		$data['vehicles'] = VehicleModel::whereIn('id', $v_id)->get();
		$data['year_select'] = date("Y");
		$data['month_select'] = date("n");
		$data['vehicle_select'] = null;
		$data['years'] = $y;
		$data['yearly_income'] = $this->yearly_income();
		$data['yearly_expense'] = $this->yearly_expense();
		$in = join(",", $v_id);
		$data['income'] = IncomeModel::select(DB::raw('sum(COALESCE(driver_amount,amount)) as income'))->whereYear('date', date('Y'))->whereMonth('date', date('n'))->whereIn('vehicle_id', $v_id)->get();
		$data['expenses'] = Expense::select(DB::raw('sum(COALESCE(driver_amount,amount)) as expense'))->whereYear('date', date('Y'))->whereMonth('date', date('n'))->whereIn('vehicle_id', $v_id)->get();
		$data['expense_by_cat'] = DB::select("select type,expense_type,sum(amount) as expense from expense where deleted_at is null and extract(year from date)=" . (int) date("Y") . " and extract(month from date)=" . (int) date("n") . " and vehicle_id in(" . $in . ") group by expense_type,type");
		$ss = ServiceItemsModel::get();
		$c = array();
		foreach ($ss as $s) {
			$c[$s->id] = $s->description;
		}
		$kk = ExpCats::get();
		foreach ($kk as $k) {
			$b[$k->id] = $k->name;
		}
		$hh = IncCats::get();
		foreach ($hh as $k) {
			$i[$k->id] = $k->name;
		}
		$data['service'] = $c;
		$data['expense_cats'] = $b;
		$data['income_cats'] = $i;
		$data['result'] = "";
		return view("drivers.monthly", $data);
	}
	public function monthly_post(Request $request) {
		$bookings = DriverLogsModel::where('driver_id', Auth::user()->id)->get();
		$v_id = array('0');
		foreach ($bookings as $key) {
			if ($key->vehicle_id != null) {
				$v_id[] = $key->vehicle_id;
			}
		}
		$years = DB::select("select distinct extract(year from date)::int as years from income union select distinct extract(year from date)::int as years from expense order by years desc");
		$y = array();
		$b = array();
		$i = array();
		$c = array();
		foreach ($years as $year) {
			$y[$year->years] = $year->years;
		}
		if ($years == null) {
			$y[date('Y')] = date('Y');
		}
		$data['vehicles'] = VehicleModel::whereIn('id', $v_id)->get();
		$data['year_select'] = $request->get("year");
		$data['month_select'] = $request->get("month");
		$data['vehicle_select'] = $request->get("vehicle_id");
		$data['yearly_income'] = $this->yearly_income();
		$data['yearly_expense'] = $this->yearly_expense();
		$income1 = IncomeModel::select(DB::raw('sum(amount) as income'))->whereYear('date', $data['year_select'])->whereMonth('date', $data['month_select']);
		$expense1 = Expense::select(DB::raw('sum(amount) as expense'))->whereYear('date', $data['year_select'])->whereMonth('date', $data['month_select']);
		$expense2 = Expense::select('type', 'expense_type', DB::raw('sum(amount) as expense'))->whereYear('date', $data['year_select'])->whereMonth('date', $data['month_select'])->groupBy(['expense_type', 'type']);
		if ($data['vehicle_select'] != "") {
			$data['income'] = $income1->where('vehicle_id', $data['vehicle_select'])->get();
			$data['expenses'] = $expense1->where('vehicle_id', $data['vehicle_select'])->get();
			$data['expense_by_cat'] = $expense2->where('vehicle_id', $data['vehicle_select'])->get();
		} else {
			$data['income'] = $income1->whereIn('vehicle_id', $v_id)->get();
			$data['expenses'] = $expense1->whereIn('vehicle_id', $v_id)->get();
			$data['expense_by_cat'] = $expense2->whereIn('vehicle_id', $v_id)->get();
		}
		$ss = ServiceItemsModel::get();
		foreach ($ss as $s) {
			$c[$s->id] = $s->description;
		}
		$kk = ExpCats::get();
		foreach ($kk as $k) {
			$b[$k->id] = $k->name;
		}
		$hh = IncCats::get();
		foreach ($hh as $k) {
			$i[$k->id] = $k->name;
		}
		$data['service'] = $c;
		$data['expense_cats'] = $b;
		$data['income_cats'] = $i;
		$data['years'] = $y;
		$data['result'] = "";
		return view("drivers.monthly", $data);
	}
	private function yearly_income() {
		$bookings = DriverLogsModel::where('driver_id', Auth::user()->id)->get();
		$v_id = array('0');
		foreach ($bookings as $key) {
			if ($key->vehicle_id != null) {
				$v_id[] = $key->vehicle_id;
			}
		}
		$in = join(",", $v_id);
		$incomes = DB::select("select to_char(date, 'FMMonth') as mnth,sum(amount) as tot from income where to_char(date, 'YYYY')=? and  deleted_at is null and vehicle_id in(" . $in . ") group by to_char(date, 'FMMonth'), extract(month from date)", [date("Y")]);
		$months = ["January" => 0, "February" => 0, "March" => 0, "April" => 0, "May" => 0, "June" => 0, "July" => 0, "August" => 0, "September" => 0, "October" => 0, "November" => 0, "December" => 0];
		$income2 = array();
		foreach ($incomes as $income) {
			$income2[$income->mnth] = $income->tot;
		}
		$yr = array_merge($months, $income2);
		return implode(",", $yr);
	}
	private function yearly_expense() {
		$bookings = DriverLogsModel::where('driver_id', Auth::user()->id)->get();
		$v_id = array('0');
		foreach ($bookings as $key) {
			if ($key->vehicle_id != null) {
				$v_id[] = $key->vehicle_id;
			}
		}
		$in = join(",", $v_id);
		$incomes = DB::select("select to_char(date, 'FMMonth') as mnth,sum(amount) as tot from expense where to_char(date, 'YYYY')=? and  deleted_at is null and vehicle_id in(" . $in . ") group by to_char(date, 'FMMonth'), extract(month from date)", [date("Y")]);
		$months = ["January" => 0, "February" => 0, "March" => 0, "April" => 0, "May" => 0, "June" => 0, "July" => 0, "August" => 0, "September" => 0, "October" => 0, "November" => 0, "December" => 0];
		$income2 = array();
		foreach ($incomes as $income) {
			$income2[$income->mnth] = $income->tot;
		}
		$yr = array_merge($months, $income2);
		return implode(",", $yr);
	}
	public function firebase() {
		$database = app('firebase.database');
		$details = $database
			->getReference('/User_Locations')
			->orderByChild('user_type')
			->equalTo('D')
			->getValue();
		$markers = array();
		foreach ($details as $d) {
			// echo $d['user_name'] . "</br>";
			if ($d['user_type'] == "D") {
				$markers[] = array("id" => $d["user_id"], "name" => $d["user_name"], "position" => ["lat" => $d['latitude'], "long" => $d['longitude'], 'av' => $d['availability']],
				);
			}
		}
	}
}
