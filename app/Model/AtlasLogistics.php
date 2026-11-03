<?php
/*
@copyright

Fleet Manager v7.0

Copyright (C) 2017-2023 Atlas Logistics <https://app.atlaslogistics.co.mw> All rights reserved.
Design and developed by Atlas Logistics <https://app.atlaslogistics.co.mw>

 */

namespace App\Model;

use App\Model\ApiSettings;
use App\Model\EmailContent;
use App\Model\Settings;
use Illuminate\Support\Arr;

class AtlasLogistics {

	public static function get($key) {
		$settings = array_pluck(Settings::all()->toArray(), 'value', 'name');
		return (is_array($key))?Arr::only($settings, $key): ($settings[$key] ?? null);
	}

	public static function set($key, $val) {
		$settings = Settings::firstOrNew(array('name' => $key));
		$settings->value = $val;
		$settings->save();
		Cache::flush();
	}

	public static function api($key) {
		$settings = array_pluck(ApiSettings::all()->toArray(), 'key_value', 'key_name');
		return (is_array($key))?Arr::only($settings, $key): ($settings[$key] ?? null);
	}

	public static function email_msg($key) {
		$settings = array_pluck(EmailContent::all()->toArray(), 'value', 'key');
		return (is_array($key))?Arr::only($settings, $key): ($settings[$key] ?? null);
	}
}
