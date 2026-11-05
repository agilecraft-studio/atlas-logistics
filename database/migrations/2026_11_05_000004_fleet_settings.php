<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $settings = [
        ['name' => 'compliance_alert_days', 'label' => 'Compliance expiry warning (days)', 'value' => '30'],
        ['name' => 'driver_licence_alert_days', 'label' => 'Driver licence expiry warning (days)', 'value' => '30'],
        ['name' => 'driver_medical_alert_days', 'label' => 'Driver medical expiry warning (days)', 'value' => '30'],
        ['name' => 'trailer_utilization_days', 'label' => 'Trailer utilisation window (days)', 'value' => '90'],
    ];

    public function up(): void
    {
        foreach ($this->settings as $row) {
            $existing = DB::table('settings')->where('name', $row['name'])->first();
            if ($existing) {
                DB::table('settings')->where('id', $existing->id)->update(['deleted_at' => null, 'updated_at' => now()]);
                continue;
            }

            DB::table('settings')->insert($row + ['created_at' => now(), 'updated_at' => now()]);
        }
    }

    public function down(): void
    {
        DB::table('settings')->whereIn('name', array_column($this->settings, 'name'))->delete();
    }
};
