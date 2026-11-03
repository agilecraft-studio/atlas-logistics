<?php
/*
@copyright
Fleet Manager v7.0
Copyright (C) 2017-2025 Atlas Logistics <https://app.atlaslogistics.co.mw> All rights reserved.
Design and developed by Atlas Logistics <https://app.atlaslogistics.co.mw>
 */
namespace App\Http\Controllers\Backend;
use App\Http\Controllers\Controller;
use App\Imports\CustomerImport;
use App\Model\IncomeModel;
use App\Model\User;
use Auth;
use DB;
use Exception;
use App\Model\AtlasLogistics;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Validator;

class CustomersApiController extends Controller {
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
				// dd($fileName);
				Excel::import(new CustomerImport, 'assets/samples/' . $fileName);
				// $excel = Importer::make('Excel');
				// $excel->load('assets/samples/' . $fileName);
				// $collection = $excel->getCollection()->toArray();
				// array_shift($collection);
				// // dd($collection);
				// foreach ($collection as $customer) {
				//     if ($customer[3] != null) {
				//         $id = User::create([
				//             "name" => $customer[0] . " " . $customer[1],
				//             "email" => $customer[3],
				//             "password" => bcrypt($customer[6]),
				//             "user_type" => "C",
				//             "api_token" => Str::random(60),
				//         ])->id;
				//         $user = User::find($id);
				//         $user->first_name = $customer[0];
				//         $user->last_name = $customer[1];
				//         $user->address = $customer[5];
				//         $user->mobno = $customer[2];
				//         if ($customer[4] == "female") {
				//             $user->gender = 0;
				//         } else {
				//             $user->gender = 1;
				//         }
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
			User::whereIn('id', $request->ids)->delete();
			$data['success'] = "1";
			$data['message'] = "Records deleted successfully!";
			$data['data'] = "";
		}
		return $data;
	}
	public function update(Request $request) {
		$validation = Validator::make($request->all(), [
			'id' => 'required|integer',
			'first_name' => 'required',
			'last_name' => 'required',
			'email' => 'unique:users,email,' . \Request::get("id"),
			'phone' => 'required|numeric',
			'gender' => 'required|integer',
		]);
		$errors = $validation->errors();
		if (count($errors) > 0) {
			$data['success'] = "0";
			$data['message'] = implode(", ", $errors->all());
			$data['data'] = "";
		} else {
			$user = User::find($request->id);
			$user->name = $request->first_name . " " . $request->last_name;
			$user->email = $request->email;
			$user->first_name = $request->first_name;
			$user->last_name = $request->last_name;
			$user->address = $request->address;
			$user->mobno = $request->phone;
			$user->gender = $request->gender;
			$user->save();
			$data['success'] = "1";
			$data['message'] = "Customer updated successfully!";
			$data['data'] = "";
		}
		return $data;
	}
	public function store(Request $request) {
		$validation = Validator::make($request->all(), [
			'first_name' => 'required',
			'last_name' => 'required',
			'email' => 'unique:users,email',
			'phone' => 'required|numeric',
			'gender' => 'required|integer',
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
				"password" => bcrypt("password"),
				"user_type" => "C",
				"api_token" => Str::random(60),
			])->id;
			$user = User::find($id);
			$user->first_name = $request->first_name;
			$user->last_name = $request->last_name;
			$user->address = $request->address;
			$user->mobno = $request->phone;
			$user->gender = $request->gender;
			$user->save();
			$data['success'] = "1";
			$data['message'] = "Customer added successfully!";
			$data['data'] = array('id' => $user->id);
		}
		return $data;
	}
	public function customers() {
		$customers = User::where("user_type", "C")->orderBy('id', 'desc')->get();
		$details = array();
		foreach ($customers as $row) {
			$details[] = array(
				"id" => $row->id,
				"name" => $row->name,
				"first_name" => $row->first_name,
				"last_name" => $row->last_name,
				"email" => $row->email,
				"address" => $row->address,
				"phone" => $row->mobno,
				"gender" => $row->gender,
				'gender_text' => ($row->gender == 1) ? "Male" : "Female",
			);
		}
		$data['success'] = "1";
		$data['message'] = "Data fetched!";
		$data['data'] = $details;
		return $data;
	}
}
