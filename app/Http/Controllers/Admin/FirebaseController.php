<?php
/*
@copyright
Fleet Manager v7.0
Copyright (C) 2017-2025 Atlas Logistics <https://app.atlaslogistics.co.mw> All rights reserved.
Design and developed by Atlas Logistics <https://app.atlaslogistics.co.mw>
 */
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;

class FirebaseController extends Controller {
	public function index() {
		$database = app('firebase.database');
		$data = $database
			->getReference('/')
			->orderByValue('user_type')
			->equalTo('D')
			->limitToFirst(5)
			->getSnapshot();
		dd($data);
		$data = $database
			->getReference('/')
			->orderByChild('user_type')
			->equalTo('D')
			->getValue();
		dd($data);
		foreach ($data as $d) {
			if ($d['user_id'] == "74") {
				if ($d['availability'] == "1") {
					echo $d['availability'] . "<br>";
					echo $d['last_updated'] . "<br>";
					echo $d['latitude'] . "<br>";
					echo $d['longitude'] . "<br>";
					echo $d['user_id'] . "<br>";
					echo $d['user_name'] . "<br>";
					echo $d['user_type'] . "<br>";
				}
			}
		}
	}
}
