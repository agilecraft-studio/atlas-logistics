<?php

/*
 |--------------------------------------------------------------------------
 | Atlas Logistics - cross-border fleet modules (systemDesign.md)
 |--------------------------------------------------------------------------
 | Loaded from routes/admin.php inside the authenticated "officeadmin" group, so every route here
 | is under /admin/logistics/... and already behind login. Each controller also enforces Spatie
 | permissions (see database/migrations/2026_10_11_000004_add_logistics_permissions.php).
 */

Route::prefix('logistics')->namespace('Logistics')->group(function () {

    // list / create / store / edit / update / destroy using {id} for the record
    $crud = function ($uri, $controller, $name, array $where = []) {
        $where = $where + ['id' => '[0-9]+'];
        Route::get($uri, "$controller@index")->name("$name.index")->where($where);
        Route::get("$uri/create", "$controller@create")->name("$name.create")->where($where);
        Route::post($uri, "$controller@store")->name("$name.store")->where($where);
        Route::get("$uri/{id}/edit", "$controller@edit")->name("$name.edit")->where($where);
        Route::put("$uri/{id}", "$controller@update")->name("$name.update")->where($where);
        Route::delete("$uri/{id}", "$controller@destroy")->name("$name.destroy")->where($where);
    };

    // Compliance
    Route::get('compliance', 'ComplianceDashboardController@index')->name('logistics.compliance');
    $crud('documents', 'ComplianceDocumentsController', 'logistics.documents');

    // Fleet register
    Route::get('register/vehicles', 'VehicleRegisterController@index')->name('logistics.register.vehicles');
    Route::get('register/vehicles/{id}/edit', 'VehicleRegisterController@edit')->name('logistics.register.vehicles.edit')->where('id', '[0-9]+');
    Route::put('register/vehicles/{id}', 'VehicleRegisterController@update')->name('logistics.register.vehicles.update')->where('id', '[0-9]+');
    Route::get('register/drivers', 'DriverRegisterController@index')->name('logistics.register.drivers');
    Route::get('register/drivers/{id}/edit', 'DriverRegisterController@edit')->name('logistics.register.drivers.edit')->where('id', '[0-9]+');
    Route::put('register/drivers/{id}', 'DriverRegisterController@update')->name('logistics.register.drivers.update')->where('id', '[0-9]+');
    $crud('trailers', 'TrailersController', 'logistics.trailers');
    $crud('components', 'VehicleComponentsController', 'logistics.components');

    // Master data (countries, currencies, exchange rates, border posts, expense categories, document types)
    $crud('master/{entity}', 'MasterDataController', 'logistics.master',
        ['entity' => 'countries|currencies|exchange-rates|border-posts|expense-categories|document-types']);
});
