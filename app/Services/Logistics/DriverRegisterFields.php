<?php

namespace App\Services\Logistics;

/**
 * Base-record (User / users where user_type=D) fields used by the canonical Driver
 * Register create form. Minimal identity + contact; licence/passport/medical dates
 * live in the compliance profile (DriverRegisterController::fields()). Mirrors legacy
 * DriversController@store so a driver created here is indistinguishable from one
 * created in legacy (same user_type, hashed password, random api_token, default
 * driver permissions) — no drift, no duplicate rows.
 */
class DriverRegisterFields
{
    /** @return array<int,array> */
    public static function all(): array
    {
        return [
            ['name' => 'first_name', 'label' => 'First name', 'rules' => 'required|string|max:80', 'col' => 4],
            ['name' => 'last_name', 'label' => 'Last name', 'rules' => 'required|string|max:80', 'col' => 4],
            ['name' => 'email', 'label' => 'Email', 'type' => 'email', 'rules' => 'required|email|max:120|unique:users,email', 'col' => 4],
            ['name' => 'phone', 'label' => 'Phone', 'type' => 'tel', 'rules' => 'required|digits_between:7,15', 'col' => 4],
            ['name' => 'password', 'label' => 'Password', 'type' => 'password', 'rules' => 'required|string|min:8|confirmed', 'col' => 4],
            ['name' => 'password_confirmation', 'label' => 'Confirm password', 'type' => 'password', 'rules' => 'required', 'col' => 4],
        ];
    }

    /** @return array<string,string> */
    public static function rules(): array
    {
        $rules = [];
        foreach (self::all() as $f) {
            $rules[$f['name']] = $f['rules'];
        }
        return $rules;
    }
}
