<?php



use Illuminate\Http\Request;

Route::namespace ('Api')->middleware(['throttle'])->group(function () {

	Route::post('vendor/vendor-login-mobile', 'VendorApiController@mobile_login');  //use

    Route::get('vendor/get-years', 'VendorApiController@years');//use

});



Route::middleware('auth:api')->post('/user', function (Request $request) {

	return $request->user();

});



Route::prefix('vendor')->namespace ('Api')->middleware(['throttle', 'auth:api'])->group(function () {

	//vehicle

	Route::post('vehicles', 'VendorApiController@vehicles');//use

	Route::post('delete-vehicle', 'VendorApiController@delete_vehicle');

	Route::post('add-vehicle', 'VendorApiController@add_vehicle');

	Route::post('edit-vehicle', 'VendorApiController@edit_vehicle');

	Route::post('vehicle-types', 'VendorApiController@types');//use



	Route::post('upload-documents', 'VendorApiController@vehicle_document'); //use

    Route::post('update-image', 'VendorApiController@update_vendor_image'); //use

    Route::post('upload-driver-documents', 'VendorApiController@driver_document'); //use

    Route::post('get-vendor', 'VendorApiController@get_vendor');//use

	//customers  APIs

    Route::post('customers', 'VendorApiController@customers');//use

    Route::post('add-customer','VendorApiController@StoreCustomer');//use

    Route::post('edit-customer','VendorApiController@EditCustomer');

    Route::post('delete-customer','VendorApiController@DeleteCustomer');//use



	Route::post('edit-profile', 'VendorApiController@edit_profile');//use vendor profile edit



	//Drivers

	Route::post('manage-drivers', 'VendorApiController@drivers'); //use

	Route::post('add-driver', 'VendorApiController@add_driver'); //use

	Route::post('edit-driver', 'VendorApiController@edit_driver'); //use

	Route::post('enable-disable-driver', 'VendorApiController@enable_disable_driver'); //use

	Route::post('delete-driver', 'VendorApiController@delete_driver'); //use



	Route::post('groups', 'VendorApiController@groups'); //use



	//income APIs

    Route::post('income', 'VendorApiController@income_list'); //use

    Route::post('income-types', 'VendorApiController@income_types'); //use

    Route::post('add-income', 'VendorApiController@add_income'); //use

    Route::post('edit-income', 'VendorApiController@edit_income'); //use

    Route::post('delete-income', 'VendorApiController@delete_income'); //use



    //expense APIs

    Route::post('expense', 'VendorApiController@expense_list'); //use

    Route::post('expense-types', 'VendorApiController@expense_types');//use

    Route::post('add-expense', 'VendorApiController@add_expense'); //use

    Route::post('delete-expense', 'VendorApiController@delete_expense'); //use

    Route::post('update-expense','VendorApiController@update_expense');//use



	 //Fuel

	 Route::post('fuel','FuelApiController@fuelList');//use

	 Route::post('add-fuel','FuelApiController@storeFuel'); //use

	 Route::post('delete-fuel','FuelApiController@deleteFuel'); //use

	 Route::post('update-fuel','FuelApiController@updateFuel'); //use 

	 Route::post('edit-fuel','FuelApiController@editFuel');//use

 

	 //service reminders APIs

	 Route::post('service-reminder','ServiceReminderApiController@serviceReminderList');//use

	 Route::post('add-service-reminder','ServiceReminderApiController@storeServiceReminder');//use

	 Route::post('delete-service-reminder','ServiceReminderApiController@deleteServiceReminder');//use

	 Route::post('service-items','ServiceReminderApiController@serviceItemList');//use

	 Route::post('update-service-reminder','ServiceReminderApiController@updateServiceReminder');//use

 

	 //vendors API for fuel module

	 Route::post('get-vendors','VendorApiController@getVendors');//use



	 //vehicle make model and colors

	 Route::Post('vehicle-model','VendorApiController@vehicle_model');//use

	 Route::post('vehicle-make','VendorApiController@vehicle_make');//use

	 Route::post('vehicle-colors','VendorApiController@vehicle_color');//use

	 Route::post('enable-disable-vehicle','VendorApiController@enable_disable_vehicle');//use



	  //settings api

	 Route::post('settings','VendorApiController@settings');

});

