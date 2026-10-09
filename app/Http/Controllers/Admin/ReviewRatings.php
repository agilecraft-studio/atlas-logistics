<?php
/*
@copyright
Fleet Manager v7.0
Copyright (C) 2017-2025 Atlas Logistics <https://app.atlaslogistics.co.mw> All rights reserved.
Design and developed by Atlas Logistics <https://app.atlaslogistics.co.mw>
 */
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Model\ReviewModel;

class ReviewRatings extends Controller {
	public function index() {
		$data['reviews'] = ReviewModel::orderBy('id', 'desc')->get();
		return view('reviews', $data);
	}
}
