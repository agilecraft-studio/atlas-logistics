<?php

/*
 * Internal admin application - no public/customer pages.
 * "/" redirects into the admin area. Authentication lives under /admin.
 * NOTE: the legacy web installer + version-upgrade controllers were hard
 * removed; all installation/upgrade/migration HTTP endpoints are gone.
 * Database work runs via CLI migrations only (`php artisan migrate --force`).
 */

Route::get('/', function () {
    return redirect('admin');
})->name('root');

// System installer / version upgrade HTTP endpoints fully removed.

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
