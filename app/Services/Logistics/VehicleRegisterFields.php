<?php

namespace App\Services\Logistics;

/**
 * Base-record (VehicleModel / vehicles) fields used by the canonical Fleet Register
 * create form. Kept intentionally minimal: plate, make/model and VIN are the
 * register's core identity; everything else stays in legacy Vehicles or in the
 * logistics profile. Explicitly mirrors legacy VehiclesController@store writes so
 * records are identical whichever screen created them (no data drift, no drift in
 * the DataTables pipeline).
 */
class VehicleRegisterFields
{
    /** @return array<int,array> */
    public static function all(): array
    {
        return [
            ['name' => 'license_plate', 'label' => 'Registration / plate', 'rules' => 'required|string|max:40|unique:vehicles,license_plate', 'col' => 4],
            ['name' => 'make_name', 'label' => 'Make', 'rules' => 'required|string|max:80', 'col' => 4],
            ['name' => 'model_name', 'label' => 'Model', 'rules' => 'required|string|max:80', 'col' => 4],
            ['name' => 'year', 'label' => 'Year', 'type' => 'number', 'rules' => 'nullable|integer|min:1950|max:' . (date('Y') + 1), 'col' => 4],
            ['name' => 'vin', 'label' => 'VIN / chassis', 'rules' => 'nullable|string|max:80', 'col' => 4],
            ['name' => 'color_name', 'label' => 'Colour', 'rules' => 'nullable|string|max:40', 'col' => 4],
            ['name' => 'engine_type', 'label' => 'Engine type', 'rules' => 'nullable|string|max:60', 'col' => 4],
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
