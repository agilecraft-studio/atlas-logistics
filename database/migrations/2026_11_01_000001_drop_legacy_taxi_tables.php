<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Passenger/taxi tables inherited from the original taxi application.
     * The logistics fleet uses trips, trip_expenses and its own master data.
     */
    private array $tables = [
        'addresses',
        'booking_alerts',
        'booking_income',
        'booking_payments',
        'booking_quotation',
        'bookings',
        'bookings_meta',
        'chat_settings',
        'cities',
        'company_services',
        'driver_alert',
        'driver_payments',
        'fare_settings',
        'frontend',
        'message',
        'messages',
        'payment_settings',
        'reasons',
        'reviews',
        'testimonials',
        'twilio_settings',
        'vehicle_breakdown',
    ];

    private array $permissionPrefixes = [
        'BookingQuotations',
        'Bookings',
        'DriverAlert',
        'Inquiries',
        'Testimonials',
        'VehicleBreakdown',
    ];

    public function up(): void
    {
        $this->removeTaxiPermissions();
        $this->removeTaxiSettings();

        foreach ($this->tables as $table) {
            Schema::dropIfExists($table);
        }
    }

    public function down(): void
    {
        // Legacy taxi tables were dump-defined and are intentionally not recreated.
    }

    private function removeTaxiPermissions(): void
    {
        if (!Schema::hasTable('permissions')) {
            return;
        }

        $ids = DB::table('permissions')
            ->where(function ($query) {
                foreach ($this->permissionPrefixes as $prefix) {
                    $query->orWhere('name', 'like', $prefix . ' %');
                }
            })
            ->pluck('id');

        if ($ids->isEmpty()) {
            return;
        }

        if (Schema::hasTable('role_has_permissions')) {
            DB::table('role_has_permissions')->whereIn('permission_id', $ids)->delete();
        }
        if (Schema::hasTable('model_has_permissions')) {
            DB::table('model_has_permissions')->whereIn('permission_id', $ids)->delete();
        }

        DB::table('permissions')->whereIn('id', $ids)->delete();
    }

    private function removeTaxiSettings(): void
    {
        if (Schema::hasTable('settings')) {
            DB::table('settings')
                ->whereIn('name', ['web_footer', 'fotter_logo_img', 'fare_mode', 'return_booking'])
                ->delete();
        }

        if (Schema::hasTable('api_settings')) {
            DB::table('api_settings')
                ->whereIn('key_name', ['region_availability', 'driver_review', 'booking', 'cancel', 'max_trip', 'api_key', 'google_api'])
                ->delete();
        }
    }
};
