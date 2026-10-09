<?php

/*
|--------------------------------------------------------------------------
| Legacy global helper compatibility
|--------------------------------------------------------------------------
|
| Laravel dropped these global helpers when laravel/helpers was retired.
| They are restored here (guarded) so existing application and Blade code
| keeps working unchanged under Laravel 13.
|
*/

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

if (! function_exists('str_random')) {
    function str_random($length = 16)
    {
        return Str::random($length);
    }
}

if (! function_exists('array_pluck')) {
    function array_pluck($array, $value, $key = null)
    {
        return Arr::pluck($array, $value, $key);
    }
}

if (! function_exists('array_only')) {
    function array_only($array, $keys)
    {
        return Arr::only($array, $keys);
    }
}

if (! function_exists('starts_with')) {
    function starts_with($haystack, $needles)
    {
        return Str::startsWith($haystack, $needles);
    }
}
