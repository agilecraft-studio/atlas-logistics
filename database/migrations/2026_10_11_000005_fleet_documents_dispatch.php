<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trailers', function (Blueprint $t) {
            $t->string('title_holder')->nullable();
            $t->string('register_number')->nullable();
            $t->unsignedInteger('tare_kg')->nullable();
            $t->unsignedInteger('gvm_kg')->nullable();
            $t->date('first_registration')->nullable();
        });
        Schema::table('vehicle_logistics_profiles', function (Blueprint $t) {
            $t->string('title_holder')->nullable();
            $t->string('register_number')->nullable();
            $t->unsignedInteger('tare_kg')->nullable();
            $t->unsignedInteger('gvm_kg')->nullable();
            $t->date('first_registration')->nullable();
        });
        Schema::table('compliance_documents', function (Blueprint $t) {
            $t->jsonb('coverage_country_ids')->nullable();
            $t->date('first_registration_date')->nullable();
        });
        $now = now();
        DB::table('document_types')->where('name', 'Registration Book')
            ->update(['name' => 'Motor Vehicle Registration Certificate', 'updated_at' => $now]);
        DB::table('document_types')->where('name', 'Cross-Border Permit')
            ->update(['name' => 'Cross-Border Road Service Permit', 'default_alert_days' => 45, 'updated_at' => $now]);
        $add = function (string $name, string $appliesTo, bool $critical, int $alert) use ($now) {
            if (! DB::table('document_types')->where('name', $name)->where('applies_to', $appliesTo)->exists()) {
                DB::table('document_types')->insert([
                    'name' => $name, 'applies_to' => $appliesTo, 'is_critical' => $critical,
                    'default_alert_days' => $alert, 'is_active' => true,
                    'created_at' => $now, 'updated_at' => $now,
                ]);
            }
        };
        $add('Cross-Border Road Service Permit', 'trailer', true, 45);
        $add('Trade Entity Certificate', 'company', true, 60);
        $add('Traffic Register Certificate', 'company', false, 60);
    }

    public function down(): void
    {
        $now = now();
        DB::table('document_types')->where('name', 'Motor Vehicle Registration Certificate')
            ->update(['name' => 'Registration Book', 'updated_at' => $now]);
        DB::table('document_types')->where('name', 'Cross-Border Road Service Permit')
            ->where('applies_to', 'vehicle')
            ->update(['name' => 'Cross-Border Permit', 'default_alert_days' => 30, 'updated_at' => $now]);
        DB::table('document_types')->where('name', 'Cross-Border Road Service Permit')->where('applies_to', 'trailer')->delete();
        DB::table('document_types')->where('applies_to', 'company')->delete();
        Schema::table('compliance_documents', function (Blueprint $t) {
            $t->dropColumn(['coverage_country_ids', 'first_registration_date']);
        });
        Schema::table('vehicle_logistics_profiles', function (Blueprint $t) {
            $t->dropColumn(['title_holder', 'register_number', 'tare_kg', 'gvm_kg', 'first_registration']);
        });
        Schema::table('trailers', function (Blueprint $t) {
            $t->dropColumn(['title_holder', 'register_number', 'tare_kg', 'gvm_kg', 'first_registration']);
        });
    }
};
