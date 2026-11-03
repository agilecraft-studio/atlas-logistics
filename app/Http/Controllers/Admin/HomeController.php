<?php
/*
@copyright
Fleet Manager v7.0
Copyright (C) 2017-2025 Atlas Logistics <https://app.atlaslogistics.co.mw> All rights reserved.
Design and developed by Atlas Logistics <https://app.atlaslogistics.co.mw>
 */
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Model\Expense;
use App\Model\IncomeModel;
use App\Model\User;
use App\Model\VehicleModel;
use App\Model\Vendor;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Model\AtlasLogistics;


class HomeController extends Controller {
	public function index() {
		// Fleet Operations Dashboard is the default landing for internal staff
		// (drivers/customers keep their own views below; internal.staff blocks
		// them from the fleet route anyway).
		if (! in_array(Auth::user()->user_type, ["D", "C"], true)
			&& Auth::user()->can('LogisticsReports list')) {
			return redirect()->route('logistics.dashboard');
		}
		if (Auth::user()->user_type == "D") {
			$index['data'] = User::whereId(Auth::user()->id)->first();
			return view("drivers.profile", $index);
		} elseif (Auth::user()->user_type == "C") {
			abort(403, 'Customer portal is not available.');
		} else {
			if (isset($_GET['year'])) {
				$pass_year = $_GET['year'];
			} else {
				$pass_year = date("Y");
			}
			$years = DB::select("select distinct extract(year from date)::int as years from income union select distinct extract(year from date)::int as years from expense order by years desc");
			$y = array();
			foreach ($years as $year) {
				$y[$year->years] = $year->years;
			}
			if ($years == null) {
				$y[date('Y')] = date('Y');
			}
			$index['year_select'] = $pass_year;
			$index['years'] = $y;
			$index['drivers'] = User::whereUser_type("D")->get()->count();
			$index['customers'] = User::whereUser_type("C")->get()->count();
			$index['users'] = User::whereUser_type("O")->get()->count();
			$vehicle_ids = array(0);
			if (Auth::user()->group_id == null || Auth::user()->user_type == "S") {
				$index['vehicles'] = VehicleModel::all()->count();
				$vehicle_ids = VehicleModel::pluck('id')->toArray();
				if ($vehicle_ids == null) {
					$vehicle_ids = array(0);
				}
			} else {
				$index['vehicles'] = VehicleModel::where('group_id', Auth::user()->group_id)->count();
				$vehicle_ids = VehicleModel::where('group_id', Auth::user()->group_id)->pluck('id')->toArray();
				if ($vehicle_ids == null) {
					$vehicle_ids = array(0);
				}
			}
			$index['vendors'] = Vendor::all()->count();
			$index['yearly_income'] = $this->yearly_income($pass_year);
			$index['yearly_expense'] = $this->yearly_expense($pass_year);
			$vv = array();
			if (Auth::user()->group_id == null || Auth::user()->user_type == "S") {
				$all_vehicles = VehicleModel::get();
			} else {
				$all_vehicles = VehicleModel::where('group_id', Auth::user()->group_id)->get();
			}
			foreach ($all_vehicles as $key) {
				$vv[$key->id] = $key->make_name . "-" . $key->model_name . "-" . $key->license_plate;
			}
			$index['vehicle_name'] = $vv;
			$index['expenses'] = Expense::select('vehicle_id', DB::raw('sum(amount) as expense'))->whereIn('vehicle_id', $vehicle_ids)->whereYear('date', date('Y'))->whereMonth('date', date('n'))->groupBy('vehicle_id')->get();
			$index['income'] = IncomeModel::whereYear('date', date("Y"))->whereMonth('date', date("n"))->whereIn('vehicle_id', $vehicle_ids)->sum("amount");
			// dd($vehicle_ids);
			$index['expense'] = Expense::whereYear('date', date("Y"))->whereMonth('date', date("n"))->whereIn('vehicle_id', $vehicle_ids)->sum("amount");
			$exp = DB::select('select date,sum(amount) as tot from expense where deleted_at is null and vehicle_id in (' . join(",", $vehicle_ids) . ') group by date');
			$inc = DB::select('select date,sum(amount) as tot from income where deleted_at is null and vehicle_id in (' . join(",", $vehicle_ids) . ') group by date');
			$date1 = IncomeModel::pluck('date')->toArray();
			$date2 = Expense::pluck('date')->toArray();
			$all_dates = array_unique(array_merge($date1, $date2));
			$dates = array_count_values($all_dates);
			ksort($dates);
			$dates = array_slice($dates, -12, 12);
			$index['dates'] = $dates;
			$temp = array();
			foreach ($all_dates as $key) {
				$temp[$key] = 0;
			}
			$income2 = array();
			foreach ($inc as $income) {
				$income2[$income->date] = $income->tot;
			}
			$inc_data = array_merge($temp, $income2);
			ksort($inc_data);
			$index['incomes'] = implode(",", array_slice($inc_data, -12, 12));
			$expense2 = array();
			foreach ($exp as $e) {
				$expense2[$e->date] = $e->tot;
			}
			$expenses = array_merge($temp, $expense2);
			ksort($expenses);
			$index['expenses1'] = implode(",", array_slice($expenses, -12, 12));

			return view('home', $index);
		}
	}
	private function yearly_income($year) {
		if (Auth::user()->group_id == null || Auth::user()->user_type == "S") {
			$all_vehicles = VehicleModel::get();
		} else {
			$all_vehicles = VehicleModel::where('group_id', Auth::user()->group_id)->get();
		}
		$vehicle_ids = array(0);
		foreach ($all_vehicles as $key) {
			$vehicle_ids[] = $key->id;
		}
		$incomes = DB::select("select to_char(date, 'FMMonth') as mnth,sum(amount) as tot from income where to_char(date, 'YYYY')=? and  deleted_at is null and vehicle_id in (" . join(",", $vehicle_ids) . ") group by to_char(date, 'FMMonth'), extract(month from date)", [$year]);
		$months = ["January" => 0, "February" => 0, "March" => 0, "April" => 0, "May" => 0, "June" => 0, "July" => 0, "August" => 0, "September" => 0, "October" => 0, "November" => 0, "December" => 0];
		$income2 = array();
		foreach ($incomes as $income) {
			$income2[$income->mnth] = $income->tot;
		}
		$yr = array_merge($months, $income2);
		return implode(",", $yr);
	}
	private function yearly_expense($year) {
		if (Auth::user()->group_id == null || Auth::user()->user_type == "S") {
			$all_vehicles = VehicleModel::get();
		} else {
			$all_vehicles = VehicleModel::where('group_id', Auth::user()->group_id)->get();
		}
		$vehicle_ids = array(0);
		foreach ($all_vehicles as $key) {
			$vehicle_ids[] = $key->id;
		}
		$incomes = DB::select("select to_char(date, 'FMMonth') as mnth,sum(amount) as tot from expense where to_char(date, 'YYYY')=? and  deleted_at is null and vehicle_id in (" . join(",", $vehicle_ids) . ") group by to_char(date, 'FMMonth'), extract(month from date)", [$year]);
		$months = ["January" => 0, "February" => 0, "March" => 0, "April" => 0, "May" => 0, "June" => 0, "July" => 0, "August" => 0, "September" => 0, "October" => 0, "November" => 0, "December" => 0];
		$income2 = array();
		foreach ($incomes as $income) {
			$income2[$income->mnth] = $income->tot;
		}
		$yr = array_merge($months, $income2);
		return implode(",", $yr);
	}
	public function logout() {
		Auth::logout();
		return redirect('admin/');
	}
}
