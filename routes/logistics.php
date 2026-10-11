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

    // ---- Phase 3: Trips / expenses / advances / reports ----
    // NOTE: this group has NO name prefix, so every name is written out in full (logistics.*).
    // Record param is {id}; nested row param is {rowId}; trip-scoped creates use {tripId}.
    $crud('trips', 'TripsController', 'logistics.trips');
    Route::get('trips/{id}', 'TripsController@show')->name('logistics.trips.show')->where('id', '[0-9]+');
    Route::post('trips/{id}/start', 'TripsController@start')->name('logistics.trips.start')->where('id', '[0-9]+');
    Route::post('trips/{id}/complete', 'TripsController@complete')->name('logistics.trips.complete')->where('id', '[0-9]+');
    Route::post('trips/{id}/close', 'TripsController@close')->name('logistics.trips.close')->where('id', '[0-9]+');
    Route::post('trips/{id}/cancel', 'TripsController@cancel')->name('logistics.trips.cancel')->where('id', '[0-9]+');
    Route::post('trips/{id}/checkpoints', 'TripsController@addCheckpoint')->name('logistics.trips.checkpoints.store')->where('id', '[0-9]+');
    Route::delete('trips/{id}/checkpoints/{rowId}', 'TripsController@deleteCheckpoint')->name('logistics.trips.checkpoints.destroy')->where(['id' => '[0-9]+', 'rowId' => '[0-9]+']);
    Route::post('trips/{id}/incidents', 'TripsController@addIncident')->name('logistics.trips.incidents.store')->where('id', '[0-9]+');
    Route::delete('trips/{id}/incidents/{rowId}', 'TripsController@deleteIncident')->name('logistics.trips.incidents.destroy')->where(['id' => '[0-9]+', 'rowId' => '[0-9]+']);

    Route::get('expenses', 'TripExpensesController@index')->name('logistics.expenses.index');
    Route::post('trips/{tripId}/expenses', 'TripExpensesController@store')->name('logistics.expenses.store')->where('tripId', '[0-9]+');
    Route::put('expenses/{id}/approve', 'TripExpensesController@approve')->name('logistics.expenses.approve')->where('id', '[0-9]+');
    Route::put('expenses/{id}/reject', 'TripExpensesController@reject')->name('logistics.expenses.reject')->where('id', '[0-9]+');
    Route::delete('expenses/{id}', 'TripExpensesController@destroy')->name('logistics.expenses.destroy')->where('id', '[0-9]+');

    Route::get('advances', 'DriverAdvancesController@index')->name('logistics.advances.index');
    Route::post('trips/{tripId}/advances', 'DriverAdvancesController@store')->name('logistics.advances.store')->where('tripId', '[0-9]+');
    Route::delete('advances/{id}', 'DriverAdvancesController@destroy')->name('logistics.advances.destroy')->where('id', '[0-9]+');

    Route::get('reports/profitability', 'TripReportsController@profitability')->name('logistics.reports.profitability');
});
