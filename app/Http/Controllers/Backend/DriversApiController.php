<?php
/*
@copyright
Fleet Manager v7.0
Copyright (C) 2017-2025 Atlas Logistics <https://app.atlaslogistics.co.mw> All rights reserved.
Design and developed by Atlas Logistics <https://app.atlaslogistics.co.mw>
 */
namespace App\Http\Controllers\Backend;
use App\Http\Controllers\Controller;
use App\Imports\DriverImport;
use App\Model\DriverLogsModel;
use App\Model\DriverVehicleModel;
use App\Model\User;
use App\Model\VehicleModel;
use App\Rules\UniqueContractNumber;
use App\Rules\UniqueEId;
use App\Rules\UniqueLicenceNumber;
use Auth;
use DB;
use Exception;
use App\Model\AtlasLogistics;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Validator;

class DriversApiController extends Controller {
	public function import_records(Request $request) {
		$validation = Validator::make($request->all(), [
			'excel' => 'required|mimes:xlsx,xls',
		]);
		$errors = $validation->errors();
		if (count($errors) > 0) {
			$data['success'] = "0";
			$data['message'] = implode(", ", $errors->all());
			$data['data'] = "";
		} else {
			try {
				$file = $request->excel;
				$destinationPath = './assets/samples/'; // upload path
				$extension = $file->getClientOriginalExtension();
				$fileName = Str::uuid() . '.' . $extension;
				$file->move($destinationPath, $fileName);
				Excel::import(new DriverImport, 'assets/samples/' . $fileName);
				// $excel = Importer::make('Excel');
				// $excel->load('assets/samples/' . $fileName);
				// $collection = $excel->getCollection()->toArray();
				// array_shift($collection);
				// // dd($collection);
				// foreach ($collection as $driver) {
				//     if ($driver[4] != null) {
				//         $id = User::create([
				//             "name" => $driver[0] . " " . $driver[2],
				//             "email" => $driver[4],
				//             "password" => bcrypt($driver[15]),
				//             "user_type" => "D",
				//             'api_token' => Str::random(60),
				//         ])->id;
				//         $user = User::find($id);
				//         $user->is_active = 1;
				//         $user->is_available = 0;
				//         $user->first_name = $driver[0];
				//         $user->middle_name = $driver[1];
				//         $user->last_name = $driver[2];
				//         $user->address = $driver[3];
				//         $user->phone = $driver[5];
				//         $user->phone_code = "+" . $driver[6];
				//         $user->emp_id = $driver[7];
				//         $user->contract_number = $driver[8];
				//         $user->license_number = $driver[9];
				//         if ($driver[10] != null) {
				//             $user->issue_date = date('Y-m-d', strtotime($driver[10]));
				//         }
				//         if ($driver[11] != null) {
				//             $user->exp_date = date('Y-m-d', strtotime($driver[11]));
				//         }
				//         if ($driver[12] != null) {
				//             $user->start_date = date('Y-m-d', strtotime($driver[12]));
				//         }
				//         if ($driver[13] != null) {
				//             $user->end_date = date('Y-m-d', strtotime($driver[13]));
				//         }
				//         $user->gender = (($driver[14] == 'female') ? 0 : 1);
				//         $user->econtact = $driver[15];
				//         $user->save();
				//     }
				// }
				$data['success'] = "1";
				$data['message'] = "Records imported successfully!";
				$data['data'] = "";
			} catch (Exception $e) {
				$data['success'] = "0";
				$data['message'] = "Unable to import records.";
				$data['data'] = "";
			}
		}
		return $data;
	}
	public function profile() {
		$middlename = (Auth::user()->middle_name) ? Auth::user()->middle_name . " " : "";
		if (Auth::user()->getMeta('driver_image') != null) {
			if (starts_with(Auth::user()->getMeta('driver_image'), 'http')) {
				$src = Auth::user()->getMeta('driver_image');
			} else {
				$src = asset('uploads/' . Auth::user()->getMeta('driver_image'));
			}
		} else {
			$src = asset("assets/images/no-user.jpg");
		}
		$user = Auth::user();
		if ($user->language != null) {
			$language = $user->language;
		} else {
			$language = AtlasLogistics::get("language");
		}
		$details = array(
			'driver_info' => array(
				'id' => Auth::id(),
				'name' => Auth::user()->name,
				'full_name' => Auth::user()->first_name . " " . $middlename . Auth::user()->last_name,
				'phone' => Auth::user()->phone,
				'email' => Auth::user()->email,
				'address' => Auth::user()->address,
				'license_number' => Auth::user()->license_number,
				'issue_date' => Auth::user()->issue_date,
				'exp_date' => Auth::user()->exp_date,
				'emp_id' => Auth::user()->emp_id,
				'contract_number' => Auth::user()->contract_number,
				'license_image' => (Auth::user()->license_image) ? asset('uploads/' . Auth::user()->license_image) : "",
				'documents' => (Auth::user()->documents) ? asset('uploads/' . Auth::user()->documents) : "",
				'driver_image' => $src,
				"language" => $language,
			),
		);
		$data['success'] = "1";
		$data['message'] = "Data fetched!";
		$data['data'] = $details;
		return $data;
	}
	public function bulk_delete(Request $request) {
		$validation = Validator::make($request->all(), [
			'ids' => 'required|array',
		]);
		$errors = $validation->errors();
		if (count($errors) > 0) {
			$data['success'] = "0";
			$data['message'] = implode(", ", $errors->all());
			$data['data'] = "";
		} else {
			$drivers = User::whereIn('id', $request->ids)->get();
			foreach ($drivers as $driver) {
				if ($driver->vehicle_id) {
					$vehicle = VehicleModel::find($driver->vehicle_id);
					if ($vehicle != null) {
						$vehicle->driver_id = null;
						$vehicle->save();
					}
				}
				if (file_exists('./uploads/' . $driver->driver_image) && !is_dir('./uploads/' . $driver->driver_image)) {
					unlink('./uploads/' . $driver->driver_image);
				}
				if (file_exists('./uploads/' . $driver->license_image) && !is_dir('./uploads/' . $driver->license_image)) {
					unlink('./uploads/' . $driver->license_image);
				}
				if (file_exists('./uploads/' . $driver->documents) && !is_dir('./uploads/' . $driver->documents)) {
					unlink('./uploads/' . $driver->documents);
				}
			}
			DriverVehicleModel::whereIn('driver_id', $request->ids)->delete();
			User::whereIn('id', $request->ids)->delete();
			$data['success'] = "1";
			$data['message'] = "Records deleted successfully!";
			$data['data'] = "";
		}
		return $data;
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
	public function upload_documents(Request $request, $id) {
		$validation = Validator::make($request->all(), [
			// 'id' => 'required|integer',
			'driver_image' => 'nullable|image|mimes:jpg,png,jpeg',
			'license_image' => 'nullable|image|mimes:jpg,png,jpeg',
			'documents' => 'nullable|mimes:jpg,png,jpeg,pdf,doc,docx',
		]);
		$errors = $validation->errors();
		if (count($errors) > 0) {
			$data['success'] = "0";
			$data['message'] = implode(", ", $errors->all());
			$data['data'] = "";
		} else {
			// $file = $request->driver_image;
			// $destinationPath = './uploads'; // upload path
			// $extension = $file->getClientOriginalExtension();
			// $fileName1 = Str::uuid() . '.' . $extension;
			// $file->move($destinationPath, $fileName1);
			// dd($fileName1);
			// $user = User::find($id);
			// $user->setMeta([$field => $fileName1]);
			// $user->save();
			$user = User::find($id);
			if ($request->file('driver_image') && $request->file('driver_image')->isValid()) {
				if (file_exists('./uploads/' . $user->driver_image) && !is_dir('./uploads/' . $user->driver_image)) {
					unlink('./uploads/' . $user->driver_image);
				}
				$this->upload_file($request->file('driver_image'), "driver_image", $id);
			}
			// dd($request->file('driver_image'), $user->driver_image);
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
			$data['success'] = "1";
			$data['message'] = "Documents uploaded successfully!";
			$data['data'] = "";
		}
		return $data;
	}
	public function delete(Request $request) {
		$validation = Validator::make($request->all(), [
			'id' => 'required|integer',
		]);
		$errors = $validation->errors();
		if (count($errors) > 0) {
			$data['success'] = "0";
			$data['message'] = implode(", ", $errors->all());
			$data['data'] = "";
		} else {
			$driver = User::find($request->id);
			if ($driver->vehicle_id) {
				$vehicle = VehicleModel::find($driver->vehicle_id);
				if ($vehicle != null) {
					$vehicle->driver_id = null;
					$vehicle->save();
				}
			}
			if (file_exists('./uploads/' . $driver->driver_image) && !is_dir('./uploads/' . $driver->driver_image)) {
				unlink('./uploads/' . $driver->driver_image);
			}
			if (file_exists('./uploads/' . $driver->license_image) && !is_dir('./uploads/' . $driver->license_image)) {
				unlink('./uploads/' . $driver->license_image);
			}
			if (file_exists('./uploads/' . $driver->documents) && !is_dir('./uploads/' . $driver->documents)) {
				unlink('./uploads/' . $driver->documents);
			}
			DriverVehicleModel::where('driver_id', $request->id)->delete();
			User::find($request->get('id'))->user_data()->delete();
			User::find($request->get('id'))->delete();
			$data['success'] = "1";
			$data['message'] = "Record deleted successfully!";
			$data['data'] = "";
		}
		return $data;
	}
	public function update(Request $request) {
		$validation = Validator::make($request->all(), [
			'emp_id' => ['required', new UniqueEId],
			'license_number' => ['required', new UniqueLicenceNumber],
			'contract_number' => ['required', new UniqueContractNumber],
			// 'emp_id' => 'required',
			// 'license_number' => 'required',
			// 'contract_number' => 'required',
			'first_name' => 'required',
			'last_name' => 'required',
			'address' => 'required',
			'phone' => 'required|numeric',
			'email' => 'required|email|unique:users,email,' . \Request::get("id"),
			'exp_date' => 'required|date|after:tomorrow',
			'start_date' => 'date',
			'issue_date' => 'date',
			'end_date' => 'nullable|date',
			'driver_image' => 'nullable|image|mimes:jpg,png,jpeg',
			'license_image' => 'nullable|image|mimes:jpg,png,jpeg',
			'documents' => 'nullable|mimes:jpg,png,jpeg,pdf,doc,docx',
			'gender' => 'required|integer',
			'vehicle_id' => 'required|integer',
			'id' => 'required|integer',
			'edit' => 'required|integer|min:1|max:1',
		]);
		$errors = $validation->errors();
		if (count($errors) > 0) {
			$data['success'] = "0";
			$data['message'] = implode(", ", $errors->all());
			$data['data'] = "";
		} else {
			// dd($request->all());
			$id = $request->id;
			$user = User::find($id);
			if ($user->vehicle_id != $request->vehicle_id) {
				$old_vehicle = VehicleModel::find($user->vehicle_id);
				$old_vehicle->driver_id = null;
				$old_vehicle->save();
				$vehicle = VehicleModel::find($request->vehicle_id);
				$vehicle->driver_id = $user->id;
				$vehicle->save();
				DriverLogsModel::create(['driver_id' => $user->id, 'vehicle_id' => $request->vehicle_id, 'date' => date('Y-m-d H:i:s')]);
				DriverVehicleModel::updateOrCreate(['driver_id' => $user->id], ['vehicle_id' => $request->vehicle_id, 'driver_id' => $user->id]);
			}
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
			$user->name = $request->first_name . " " . $request->last_name;
			$user->email = $request->email;
			$user->save();
			$form_data = $request->all();
			unset($form_data['driver_image']);
			unset($form_data['documents']);
			unset($form_data['license_image']);
			unset($form_data['edit']);
			unset($form_data['id']);
			$user->setMeta($form_data);
			$user->exp_date = date('Y-m-d', strtotime($request->exp_date));
			$user->start_date = date('Y-m-d', strtotime($request->start_date));
			$user->issue_date = date('Y-m-d', strtotime($request->issue_date));
			if ($request->end_date) {
				$user->end_date = date('Y-m-d', strtotime($request->end_date));
			}
			$user->save();
			$data['success'] = "1";
			$data['message'] = "Driver updated successfully!";
			$data['data'] = "";
		}
		return $data;
	}
	public function drivers() {
		$drivers = User::whereUser_type("D")->orderBy('id', 'desc')->get();
		$details = array();
		foreach ($drivers as $row) {
			$details[] = array(
				'name' => $row->name,
				'email' => $row->email,
				'phone' => $row->phone,
				'image' => ($row->driver_image) ? url('uploads/' . $row->driver_image) : url('assets/images/user-noimage.png'),
				'startDate' => date("Y/m/d", strtotime($row->start_date)),
				'active' => ($row->is_active) ? true : false,
				'action' => '',
				// other info
				'id' => $row->id,
				'first_name' => $row->first_name,
				'middle_name' => $row->middle_name,
				'last_name' => $row->last_name,
				'vehicle_id' => $row->vehicle_id,
				'address' => $row->address,
				'phone_code' => $row->phone_code,
				'emp_id' => $row->emp_id,
				'contract_number' => $row->contract_number,
				'license_number' => $row->license_number,
				'issue_date' => date("Y/m/d", strtotime($row->issue_date)),
				'exp_date' => date("Y/m/d", strtotime($row->exp_date)),
				'start_date' => date("Y/m/d", strtotime($row->start_date)),
				'end_date' => date("Y/m/d", strtotime($row->end_date)),
				'gender_text' => ($row->gender == 1) ? "Male" : "Female",
				'gender' => $row->gender,
				'license_image' => ($row->license_image) ? url('uploads/' . $row->license_image) : null,
				'documents' => ($row->documents) ? url('uploads/' . $row->documents) : null,
				'econtact' => $row->econtact,
				'created' => date("Y-m-d", strtotime($row->created_at)),
			);
		}
		$data['success'] = "1";
		$data['message'] = "Data fetched!";
		$data['data'] = $details;
		return $data;
	}
	public function store(Request $request) {
		$validation = Validator::make($request->all(), [
			'emp_id' => ['required', new UniqueEId],
			'license_number' => ['required', new UniqueLicenceNumber],
			'contract_number' => ['required', new UniqueContractNumber],
			'first_name' => 'required',
			'last_name' => 'required',
			'address' => 'required',
			'phone' => 'required|numeric',
			'email' => 'required|email|unique:users,email',
			'exp_date' => 'required|date|after:tomorrow',
			'start_date' => 'date',
			'issue_date' => 'date',
			'end_date' => 'nullable|date',
			// 'driver_image' => 'nullable|image|mimes:jpg,png,jpeg',
			// 'license_image' => 'nullable|image|mimes:jpg,png,jpeg',
			// 'documents' => 'nullable|mimes:jpg,png,jpeg,pdf,doc,docx',
			'password' => 'required',
			'gender' => 'required|integer',
			'vehicle_id' => 'required|integer',
		]);
		$errors = $validation->errors();
		if (count($errors) > 0) {
			$data['success'] = "0";
			$data['message'] = implode(", ", $errors->all());
			$data['data'] = "";
		} else {
			$id = User::create([
				"name" => $request->first_name . " " . $request->last_name,
				"email" => $request->email,
				"password" => bcrypt($request->password),
				"user_type" => "D",
				'api_token' => Str::random(60),
			])->id;
			$user = User::find($id);
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
			$user->setMeta($form_data);
			$user->is_active = 0;
			$user->is_available = 0;
			$user->exp_date = date('Y-m-d', strtotime($request->exp_date));
			$user->start_date = date('Y-m-d', strtotime($request->start_date));
			$user->issue_date = date('Y-m-d', strtotime($request->issue_date));
			if ($request->end_date) {
				$user->end_date = date('Y-m-d', strtotime($request->end_date));
			}
			$user->save();
			$vehicle = VehicleModel::find($request->vehicle_id);
			$vehicle->driver_id = $user->id;
			$vehicle->save();
			DriverLogsModel::create(['driver_id' => $user->id, 'vehicle_id' => $request->vehicle_id, 'date' => date('Y-m-d H:i:s')]);
			DriverVehicleModel::updateOrCreate(['vehicle_id' => $request->vehicle_id], ['vehicle_id' => $request->vehicle_id, 'driver_id' => $user->id]);
			$data['success'] = "1";
			$data['message'] = "Driver added successfully!";
			$data['data'] = array('id' => $user->id);
		}
		return $data;
	}
	public function phone_codes() {
		$data = array('+93',
			'+358',
			'+355',
			'+213',
			'+1 684',
			'+376',
			'+244',
			'+1 264',
			'+672',
			'+1268',
			'+54',
			'+374',
			'+297',
			'+61',
			'+43',
			'+994',
			'+1 242',
			'+973',
			'+880',
			'+1 246',
			'+375',
			'+32',
			'+501',
			'+229',
			'+1 441',
			'+975',
			'+591',
			'+387',
			'+267',
			'+55',
			'+246',
			'+673',
			'+359',
			'+226',
			'+257',
			'+855',
			'+237',
			'+1',
			'+238',
			'+ 345',
			'+236',
			'+235',
			'+56',
			'+86',
			'+61',
			'+61',
			'+57',
			'+269',
			'+242',
			'+243',
			'+682',
			'+506',
			'+225',
			'+385',
			'+53',
			'+357',
			'+420',
			'+45',
			'+253',
			'+1 767',
			'+1 849',
			'+593',
			'+20',
			'+503',
			'+240',
			'+291',
			'+372',
			'+251',
			'+500',
			'+298',
			'+679',
			'+358',
			'+33',
			'+594',
			'+689',
			'+241',
			'+220',
			'+995',
			'+49',
			'+233',
			'+350',
			'+30',
			'+299',
			'+1 473',
			'+590',
			'+1 671',
			'+502',
			'+44',
			'+224',
			'+245',
			'+595',
			'+509',
			'+379',
			'+504',
			'+852',
			'+36',
			'+354',
			'+91',
			'+62',
			'+98',
			'+964',
			'+353',
			'+44',
			'+972',
			'+39',
			'+1 876',
			'+81',
			'+44',
			'+962',
			'+7 7',
			'+254',
			'+686',
			'+850',
			'+82',
			'+965',
			'+996',
			'+856',
			'+371',
			'+961',
			'+266',
			'+231',
			'+218',
			'+423',
			'+370',
			'+352',
			'+853',
			'+389',
			'+261',
			'+265',
			'+60',
			'+960',
			'+223',
			'+356',
			'+692',
			'+596',
			'+222',
			'+230',
			'+262',
			'+52',
			'+691',
			'+373',
			'+377',
			'+976',
			'+382',
			'+1664',
			'+212',
			'+258',
			'+95',
			'+264',
			'+674',
			'+977',
			'+31',
			'+599',
			'+687',
			'+64',
			'+505',
			'+227',
			'+234',
			'+683',
			'+672',
			'+1 670',
			'+47',
			'+968',
			'+92',
			'+680',
			'+970',
			'+507',
			'+675',
			'+595',
			'+51',
			'+63',
			'+872',
			'+48',
			'+351',
			'+1 939',
			'+974',
			'+40',
			'+7',
			'+250',
			'+262',
			'+590',
			'+290',
			'+1 869',
			'+1 758',
			'+590',
			'+508',
			'+1 784',
			'+685',
			'+378',
			'+239',
			'+966',
			'+221',
			'+381',
			'+248',
			'+232',
			'+65',
			'+421',
			'+386',
			'+677',
			'+252',
			'+27',
			'+500',
			'+34',
			'+94',
			'+249',
			'+597',
			'+47',
			'+268',
			'+46',
			'+41',
			'+963',
			'+886',
			'+992',
			'+255',
			'+66',
			'+670',
			'+228',
			'+690',
			'+676',
			'+1 868',
			'+216',
			'+90',
			'+993',
			'+1 649',
			'+688',
			'+256',
			'+380',
			'+971',
			'+44',
			'+1',
			'+598',
			'+998',
			'+678',
			'+58',
			'+84',
			'+1 284',
			'+1 340',
			'+681',
			'+967',
			'+260',
			'+263',
			'+1 809',
			'+1 829',
		);
		return $data;
	}
	public function vehicles($vid) {
		if ($vid == 0) {
			$exclude = DriverVehicleModel::select('vehicle_id')->get('vehicle_id')->pluck('vehicle_id')->toArray();
		} else {
			$exclude = DriverVehicleModel::select('vehicle_id')->where('vehicle_id', '!=', $vid)->get('vehicle_id')->pluck('vehicle_id')->toArray();
		}
		if (Auth::user()->group_id == null || Auth::user()->user_type == "S") {
			$vehicles = VehicleModel::whereNotIn('id', $exclude)->get();
		} else {
			$vehicles = VehicleModel::where('group_id', Auth::user()->group_id)->whereNotIn('id', $exclude)->get();
		}
		$details = array();
		foreach ($vehicles as $row) {
			$details[] = array(
				'id' => $row->id,
				'vehicle' => $row->make_name . " - " . $row->model_name . " - " . $row->license_plate,
			);
		}
		$data['success'] = "1";
		$data['message'] = "Data fetched!";
		$data['data'] = $details;
		return $data;
	}
	public function enable_disable(Request $request) {
		$validation = Validator::make($request->all(), [
			'is_active' => 'required|integer',
			'id' => 'required|integer',
		]);
		$errors = $validation->errors();
		if (count($errors) > 0) {
			$data['success'] = "0";
			$data['message'] = implode(", ", $errors->all());
			$data['data'] = "";
		} else {
			$driver = User::find($request->id);
			$driver->is_active = $request->is_active;
			$driver->save();
			$data['success'] = "1";
			$data['message'] = "Driver's active status changed successfully!";
			$data['data'] = "";
		}
		return $data;
	}
}
