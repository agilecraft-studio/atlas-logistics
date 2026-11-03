<?php
/*
@copyright
Fleet Manager v7.0
Copyright (C) 2017-2025 Atlas Logistics <https://app.atlaslogistics.co.mw> All rights reserved.
Design and developed by Atlas Logistics <https://app.atlaslogistics.co.mw>
 */
namespace App\Http\Controllers\Backend;
use App\Http\Controllers\Controller;
use App\Model\DriverLogsModel;
use App\Model\DriverVehicleModel;
use App\Model\EmailContent;
use App\Model\ExpCats;
use App\Model\Expense;
use App\Model\FuelModel;
use App\Model\IncCats;
use App\Model\IncomeModel;
use App\Model\NotesModel;
use App\Model\PartsCategoryModel;
use App\Model\PartsModel;
use App\Model\PartsUsedModel;
use App\Model\ServiceItemsModel;
use App\Model\ServiceReminderModel;
use App\Model\Settings;
use App\Model\TeamModel;
use App\Model\User;
use App\Model\UserData;
use App\Model\VehicleGroupModel;
use App\Model\VehicleModel;
use App\Model\VehicleReviewModel;
use App\Model\Vendor;
use App\Model\WorkOrderLogs;
use App\Model\WorkOrders;
use Auth;
use DB;
use App\Model\AtlasLogistics;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Validator;

