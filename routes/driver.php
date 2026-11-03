<?php

/*
 |--------------------------------------------------------------------------
 | Driver mobile checkpoint capture
 |--------------------------------------------------------------------------
 | Deliberately tiny: the driver picks a trip, enters odometer + fuel gauge,
 | and saves. Departure starts the trip; arrival marks it delivered.
 */

Route::get('/', 'Driver\DriverTripController@index')->name('driver.index');
Route::get('trips/{trip}', 'Driver\DriverTripController@show')->name('driver.trips.show');
Route::post('trips/{trip}/checkpoint', 'Driver\DriverTripController@store')->name('driver.checkpoints.store');
