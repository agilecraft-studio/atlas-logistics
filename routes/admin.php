<?php
Auth::routes(['register' => false]);
Route::namespace ('Admin')->group(function () {
    // Public test-login disconnected (auto-login as user 1; controller remains on disk).
    // Route::get('test-login', 'Testcontroller@login');

    Route::post("logout", 'HomeController@logout')->name('logout');

    


    // Route::get('add-permission', function () {
    //     $drivers = App\Model\User::where('user_type', 'D')->get();
    //     foreach ($drivers as $driver) {
    //         $d = App\Model\User::find($driver->id);
    //         $d->givePermissionTo(['VehicleInspection add']);
    //         //$d->givePermissionTo(['Transactions list','Transactions add','Transactions edit','Transactions delete']);
    //     }
    // });
// dd('test');
    Route::get("/", 'HomeController@index')->middleware(['lang_check', 'auth']);
    Route::group(['middleware' => ['lang_check', 'auth', 'officeadmin']], function () {




        Route::get('/drivers/verify-driver/{id}', 'DriversController@verify_driver');
        Route::post('/drivers/update_verify_driver', 'DriversController@update_verify_driver')->name('update_verify_driver');
        
 
       

        Route::get('get-models/{id}', 'VehiclesController@get_models');

        // Route::post('vehicle-color-fetch', 'VehicleColorsController@fetch_data');
        // Route::resource('vehicle-color', 'VehicleColorsController');
        // Route::post('delete-vehicle-color', 'VehicleColorsController@bulk_delete');

        // Route::post('vehicle-make-fetch', 'VehicleMakeController@fetch_data');
        // Route::resource('vehicle-make', 'VehicleMakeController');
        // Route::post('delete-vehicle-make', 'VehicleMakeController@bulk_delete');

        // Route::post('vehicle-model-fetch', 'VehicleModelController@fetch_data');
        // Route::resource('vehicle-model', 'VehicleModelController');
        // Route::post('delete-vehicle-model', 'VehicleModelController@bulk_delete');


        Route::resource('roles', 'UserAccessController')->except(['show']);

        Route::post('/users-fetch', 'UsersController@fetch_data');
        Route::resource('/users', 'UsersController')->except(['show']);

        Route::post('clear-database', 'SettingsController@clear_database');
        Route::get('parts-used/{id}', 'WorkOrdersController@parts_used');
        Route::get('remove-part/{id}', 'WorkOrdersController@remove_part');
        Route::post('add-stock', 'PartsController@add_stock');
        Route::post('import-vehicles', 'VehiclesController@importVehicles');
        Route::post('import-drivers', 'DriversController@importDrivers');
        Route::post('import-income', 'IncomeCategories@importIncome');
        Route::post('import-expense', 'ExpenseCategories@importExpense');
        Route::post('import-customers', 'CustomersController@importCutomers');
        Route::post('import-vendors', 'VendorController@importVendors');
        // routes for bulk delete action
        Route::post('delete-reminders', 'ServiceReminderController@bulk_delete');
        Route::post('delete-service-items', 'ServiceItemsController@bulk_delete');
        Route::post('delete-parts', 'PartsController@bulk_delete');
        Route::post('delete-work-orders', 'WorkOrdersController@bulk_delete');
        Route::post('delete-parts-category', 'PartsCategoryController@bulk_delete');
        Route::post('delete-customer', 'CustomersController@bulk_delete');
        Route::post('delete-users', 'UsersController@bulk_delete');
        Route::post('delete-drivers', 'DriversController@bulk_delete');
        Route::post('delete-vehicles', 'VehiclesController@bulk_delete');
        // Route::post('delete-fuel', 'FuelController@bulk_delete');
        Route::post('delete-vendors', 'VendorController@bulk_delete');
        Route::post('delete-vehicle-types', 'VehicleTypeController@bulk_delete');
        Route::post('delete-vehicle-groups', 'VehicleGroupController@bulk_delete');
        Route::post('delete-vehicle-reviews', 'VehiclesController@bulk_delete_reviews');

        Route::get('reports/income', 'ReportsController@income');
        Route::post('reports/income', 'ReportsController@income_post');
        Route::post('print-income', 'ReportsController@income_print');

        Route::get('reports/expense', 'ReportsController@expense');
        Route::post('reports/expense', 'ReportsController@expense_post');
        Route::post('print-expense', 'ReportsController@expense_print');

        Route::get('work_order/logs', 'WorkOrdersController@logs');
        Route::resource('parts-category', 'PartsCategoryController')->except(['show']);

        Route::post('driver-logs-fetch', 'VehiclesController@driver_logs_fetch_data');
        Route::get('driver-logs', 'VehiclesController@driver_logs');
        Route::post('/vehicle-types-fetch', 'VehicleTypeController@fetch_data');
        Route::resource('/vehicle-types', 'VehicleTypeController')->except(['show']);

        Route::post('vehicle-reviews-fetch', 'VehiclesController@vehicle_review_fetch_data');
        Route::get('vehicle-reviews', 'VehiclesController@vehicle_review_index')->name('vehicle_reviews');
        Route::get('print-vehicle-review/{id}', 'VehiclesController@print_vehicle_review');
        Route::get('view-vehicle-review/{id}', 'VehiclesController@view_vehicle_review');
        Route::get('vehicle-reviews-create', 'VehiclesController@vehicle_review');
        Route::get('vehicle-review/{id}/edit', 'VehiclesController@review_edit');
        Route::post('vehicle-review-update', 'VehiclesController@update_vehicle_review');
        Route::delete('delete-vehicle-review/{id}', 'VehiclesController@destroy_vehicle_review');
        Route::get('print-vendor-report', 'ReportsController@print_vendor');
        Route::post('print-yearly-report', 'ReportsController@print_yearly');
        Route::post('print-fuel-report', 'ReportsController@print_fuel');
        Route::post('print-deliquent-report', 'ReportsController@print_deliquent');
        Route::post('print-monthly-report', 'ReportsController@print_monthly');

        Route::get('api-settings', 'SettingsController@api_settings');
        Route::post('api-settings', 'SettingsController@store_settings');

        // Route::get('fb', 'SettingsController@fb_create')->name('fb');
        Route::post('firebase-settings', 'SettingsController@firebase');
        Route::post('store-key', 'SettingsController@store_key');
        Route::get('test-key', 'SettingsController@test_key');
        Route::resource('service-reminder', 'ServiceReminderController')->only(['index', 'create', 'store', 'destroy']);
        Route::resource('service-item', 'ServiceItemsController')->except(['show']);
        Route::post('/vehicle-group-fetch', 'VehicleGroupController@fetch_data');
        Route::resource('/vehicle_group', 'VehicleGroupController')->except(['show']);
        Route::post('/store_insurance', 'VehiclesController@store_insurance');
        Route::get('vehicle/event/{id}', 'VehiclesController@view_event');
        Route::post('assignDriver', 'VehiclesController@assign_driver');

        Route::post('/work-orders-fetch', 'WorkOrdersController@fetch_data');
        Route::resource('/work_order', 'WorkOrdersController')->except(['show']);
        Route::post('/vendors-fetch', 'VendorController@fetch_data');
        Route::resource('/vendors', 'VendorController')->except(['show']);
        Route::post('drivers-fetch', 'DriversController@fetch_data');
        Route::resource('/drivers', 'DriversController')->except(['show']);
        Route::resource('/parts', 'PartsController')->except(['show']);
        Route::post('/vehicles-fetch', 'VehiclesController@fetch_data');
        Route::resource('/vehicles', 'VehiclesController')->except(['show']);
        Route::get("/vehicles/enable/{id}", 'VehiclesController@enable');
        Route::get("/vehicles/disable/{id}", 'VehiclesController@disable');
        Route::resource('/income', 'IncomeController')->only(['index', 'store', 'destroy']);
        Route::get('/settings', 'SettingsController@index')->name('settings.index');
        Route::post('/settings', 'SettingsController@store')->name('settings.store');
        Route::post('/customers-fetch', 'CustomersController@fetch_data');
        Route::resource('/customers', 'CustomersController');

        // Customer document hub: delivery notes, dispatch sheets, waybills, quotes and PoDs.
        Route::prefix('customers/{customer}/documents')->name('customers.documents.')
            ->whereNumber('customer')->group(function () {
                Route::get('create', 'CustomerDocumentsController@create')->name('create');
                Route::post('/', 'CustomerDocumentsController@store')->name('store');
                Route::get('{document}', 'CustomerDocumentsController@show')->name('show')->whereNumber('document');
                Route::get('{document}/edit', 'CustomerDocumentsController@edit')->name('edit')->whereNumber('document');
                Route::put('{document}', 'CustomerDocumentsController@update')->name('update')->whereNumber('document');
                Route::delete('{document}', 'CustomerDocumentsController@destroy')->name('destroy')->whereNumber('document');
                Route::get('{document}/download', 'CustomerDocumentsController@download')->name('download')->whereNumber('document');
                Route::get('{document}/print', 'CustomerDocumentsController@print')->name('print')->whereNumber('document');
                Route::post('{document}/email', 'CustomerDocumentsController@email')->name('email')->whereNumber('document');
            });
        Route::resource('/expense', 'ExpenseController')->only(['index', 'store', 'destroy']);
        Route::resource('/expensecategories', 'ExpenseCategories')->except(['show']);
        Route::resource('/incomecategories', 'IncomeCategories')->except(['show']);
        Route::get("/reports/monthly", "ReportsController@monthly")->name("reports.monthly");
        Route::get("/reports/vendors", "ReportsController@vendors")->name("reports.vendors");
        Route::post("/reports/vendors", "ReportsController@vendors_post");
        Route::get("/reports/delinquent", "ReportsController@delinquent")->name("reports.delinquent");
        Route::get("/drivers/enable/{id}", 'DriversController@enable');
        Route::get("/drivers/disable/{id}", 'DriversController@disable');
        Route::post("/reports/fuel", "ReportsController@fuel_post");
        Route::get("/reports/fuel", "ReportsController@fuel")->name("reports.fuel");
        Route::get("/reports/yearly", "ReportsController@yearly")->name("reports.yearly");
        Route::post("/reports/yearly", "ReportsController@yearly_post");

        Route::post('/customer/ajax_save', 'CustomersController@ajax_store')->name('customers.ajax_store');
        Route::post("/reports/monthly", "ReportsController@monthly_post");
        Route::post("/reports/delinquent", "ReportsController@delinquent_post");
        Route::get("/send-email", "SettingsController@send_email");
        Route::post("/email-settings", "SettingsController@email_settings");
        Route::post('enable-mail', 'SettingsController@enable_mail');
        Route::get("/set-email", "SettingsController@set_email");
        Route::post("/set-content/{type}", "SettingsController@set_content");

        // Atlas Logistics cross-border modules (trips, compliance, fleet register, master data)
        Route::middleware('internal.staff')->group(function () {
            require base_path('routes/logistics.php');
        });

    });

    Route::group(['middleware' => ['lang_check', 'auth']], function () {

        Route::get('changepassword/{id}', 'UtilityController@change');
        Route::post('changepassword/{id}', 'UtilityController@change_post');
        Route::get('/change-details/{id}', 'UtilityController@changepass')->name("changepass");
        Route::post('/change-details/{id}', 'UtilityController@changepassword');
        Route::post('/change_password', 'UtilityController@password_change');
        Route::get('/vehicle_notification/{type}', 'NotificationController@vehicle_notification');
        Route::get('driver_notification/{type}', 'NotificationController@driver_notification');
        Route::get('/reminder/{type}', 'NotificationController@service_reminder');

//report work order
        Route::get("/reports/work-order", "ReportsController@work_order")->name("reports.work_order");
        Route::post("/reports/work-order", "ReportsController@work_order_post");
        Route::post('print-workOrder-report', 'ReportsController@print_workOrder_report');
//mechanics  CRUD
        Route::resource('mechanic', 'MechanicController')->except(['show']);
        Route::post("/delete-mechanics", "MechanicController@bulk_delete");
// vehicle inspection
        Route::get('vehicle-inspection', 'VehiclesController@vehicle_inspection_index')->name('vehicle_inspection');
        Route::get('print-vehicle-inspection/{id}', 'VehiclesController@print_vehicle_inspection');
        Route::get('vehicle-inspection-create', 'VehiclesController@vehicle_inspection_create');
        Route::post('store-vehicle-review', 'VehiclesController@store_vehicle_review');
        Route::get('view-vehicle-inspection/{id}', 'VehiclesController@view_vehicle_inspection');
// fuel detail
        Route::resource('/fuel', 'FuelController')->except(['show']);
//vehicle Expense
        Route::post('/expense_records', 'ExpenseController@expense_records');
        Route::post('delete-expense', 'ExpenseController@bulk_delete');
//vehicle income
        Route::post('delete-income', 'IncomeController@bulk_delete');
        Route::post('/income_records', 'IncomeController@income_records');
// driver reports
        Route::get("/driver-reports/yearly", "DriversController@yearly")->name("dreports.yearly");
        Route::post("/driver-reports/yearly", "DriversController@yearly_post");
        Route::get("/driver-reports/monthly", "DriversController@monthly")->name("dreports.monthly");
        Route::post("/driver-reports/monthly", "DriversController@monthly_post");

        Route::post('delete-fuel', 'FuelController@bulk_delete');

    });

});