class SettingsApiController extends Controller {
	public function upload_documents(Request $request) {
		$validation = Validator::make($request->all(), [
			'icon_img' => 'required_if:logo_img,|image|mimes:png,jpg,jpeg',
			'logo_img' => 'required_if:icon_img,|image|mimes:png,jpg,jpeg',
		]);
		$errors = $validation->errors();
		if (count($errors) > 0) {
			$data['success'] = "0";
			$data['message'] = implode(", ", $errors->all());
			$data['data'] = "";
		} else {
			if ($request->file('icon_img') && $request->file('icon_img')->isValid()) {
				$this->upload_file($request->file('icon_img'), "value", 'icon_img');
			}
			if ($request->file('logo_img') && $request->file('logo_img')->isValid()) {
				$this->upload_file($request->file('logo_img'), "value", 'logo_img');
			}
			$data['success'] = "1";
			$data['message'] = "Image(s) uploaded successfully!";
			$data['data'] = "";
		}
		return $data;
	}
	public function update(Request $request) {
		$validation = Validator::make($request->all(), [
			'app_name' => 'required',
			'email' => 'required|email',
			'address1' => 'required',
			'address2' => 'required',
			'city' => 'required',
			'state' => 'required',
			'country' => 'required',
			'distance_format' => 'required|in:km,miles',
			'fuel_unit' => 'required|in:gallon,liter',
			'language' => 'required',
			'icon_img' => 'image|mimes:jpg,png,gif,jpeg',
			'logo_img' => 'image|mimes:jpg,png,gif,jpeg',
			'time_interval' => 'required|integer',
			'currency' => 'required',
			'date_format' => 'required',
			'tax_no' => 'required',
			'invoice_text' => 'required',
		]);
		$errors = $validation->errors();
		if (count($errors) > 0) {
			$data['success'] = "0";
			$data['message'] = implode(", ", $errors->all());
			$data['data'] = "";
		} else {
			$date_setting = "d-m-Y";
			if ($request->date_format == "YYYY-MM-DD") {
				$date_setting = 'Y-m-d';
			}
			if ($request->date_format == "MM-DD-YYYY") {
				$date_setting = 'm-d-Y';
			}
			Settings::where('name', 'app_name')->update(['value' => $request->app_name]);
			Settings::where('name', 'email')->update(['value' => $request->email]);
			Settings::where('name', 'badd1')->update(['value' => $request->address1]);
			Settings::where('name', 'badd2')->update(['value' => $request->address2]);
			Settings::where('name', 'city')->update(['value' => $request->city]);
			Settings::where('name', 'state')->update(['value' => $request->state]);
			Settings::where('name', 'country')->update(['value' => $request->country]);
			Settings::where('name', 'dis_format')->update(['value' => $request->distance_format]);
			Settings::where('name', 'fuel_unit')->update(['value' => $request->fuel_unit]);
			Settings::where('name', 'time_interval')->update(['value' => $request->time_interval]);
			Settings::where('name', 'currency')->update(['value' => $request->currency]);
			Settings::where('name', 'date_format')->update(['value' => $date_setting]);
			Settings::where('name', 'tax_no')->update(['value' => $request->tax_no]);
			Settings::where('name', 'invoice_text')->update(['value' => $request->invoice_text]);
			Settings::where('name', 'language')->update(['value' => $request->language]);
			$user = Auth::user();
			$user->language = $request->language;
			$user->save();
			$taxes = json_encode($request->tax);
			Settings::where('name', 'tax_charge')->update(['value' => $taxes]);
			$app_name = str_replace(" ", "_", $request->app_name);
			if (!env('APP_NAME')) {
				file_put_contents(base_path('.env'), "APP_NAME=" . $app_name . PHP_EOL, FILE_APPEND);
			}
			if (env('APP_NAME')) {
				file_put_contents(base_path('.env'), str_replace(
					'APP_NAME=' . env('APP_NAME'), 'APP_NAME=' . $app_name, file_get_contents(base_path('.env'))));
			}
			if ($request->file('icon_img') && $request->file('icon_img')->isValid()) {
				$this->upload_file($request->file('icon_img'), "value", 'icon_img');
			}
			if ($request->file('logo_img') && $request->file('logo_img')->isValid()) {
				$this->upload_file($request->file('logo_img'), "value", 'logo_img');
			}
			$data['success'] = "1";
			$data['message'] = "Settings updated successfully!";
			$data['data'] = "";
		}
		return $data;
	}
	private function upload_file($file, $field, $name) {
		$destinationPath = './assets/images'; // upload path
		$extension = $file->getClientOriginalExtension();
		$fileName1 = Str::uuid() . '.' . $extension;
		$x = Settings::where("name", $name)->first();
		if (file_exists('./uploads/' . $x->$field) && !is_dir('./uploads/' . $x->$field)) {
			unlink('./uploads/' . $x->$field);
		}
		$file->move($destinationPath, $fileName1);
		$x->update([$field => $fileName1]);
	}
	public function clear_database() {
		DriverLogsModel::whereNotNull('id')->delete();
		DriverVehicleModel::whereNotNull('id')->delete();
		Expense::whereNotNull('id')->delete();
		ExpCats::where('type', 'u')->delete();
		FuelModel::whereNotNull('id')->delete();
		IncCats::where('type', 'u')->delete();
		IncomeModel::whereNotNull('id')->delete();
		NotesModel::whereNotNull('id')->delete();
		PartsCategoryModel::whereNotNull('id')->delete();
		PartsModel::whereNotNull('id')->delete();
		PartsUsedModel::whereNotNull('id')->delete();
		ServiceItemsModel::whereNotNull('id')->delete();
		ServiceReminderModel::whereNotNull('id')->delete();
		TeamModel::whereNotNull('id')->delete();
		User::where('id', '!=', 1)->delete();
		UserData::where('user_id', '!=', 1)->delete();
		VehicleGroupModel::whereNotNull('id')->delete();
		VehicleModel::whereNotNull('id')->delete();
		VehicleReviewModel::whereNotNull('id')->delete();
		// VehicleTypeModel::whereNotNull('id')->delete();
		Vendor::whereNotNull('id')->delete();
		WorkOrderLogs::whereNotNull('id')->delete();
		WorkOrders::whereNotNull('id')->delete();
		EmailContent::where('key', 'users')->update(['value' => '']);
		EmailContent::where('key', 'options')->update(['value' => '']);
		DB::table('notifications')->truncate();
		$data['success'] = "1";
		$data['message'] = "Database cleared successfully!";
		$data['data'] = "";
		return $data;
	}
	public function get_general_settings() {
		$user = Auth::user();
		$date_setting = "DD-MM-YYYY";
		if (AtlasLogistics::get('date_format') == 'Y-m-d') {
			$date_setting = "YYYY-MM-DD";
		}
		if (AtlasLogistics::get('date_format') == 'm-d-Y') {
			$date_setting = "MM-DD-YYYY";
		}
		if ($user->language != null) {
			$lang = $user->language;
		} else {
			$lang = AtlasLogistics::get("language");
		}
		$language = explode('-', $lang);
		$taxes = null;
		$new_taxes = array();
		if (AtlasLogistics::get('tax_charge') != "null") {
			$taxes = json_decode(AtlasLogistics::get('tax_charge'), true);
			foreach ($taxes as $key => $val) {
				$new_taxes[] = array(
					'name' => $key,
					'value' => $val,
				);
			}
		}
		$details = array(
			'app_name' => AtlasLogistics::get('app_name'),
			'email' => AtlasLogistics::get('email'),
			'address1' => AtlasLogistics::get('badd1'),
			'address2' => AtlasLogistics::get('badd2'),
			'city' => AtlasLogistics::get('city'),
			'state' => AtlasLogistics::get('state'),
			'country' => AtlasLogistics::get('country'),
			'distance_format' => AtlasLogistics::get("dis_format"),
			'fuel_unit' => AtlasLogistics::get("fuel_unit"),
			'time_interval' => AtlasLogistics::get('time_interval'),
			'icon_image' => asset('assets/images/' . AtlasLogistics::get('icon_img')),
			'logo_image' => asset('assets/images/' . AtlasLogistics::get('logo_img')),
			'currency' => AtlasLogistics::get('currency'),
			'date_format' => $date_setting,
			'tax_no' => AtlasLogistics::get('tax_no'),
			'invoice_text' => AtlasLogistics::get('invoice_text'),
			'language' => ($language[1] == "en") ? "en-us" : $language[1],
			'selected_lang' => $lang,
			// 'tax' => $taxes,
			'tax' => $new_taxes,
		);
		$data['success'] = "1";
		$data['message'] = "Data fetched!";
		$data['data'] = $details;
		return $data;
	}
}
