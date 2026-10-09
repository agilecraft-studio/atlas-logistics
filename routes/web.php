<?php

/*
 * Internal admin application - no public/customer pages.
 * "/" redirects into the admin area. Authentication lives under /admin.
 */

Route::get('/', function () {
    return redirect('admin');
})->middleware(['IsInstalled'])->name('root');

// System installer / version upgrade (maintenance, kept per approved decision)
Route::get('installation', 'LaravelWebInstaller@index');
Route::post('installed', 'LaravelWebInstaller@install');
Route::get('installed', 'LaravelWebInstaller@index');
Route::get('migrate', 'LaravelWebInstaller@db_migration');
Route::get('migration', 'LaravelWebInstaller@migration');
Route::get('upgrade', 'UpdateVersion@upgrade')->middleware('canInstall');
Route::get('upgrade3', 'UpdateVersion@upgrade3')->middleware('canInstall');
Route::get('upgrade4', 'UpdateVersion@upgrade4')->middleware('canInstall');
Route::get('upgrade4.0.2', 'UpdateVersion@upgrade402')->middleware('canInstall');
Route::get('upgrade4.0.3', 'UpdateVersion@upgrade403')->middleware('canInstall');
Route::get('upgrade5', 'UpdateVersion@upgrade5')->middleware('canInstall');
Route::get('upgrade6', 'UpdateVersion@upgrade6')->middleware('canInstall');
Route::get('upgrade6.0.1', 'UpdateVersion@upgrade601')->middleware('canInstall');
Route::get('upgrade6.0.2', 'UpdateVersion@upgrade602')->middleware('canInstall');
Route::get('upgrade6.0.3', 'UpdateVersion@upgrade603')->middleware('canInstall');
Route::get('upgrade6.1', 'UpdateVersion@upgrade61')->middleware('canInstall');

// Payment gateway routes disconnected (public customer booking-checkout flow;
// PaymentController remains on disk for reference, no routes registered).
// Route::get('stripe/{booking_id}', 'PaymentController@stripe');
// Route::get('paystack/{booking_id}', 'PaymentController@paystack');
// Route::get('razorpay/{booking_id}', 'PaymentController@razorpay');
// Route::get('cash/{booking_id}', 'PaymentController@cash');

// Payment gateway callbacks / results (disconnected with the flow above)
// Route::get('stripe-success', 'PaymentController@stripe_success');
// Route::get('stripe-cancel', 'PaymentController@stripe_cancel');
// Route::get('paystack-success', 'PaymentController@paystack_callback');
// Route::post('razorpay-success', 'PaymentController@razorpay_success');
// Route::get('razorpay-failed', 'PaymentController@razorpay_failed');
// Route::get('transaction', 'PaymentController@transaction');
