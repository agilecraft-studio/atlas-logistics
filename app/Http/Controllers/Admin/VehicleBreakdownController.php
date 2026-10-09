<?php
/*
@copyright
Fleet Manager v7.0
Copyright (C) 2017-2025 Atlas Logistics <https://app.atlaslogistics.co.mw> All rights reserved.
Design and developed by Atlas Logistics <https://app.atlaslogistics.co.mw>
 */
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Http\Requests\NotesRequest;
use App\Model\VehicleBreakdown;
use Auth;
use Illuminate\Http\Request;
use Validator;

class VehicleBreakdownController extends Controller {

	public function index() {
		$data=VehicleBreakdown::all();
		return view('vehicle_breakdown.index',compact('data'));
	}
	public function create() {
        return view('vehicle_breakdown.create');
	}
	public function store(Request $request) {
	
        $validation = Validator::make($request->all(), [
            'name' => 'required',
        ]);

        if ($validation->fails()) {
            return back()->withErrors($validation)->withInput();
        } 

        $data=new VehicleBreakdown();
        $data->name=$request->name;
        $data->save();

		return redirect(asset('/admin/vehicle-breakdown'));
	}
	public function edit($id) {
		$data=VehicleBreakdown::find($id);

		return view('vehicle_breakdown.edit',compact('data'));
    
	}
	public function update(Request $request) {
		
		$validation = Validator::make($request->all(), [
            'name' => 'required',
        ]);

        if ($validation->fails()) {
            return back()->withErrors($validation)->withInput();
        } 
		
		$data=VehicleBreakdown::find($request->id);
        $data->name=$request->name;
        $data->save();

		return redirect(asset('/admin/vehicle-breakdown'));
	}
	public function destroy(Request $request) {
	
		VehicleBreakdown::where('id', $request->id)->delete();
		return redirect(asset('/admin/vehicle-breakdown'));

	}
	public function bulk_delete(Request $request) {
		$ids=$request->ids;
		foreach($ids as $id)
		{
			$data=VehicleBreakdown::where('id', $id)->delete();
		}
		return redirect(asset('/admin/vehicle-breakdown'));
	}
}
