<?php
/*
@copyright
Fleet Manager v7.0
Copyright (C) 2017-2025 Atlas Logistics <https://app.atlaslogistics.co.mw> All rights reserved.
Design and developed by Atlas Logistics <https://app.atlaslogistics.co.mw>
 */
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Model\ExpCats;
use App\Model\Expense;
use App\Model\FuelModel;
use App\Model\IncCats;
use App\Model\IncomeModel;
use App\Model\ServiceItemsModel;
use App\Model\User;
use App\Model\VehicleModel;
use App\Model\WorkOrders;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportsController extends Controller {
	public function __construct() {
		// $this->middleware(['role:Admin']);
		$this->middleware('permission:Reports add', ['only' => ['create']]);
		$this->middleware('permission:Reports edit', ['only' => ['edit']]);
		$this->middleware('permission:Reports delete', ['only' => ['bulk_delete', 'destroy']]);
		$this->middleware('permission:Reports list');
	}
	public function expense() {
		$years = collect(DB::select("select distinct extract(year from date)::int as years from expense where deleted_at is null order by years desc"))->toArray();
		$y = array();
		foreach ($years as $year) {
			$y[$year->years] = $year->years;
		}
		if ($years == null) {
			$y[date('Y')] = date('Y');
		}
		$data['vehicles'] = VehicleModel::when(Auth::user()->group_id == null || Auth::user()->user_type == "S", function ($query) {
			// Apply this query when the condition is true (role is false or null)
			return $query->get();
		}, function ($query) {
			// Apply this query when the condition is false (role is provided)
			return $query->where('group_id', Auth::user()->group_id)->get();
		});
		$vehicle_ids = array(0);
		foreach ($data['vehicles'] as $vehicle) {
			$vehicle_ids[] = $vehicle['id'];
		}
		$data['vehicle_id'] = "";
		$data['year_select'] = date("Y");
		$data['month_select'] = date("n");
		$data['years'] = $y;
		$data['expense'] = Expense::whereIn('vehicle_id', $vehicle_ids)->whereYear("date", date("Y"))->whereMonth("date", date('m'))->get();
		return view('reports.expense', $data);
	}
	public function expense_post(Request $request) {
		$years = collect(DB::select("select distinct extract(year from date)::int as years from expense where deleted_at is null order by years desc"))->toArray();
		$y = array();
		foreach ($years as $year) {
			$y[$year->years] = $year->years;
		}
		if ($years == null) {
			$y[date('Y')] = date('Y');
		}
		$data['vehicles'] = VehicleModel::when(Auth::user()->group_id == null || Auth::user()->user_type == "S", function ($query) {
			// Apply this query when the condition is true (role is false or null)
			return $query->get();
		}, function ($query) {
			// Apply this query when the condition is false (role is provided)
			return $query->where('group_id', Auth::user()->group_id)->get();
		});
		$vehicle_ids = array(0);
		foreach ($data['vehicles'] as $vehicle) {
			$vehicle_ids[] = $vehicle['id'];
		}
		$data['vehicle_id'] = $request->vehicle_id;
		$data['year_select'] = $request->year;
		$data['month_select'] = $request->month;
		$data['years'] = $y;
		$records = Expense::whereIn('vehicle_id', $vehicle_ids)->whereYear("date", $request->year)->whereMonth("date", $request->month);
		if ($request->vehicle_id != null) {
			$data['expense'] = $records->where('vehicle_id', $request->vehicle_id)->get();
		} else {
			$data['expense'] = $records->get();
		}
		return view('reports.expense', $data);
	}
	public function expense_print(Request $request) {
		$data['vehicle_id'] = $request->vehicle_id;
		$data['year_select'] = $request->year;
		$data['month_select'] = $request->month;
		if (Auth::user()->group_id == null || Auth::user()->user_type == "S") {
			$data['vehicles'] = VehicleModel::get()->toArray();
		} else {
			$data['vehicles'] = VehicleModel::where('group_id', Auth::user()->group_id)->get()->toArray();
		}
		$vehicle_ids = array(0);
		foreach ($data['vehicles'] as $vehicle) {
			$vehicle_ids[] = $vehicle['id'];
		}
		$records = Expense::whereIn('vehicle_id', $vehicle_ids)->whereYear("date", $request->year)->whereMonth("date", $request->month);
		if ($request->vehicle_id != null) {
			$data['expense'] = $records->where('vehicle_id', $request->vehicle_id)->get();
		} else {
			$data['expense'] = $records->get();
		}
		return view('reports.print_expense', $data);
	}
	public function income() {
		$years = collect(DB::select("select distinct extract(year from date)::int as years from income where deleted_at is null order by years desc"))->toArray();
		$y = array();
		foreach ($years as $year) {
			$y[$year->years] = $year->years;
		}
		if ($years == null) {
			$y[date('Y')] = date('Y');
		}
		$data['vehicles'] = VehicleModel::when(Auth::user()->group_id == null || Auth::user()->user_type == "S", function ($query) {
			// Apply this query when the condition is true (role is false or null)
			return $query->get();
		}, function ($query) {
			// Apply this query when the condition is false (role is provided)
			return $query->where('group_id', Auth::user()->group_id)->get();
		});
		$vehicle_ids = array(0);
		foreach ($data['vehicles'] as $vehicle) {
			$vehicle_ids[] = $vehicle['id'];
		}
		$data['vehicle_id'] = "";
		$data['year_select'] = date("Y");
		$data['month_select'] = date("n");
		$data['years'] = $y;
		$data['income'] = IncomeModel::whereIn('vehicle_id', $vehicle_ids)->whereYear("date", date("Y"))->whereMonth("date", date('m'))->get();
		return view('reports.income', $data);
	}
	public function income_post(Request $request) {
		$years = collect(DB::select("select distinct extract(year from date)::int as years from income where deleted_at is null order by years desc"))->toArray();
		$y = array();
		foreach ($years as $year) {
			$y[$year->years] = $year->years;
		}
		if ($years == null) {
			$y[date('Y')] = date('Y');
		}
		$data['vehicles'] = VehicleModel::when(Auth::user()->group_id == null || Auth::user()->user_type == "S", function ($query) {
			// Apply this query when the condition is true (role is false or null)
			return $query->get();
		}, function ($query) {
			// Apply this query when the condition is false (role is provided)
			return $query->where('group_id', Auth::user()->group_id)->get();
		});
		$vehicle_ids = array(0);
		foreach ($data['vehicles'] as $vehicle) {
			$vehicle_ids[] = $vehicle['id'];
		}
		$data['vehicle_id'] = $request->vehicle_id;
		$data['year_select'] = $request->year;
		$data['month_select'] = $request->month;
		$data['years'] = $y;
		$records = IncomeModel::whereYear("date", $request->year)->whereMonth("date", $request->month);
		if ($request->vehicle_id != null) {
			$data['income'] = $records->where('vehicle_id', $request->vehicle_id)->get();
		} else {
			$data['income'] = $records->whereIn('vehicle_id', $vehicle_ids)->get();
		}
		return view('reports.income', $data);
	}
	public function income_print(Request $request) {
		$data['vehicle_id'] = $request->vehicle_id;
		$data['year_select'] = $request->year;
		$data['month_select'] = $request->month;
		if (Auth::user()->group_id == null || Auth::user()->user_type == "S") {
			$data['vehicles'] = VehicleModel::get()->toArray();
		} else {
			$data['vehicles'] = VehicleModel::where('group_id', Auth::user()->group_id)->get()->toArray();
		}
		$vehicle_ids = array(0);
		foreach ($data['vehicles'] as $vehicle) {
			$vehicle_ids[] = $vehicle['id'];
		}
		$records = IncomeModel::whereYear("date", $request->year)->whereMonth("date", $request->month);
		if ($request->vehicle_id != null) {
			$data['income'] = $records->where('vehicle_id', $request->vehicle_id)->get();
		} else {
			$data['income'] = $records->whereIn('vehicle_id', $vehicle_ids)->get();
		}
		return view('reports.print_income', $data);
	}
	public function monthly() {
		$years = DB::select("select distinct extract(year from date)::int as years from income union select distinct extract(year from date)::int as years from expense order by years desc");
		$y = array();
		$c = array();
		foreach ($years as $year) {
			$y[$year->years] = $year->years;
		}
		if ($years == null) {
			$y[date('Y')] = date('Y');
		}
		$data['vehicles'] = VehicleModel::when(Auth::user()->group_id == null || Auth::user()->user_type == "S", function ($query) {
			// Apply this query when the condition is true (role is false or null)
			return $query->get();
		}, function ($query) {
			// Apply this query when the condition is false (role is provided)
			return $query->where('group_id', Auth::user()->group_id)->get();
		});
		$vehicle_ids = array(0);
		foreach ($data['vehicles'] as $vehicle) {
			$vehicle_ids[] = $vehicle->id;
		}
		$data['year_select'] = date("Y");
		$data['month_select'] = date("n");
		$data['vehicle_select'] = null;
		$data['years'] = $y;
		$data['income'] = IncomeModel::select(DB::raw("SUM(amount) as income"))->whereYear('date', $data['year_select'])->whereMonth('date', $data['month_select'])->whereIn('vehicle_id', $vehicle_ids)->get();
		$data['expenses'] = Expense::select(DB::raw("SUM(amount) as expense"))->whereYear('date', $data['year_select'])->whereMonth('date', $data['month_select'])->whereIn('vehicle_id', $vehicle_ids)->get();
		$data['expense_by_cat'] = Expense::select("type", "expense_type", DB::raw("sum(amount) as expense"))->whereYear('date', date('Y'))->whereMonth('date', date('n'))->groupBy(['expense_type', 'type'])->whereIn('vehicle_id', $vehicle_ids)->get();
		$data['income_by_cat'] = IncomeModel::select("income_cat", DB::raw("sum(amount) as amount"))->whereYear('date', date('Y'))->whereMonth('date', date('n'))->groupBy(['income_cat'])->whereIn('vehicle_id', $vehicle_ids)->get();
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
		return view("reports.monthly", $data);
	}
	public function delinquent() {
		$years = collect(DB::select("select distinct extract(year from date)::int as years from income where deleted_at is null order by years desc"))->toArray();
		$y = array();
		foreach ($years as $year) {
			$y[$year->years] = $year->years;
		}
		if ($years == null) {
			$y[date('Y')] = date('Y');
		}
		$data['vehicles'] = VehicleModel::when(Auth::user()->group_id == null || Auth::user()->user_type == "S", function ($query) {
			// Apply this query when the condition is true (role is false or null)
			return $query->get();
		}, function ($query) {
			// Apply this query when the condition is false (role is provided)
			return $query->where('group_id', Auth::user()->group_id)->get();
		});
		$data['vehicle_id'] = "";
		$data['year_select'] = date("Y");
		$data['month_select'] = date("n");
		$data['years'] = $y;
		return view("reports.delinquent", $data);
	}
	public function delinquent_post(Request $request) {
		$years = DB::select("select distinct extract(year from date)::int as years from income where deleted_at is null order by years desc");
		$y = array();
		foreach ($years as $year) {
			$y[$year->years] = $year->years;
		}
		if ($years == null) {
			$y[date('Y')] = date('Y');
		}
		$data['vehicles'] = VehicleModel::when(Auth::user()->group_id == null || Auth::user()->user_type == "S", function ($query) {
			// Apply this query when the condition is true (role is false or null)
			return $query->get();
		}, function ($query) {
			// Apply this query when the condition is false (role is provided)
			return $query->where('group_id', Auth::user()->group_id)->get();
		});
		$vehicle_ids = array(0);
		foreach ($data['vehicles'] as $vehicle) {
			$vehicle_ids[] = $vehicle->id;
		}
		$data['year_select'] = $request->get("year");
		$data['month_select'] = $request->get("month");
		foreach ($data['vehicles'] as $row) {
			$data['v'][$row['id']] = $row;
		}
		$data['vehicle_id'] = $request->get("vehicle_id");
		$income = IncomeModel::select(['date', DB::raw('sum(amount) as "Income2",to_char(date, \'FMDay\') as day,MIN(vehicle_id) as vehicle_id')])->whereYear('date', $data['year_select'])->whereMonth('date', $data['month_select'])->groupBy('date')->orderBy('date');
		if ($data['vehicle_id'] != "") {
			$data['data'] = $income->where('vehicle_id', $data['vehicle_id'])->get();
		} else {
			$data['data'] = $income->whereIn('vehicle_id', $vehicle_ids)->get();
		}
		$data['years'] = $y;
		$data['result'] = "";
		return view("reports.delinquent", $data);
	}
	public function monthly_post(Request $request) {
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
		$data['vehicles'] = VehicleModel::when(Auth::user()->group_id == null || Auth::user()->user_type == "S", function ($query) {
			// Apply this query when the condition is true (role is false or null)
			return $query->get();
		}, function ($query) {
			// Apply this query when the condition is false (role is provided)
			return $query->where('group_id', Auth::user()->group_id)->get();
		});
		$vehicle_ids = array(0);
		foreach ($data['vehicles'] as $vehicle) {
			$vehicle_ids[] = $vehicle->id;
		}
		$data['year_select'] = $request->get("year");
		$data['month_select'] = $request->get("month");
		$data['vehicle_select'] = $request->get("vehicle_id");
		$income1 = IncomeModel::select(DB::raw("SUM(amount) as income"))->whereYear('date', $data['year_select'])->whereMonth('date', $data['month_select'])->whereIn('vehicle_id', $vehicle_ids);
		$expense1 = Expense::select(DB::raw("SUM(amount) as expense"))->whereYear('date', $data['year_select'])->whereMonth('date', $data['month_select'])->whereIn('vehicle_id', $vehicle_ids);
		$expense2 = Expense::select("type", "expense_type", DB::raw("sum(amount) as expense"))->whereYear('date', $data['year_select'])->whereMonth('date', $data['month_select'])->whereIn('vehicle_id', $vehicle_ids)->groupBy(['expense_type', 'type']);
		$income2 = IncomeModel::select("income_cat", DB::raw("sum(amount) as amount"))->whereYear('date', $data['year_select'])->whereMonth('date', $data['month_select'])->whereIn('vehicle_id', $vehicle_ids)->groupBy(['income_cat']);
		if ($data['vehicle_select'] != "") {
			$data['income'] = $income1->where('vehicle_id', $data['vehicle_select'])->get();
			$data['expenses'] = $expense1->where('vehicle_id', $data['vehicle_select'])->get();
			$data['expense_by_cat'] = $expense2->where('vehicle_id', $data['vehicle_select'])->get();
			$data['income_by_cat'] = $income2->where('vehicle_id', $data['vehicle_select'])->get();
		} else {
			$data['income'] = $income1->get();
			$data['expenses'] = $expense1->get();
			$data['expense_by_cat'] = $expense2->get();
			$data['income_by_cat'] = $income2->get();
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
		return view("reports.monthly", $data);
	}
	public function fuel() {
		$years = collect(DB::select("select distinct extract(year from date)::int as years from income where deleted_at is null order by years desc"))->toArray();
		$y = array();
		foreach ($years as $year) {
			$y[$year->years] = $year->years;
		}
		if ($years == null) {
			$y[date('Y')] = date('Y');
		}
		$data['vehicles'] = VehicleModel::when(Auth::user()->group_id == null || Auth::user()->user_type == "S", function ($query) {
			// Apply this query when the condition is true (role is false or null)
			return $query->get();
		}, function ($query) {
			// Apply this query when the condition is false (role is provided)
			return $query->where('group_id', Auth::user()->group_id)->get();
		});
		$vehicle_ids = array(0);
		foreach ($data['vehicles'] as $vehicle) {
			$vehicle_ids[] = $vehicle['id'];
		}
		$data['fuel'] = FuelModel::whereIn('vehicle_id', $vehicle_ids)->get();
		$data['vehicle_id'] = "";
		$data['year_select'] = date("Y");
		$data['month_select'] = date("n");
		$data['years'] = $y;
		return view('reports.fuel', $data);
	}
	public function fuel_post(Request $request) {
		$years = collect(DB::select("select distinct extract(year from date)::int as years from income where deleted_at is null order by years desc"))->toArray();
		$y = array();
		foreach ($years as $year) {
			$y[$year->years] = $year->years;
		}
		if ($years == null) {
			$y[date('Y')] = date('Y');
		}
		$data['vehicles'] = VehicleModel::when(Auth::user()->group_id == null || Auth::user()->user_type == "S", function ($query) {
			// Apply this query when the condition is true (role is false or null)
			return $query->get();
		}, function ($query) {
			// Apply this query when the condition is false (role is provided)
			return $query->where('group_id', Auth::user()->group_id)->get();
		});
		$data['vehicle_id'] = $request->get('vehicle_id');
		$data['year_select'] = $request->get('year');
		$data['month_select'] = $request->get('month');
		$data['years'] = $y;
		$v = " and vehicle_id=" . $data['vehicle_id'];
		if ($request->get('month') == '0') {
			$data['fuel'] = FuelModel::whereYear('date', $data['year_select'])->where('vehicle_id', $request->get('vehicle_id'))->get();
		} else {
			$data['fuel'] = FuelModel::whereYear('date', $data['year_select'])->whereMonth('date', $data['month_select'])->where('vehicle_id', $request->get('vehicle_id'))->get();
		}
		$data['result'] = "";
		return view('reports.fuel', $data);
	}
	public function yearly() {
		$years = DB::select("select distinct extract(year from date)::int as years from income union select distinct extract(year from date)::int as years from expense order by years desc");
		$y = array();
		$c = array();
		foreach ($years as $year) {
			$y[$year->years] = $year->years;
		}
		if ($years == null) {
			$y[date('Y')] = date('Y');
		}
		$data['vehicles'] = VehicleModel::when(Auth::user()->group_id == null || Auth::user()->user_type == "S", function ($query) {
			// Apply this query when the condition is true (role is false or null)
			return $query->get();
		}, function ($query) {
			// Apply this query when the condition is false (role is provided)
			return $query->where('group_id', Auth::user()->group_id)->get();
		});
		$vehicle_ids = array(0);
		foreach ($data['vehicles'] as $vehicle) {
			$vehicle_ids[] = $vehicle->id;
		}
		$data['year_select'] = date("Y");
		$data['vehicle_select'] = null;
		$data['years'] = $y;
		$data['income'] = IncomeModel::select(DB::raw('sum(amount) as income'))->whereYear('date', date('Y'))->whereIn('vehicle_id', $vehicle_ids)->get();
		$data['expenses'] = Expense::select(DB::raw('sum(amount) as expense'))->whereYear('date', date("Y"))->whereIn('vehicle_id', $vehicle_ids)->get();
		$data['expense_by_cat'] = Expense::select(['type', 'expense_type', DB::raw('sum(amount) as expense')])->whereYear('date', date('Y'))->whereIn('vehicle_id', $vehicle_ids)->groupBy(['expense_type', 'type'])->get();
		$data['income_by_cat'] = IncomeModel::select(['income_cat', DB::raw('sum(amount) as amount')])->whereYear('date', date('Y'))->whereIn('vehicle_id', $vehicle_ids)->groupBy('income_cat')->get();
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
		return view('reports.yearly', $data);
	}
	public function yearly_post(Request $request) {
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
		$data['vehicles'] = VehicleModel::when(Auth::user()->group_id == null || Auth::user()->user_type == "S", function ($query) {
			// Apply this query when the condition is true (role is false or null)
			return $query->get();
		}, function ($query) {
			// Apply this query when the condition is false (role is provided)
			return $query->where('group_id', Auth::user()->group_id)->get();
		});
		$vehicle_ids = array(0);
		foreach ($data['vehicles'] as $vehicle) {
			$vehicle_ids[] = $vehicle->id;
		}
		$data['year_select'] = $request->get("year");
		$data['vehicle_select'] = $request->get("vehicle_id");
		$income1 = IncomeModel::select(DB::raw("sum(amount) as income"))->whereYear('date', $data['year_select']);
		$expense1 = Expense::select(DB::raw("sum(amount) as expense"))->whereYear('date', $data['year_select']);
		$expense2 = Expense::select("type", "expense_type", DB::raw("sum(amount) as expense"))->whereYear('date', $data['year_select'])->groupBy('expense_type', 'type');
		$income2 = IncomeModel::select('income_cat', DB::raw("sum(amount) as amount"))->whereYear('date', $data['year_select'])->groupBy('income_cat');
		if ($data['vehicle_select'] != "") {
			$data['income'] = $income1->where('vehicle_id', $data['vehicle_select'])->get();
			$data['expenses'] = $expense1->where('vehicle_id', $data['vehicle_select'])->get();
			$data['expense_by_cat'] = $expense2->where('vehicle_id', $data['vehicle_select'])->get();
			$data['income_by_cat'] = $income2->where('vehicle_id', $data['vehicle_select'])->get();
		} else {
			$data['income'] = $income1->whereIn('vehicle_id', $vehicle_ids)->get();
			$data['expenses'] = $expense1->whereIn('vehicle_id', $vehicle_ids)->get();
			$data['expense_by_cat'] = $expense2->whereIn('vehicle_id', $vehicle_ids)->get();
			$data['income_by_cat'] = $income2->whereIn('vehicle_id', $vehicle_ids)->get();
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
		return view('reports.yearly', $data);
	}
	public function vendors() {
		// $data['details'] = DB::select("select vendor_id,sum(price) as total from work_orders where deleted_at is null group by vendor_id"));
		$data['vehicles'] = VehicleModel::when(Auth::user()->group_id == null || Auth::user()->user_type == "S", function ($query) {
			// Apply this query when the condition is true (role is false or null)
			return $query->get();
		}, function ($query) {
			// Apply this query when the condition is false (role is provided)
			return $query->where('group_id', Auth::user()->group_id)->get();
		});
		$vehicle_ids = array(0);
		foreach ($data['vehicles'] as $vehicle) {
			$vehicle_ids[] = $vehicle->id;
		}
		$data['details'] = WorkOrders::select(['vendor_id', DB::raw('sum(price) as total')])->whereIn('vehicle_id', $vehicle_ids)->groupBy('vendor_id')->get();
		// dd($data);
		$kk = WorkOrders::select('vendor_id')->whereIn('vehicle_id', $vehicle_ids)->groupBy('vendor_id')->get();
		$b = array();
		foreach ($kk as $k) {
			$b[$k->vendor_id] = $k->vendor->name;
		}
		$data['vendors'] = $b;
		$data['date1'] = null;
		$data['date2'] = null;
		return view('reports.vendor', $data);
	}
	public function vendors_post(Request $request) {
		$data['vehicles'] = VehicleModel::when(Auth::user()->group_id == null || Auth::user()->user_type == "S", function ($query) {
			// Apply this query when the condition is true (role is false or null)
			return $query->get();
		}, function ($query) {
			// Apply this query when the condition is false (role is provided)
			return $query->where('group_id', Auth::user()->group_id)->get();
		});
		$vehicle_ids = array(0);
		foreach ($data['vehicles'] as $vehicle) {
			$vehicle_ids[] = $vehicle->id;
		}
		$start = date('Y-m-d H:i:s', strtotime($request->get('date1')));
		$end = date('Y-m-d H:i:s', strtotime($request->get('date2')));
		$data['details'] = WorkOrders::select(['vendor_id', DB::raw('sum(price) as total')])->whereBetween('created_at', [$start, $end])->whereIn('vehicle_id', $vehicle_ids)->groupBy('vendor_id')->get();
		$kk = WorkOrders::select('vendor_id')->whereIn('vehicle_id', $vehicle_ids)->groupBy('vendor_id')->get();
		$b = array();
		foreach ($kk as $k) {
			$b[$k->vendor_id] = $k->vendor->name;
		}
		$data['vendors'] = $b;
		$data['date1'] = $request->date1;
		$data['date2'] = $request->date2;
		return view('reports.vendor', $data);
	}
	public function print_deliquent(Request $request) {
		$data['vehicles'] = VehicleModel::when(Auth::user()->group_id == null || Auth::user()->user_type == "S", function ($query) {
			// Apply this query when the condition is true (role is false or null)
			return $query->get();
		}, function ($query) {
			// Apply this query when the condition is false (role is provided)
			return $query->where('group_id', Auth::user()->group_id)->get();
		});
		$vehicle_ids = array(0);
		foreach ($data['vehicles'] as $vehicle) {
			$vehicle_ids[] = $vehicle->id;
		}
		$data['year_select'] = $request->get("year");
		$data['month_select'] = $request->get("month");
		foreach ($data['vehicles'] as $row) {
			$data['v'][$row['id']] = $row;
		}
		$data['vehicle_id'] = $request->get("vehicle_id");
		$income = IncomeModel::select(['date', DB::raw('sum(amount) as "Income2",to_char(date, \'FMDay\') as day,MIN(vehicle_id) as vehicle_id')])->whereYear('date', $data['year_select'])->whereMonth('date', $data['month_select'])->groupBy('date')->orderBy('date');
		if ($data['vehicle_id'] != "") {
			$data['data'] = $income->where('vehicle_id', $data['vehicle_id'])->get();
		} else {
			$data['data'] = $income->whereIn('vehicle_id', $vehicle_ids)->get();
		}
		$data['vehicle'] = VehicleModel::find($request->get('vehicle_id'));
		return view('reports.print_delinquent', $data);
	}
	public function print_monthly(Request $request) {
		$b = array();
		$i = array();
		$c = array();
		$data['year_select'] = $request->get("year");
		$data['month_select'] = $request->get("month");
		$data['vehicle_select'] = $request->get("vehicle_id");
		$data['vehicles'] = VehicleModel::when(Auth::user()->group_id == null || Auth::user()->user_type == "S", function ($query) {
			// Apply this query when the condition is true (role is false or null)
			return $query->get();
		}, function ($query) {
			// Apply this query when the condition is false (role is provided)
			return $query->where('group_id', Auth::user()->group_id)->get();
		});
		$vehicle_ids = array(0);
		foreach ($data['vehicles'] as $vehicle) {
			$vehicle_ids[] = $vehicle->id;
		}
		$income1 = IncomeModel::select(DB::raw("SUM(amount) as income"))->whereYear('date', $data['year_select'])->whereMonth('date', $data['month_select']);
		$expense1 = Expense::select(DB::raw("SUM(amount) as expense"))->whereYear('date', $data['year_select'])->whereMonth('date', $data['month_select']);
		$expense2 = Expense::select("type", "expense_type", DB::raw("sum(amount) as expense"))->whereYear('date', $data['year_select'])->whereMonth('date', $data['month_select'])->groupBy(['expense_type', 'type']);
		$income2 = IncomeModel::select("income_cat", DB::raw("sum(amount) as amount"))->whereYear('date', $data['year_select'])->whereMonth('date', $data['month_select'])->groupBy(['income_cat']);
		if ($data['vehicle_select'] != "") {
			$data['income'] = $income1->where('vehicle_id', $data['vehicle_select'])->get();
			$data['expenses'] = $expense1->where('vehicle_id', $data['vehicle_select'])->get();
			$data['expense_by_cat'] = $expense2->where('vehicle_id', $data['vehicle_select'])->get();
			$data['income_by_cat'] = $income2->where('vehicle_id', $data['vehicle_select'])->get();
		} else {
			$data['income'] = $income1->whereIn('vehicle_id', $vehicle_ids)->get();
			$data['expenses'] = $expense1->whereIn('vehicle_id', $vehicle_ids)->get();
			$data['expense_by_cat'] = $expense2->whereIn('vehicle_id', $vehicle_ids)->get();
			$data['income_by_cat'] = $income2->whereIn('vehicle_id', $vehicle_ids)->get();
		}
		$kk = ExpCats::get();
		$ss = ServiceItemsModel::get();
		foreach ($ss as $s) {
			$c[$s->id] = $s->description;
		}
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
		$data['vehicle'] = VehicleModel::find($request->get("vehicle_id"));
		return view('reports.print_monthly', $data);
	}
	public function print_fuel(Request $request) {
		$data['vehicles'] = VehicleModel::when(Auth::user()->group_id == null || Auth::user()->user_type == "S", function ($query) {
			// Apply this query when the condition is true (role is false or null)
			return $query->get();
		}, function ($query) {
			// Apply this query when the condition is false (role is provided)
			return $query->where('group_id', Auth::user()->group_id)->get();
		});
		$data['vehicle_id'] = $request->get('vehicle_id');
		$data['year_select'] = $request->get('year');
		$data['month_select'] = $request->get('month');
		if ($request->get('month') == '0') {
			$data['fuel'] = FuelModel::whereYear('date', $data['year_select'])->where('vehicle_id', $request->get('vehicle_id'))->get();
		} else {
			$data['fuel'] = FuelModel::whereYear('date', $data['year_select'])->whereMonth('date', $data['month_select'])->where('vehicle_id', $request->get('vehicle_id'))->get();
		}
		$data['vehicle'] = VehicleModel::find($request->get('vehicle_id'));
		return view('reports.print_fuel', $data);
	}
	public function print_yearly(Request $request) {
		$b = array();
		$i = array();
		$c = array();
		$data['vehicles'] = VehicleModel::when(Auth::user()->group_id == null || Auth::user()->user_type == "S", function ($query) {
			// Apply this query when the condition is true (role is false or null)
			return $query->get();
		}, function ($query) {
			// Apply this query when the condition is false (role is provided)
			return $query->where('group_id', Auth::user()->group_id)->get();
		});
		$vehicle_ids = array(0);
		foreach ($data['vehicles'] as $vehicle) {
			$vehicle_ids[] = $vehicle->id;
		}
		$data['year_select'] = $request->get("year");
		$data['vehicle_select'] = $request->get("vehicle_id");
		$income1 = IncomeModel::select(DB::raw("sum(amount) as income"))->whereYear('date', $data['year_select']);
		$expense1 = Expense::select(DB::raw("sum(amount) as expense"))->whereYear('date', $data['year_select']);
		$expense2 = Expense::select("type", "expense_type", DB::raw("sum(amount) as expense"))->whereYear('date', $data['year_select'])->groupBy('expense_type', 'type');
		$income2 = IncomeModel::select('income_cat', DB::raw("sum(amount) as amount"))->whereYear('date', $data['year_select'])->groupBy('income_cat');
		if ($data['vehicle_select'] != "") {
			$data['income'] = $income1->where('vehicle_id', $data['vehicle_select'])->get();
			$data['expenses'] = $expense1->where('vehicle_id', $data['vehicle_select'])->get();
			$data['expense_by_cat'] = $expense2->where('vehicle_id', $data['vehicle_select'])->get();
			$data['income_by_cat'] = $income2->where('vehicle_id', $data['vehicle_select'])->get();
		} else {
			$data['income'] = $income1->whereIn('vehicle_id', $vehicle_ids)->get();
			$data['expenses'] = $expense1->whereIn('vehicle_id', $vehicle_ids)->get();
			$data['expense_by_cat'] = $expense2->whereIn('vehicle_id', $vehicle_ids)->get();
			$data['income_by_cat'] = $income2->whereIn('vehicle_id', $vehicle_ids)->get();
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
		$data['vehicle'] = VehicleModel::find($request->get('vehicle_id'));
		return view('reports.print_yearly', $data);
	}
	public function print_vendor() {
		$data['vehicles'] = VehicleModel::when(Auth::user()->group_id == null || Auth::user()->user_type == "S", function ($query) {
			// Apply this query when the condition is true (role is false or null)
			return $query->get();
		}, function ($query) {
			// Apply this query when the condition is false (role is provided)
			return $query->where('group_id', Auth::user()->group_id)->get();
		});
		$vehicle_ids = array(0);
		foreach ($data['vehicles'] as $vehicle) {
			$vehicle_ids[] = $vehicle->id;
		}
		$data['details'] = WorkOrders::select(['vendor_id', DB::raw('sum(price) as total')])->whereIn('vehicle_id', $vehicle_ids)->groupBy('vendor_id')->get();
		$kk = WorkOrders::select('vendor_id')->whereIn('vehicle_id', $vehicle_ids)->groupBy('vendor_id')->get();
		$b = array();
		foreach ($kk as $k) {
			$b[$k->vendor_id] = $k->vendor->name;
		}
		$data['vendors'] = $b;
		return view('reports.print_vendor', $data);
	}
	public function work_order() {
		$years = DB::select("select distinct extract(year from required_by)::int as years from work_orders where deleted_at is null and required_by is not null order by years desc");
		$y = array();
		foreach ($years as $year) {
			$y[$year->years] = $year->years;
		}
		if ($years == null) {
			$y[date('Y')] = date('Y');
		}
		$data['vehicle'] = VehicleModel::all();
		$data['vehicle_id'] = "";
		$data['year_select'] = date("Y");
		$data['month_select'] = date("n");
		$data['years'] = $y;
		return view('reports.work_order', $data);
	}
	public function work_order_post(Request $request) {
		$years = DB::select("select distinct extract(year from required_by)::int as years from work_orders where deleted_at is null and required_by is not null order by years desc");
		$y = array();
		foreach ($years as $year) {
			$y[$year->years] = $year->years;
		}
		if ($years == null) {
			$y[date('Y')] = date('Y');
		}
		$data['vehicle'] = VehicleModel::all();
		$data['year_select'] = $request->get("year");
		$data['month_select'] = $request->get("month");
		$data['vehicle_id'] = $request->get("vehicle_id");
		$data['data'] = WorkOrders::whereYear('required_by', $data['year_select'])->whereMonth('required_by', $data['month_select'])->where('vehicle_id', $request->get('vehicle_id'))->get();
		$data['years'] = $y;
		$data['result'] = "";
		return view("reports.work_order", $data);
	}
	public function print_workOrder_report(Request $request) {
		$data['year_select'] = $request->get("year");
		$data['month_select'] = $request->get("month");
		$data['data'] = WorkOrders::whereYear('required_by', $data['year_select'])->whereMonth('required_by', $data['month_select'])->where('vehicle_id', $request->get('vehicle_id'))->get();
		return view("reports.print_work_order", $data);
	}
}
