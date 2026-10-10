<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Atlas Logistics - fleet & driver compliance (systemDesign.md sections 7.2, 7.3, 7.12).
 * vehicles and users (drivers) already exist in the legacy schema; they are referenced by indexed
 * id columns (no hard foreign keys to legacy tables, to avoid integer/bigint type clashes).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('trailers')) {
            Schema::create('trailers', function (Blueprint $t) {
                $t->id();
                $t->string('registration_number')->unique();
                $t->string('chassis_number')->nullable();
                $t->string('trailer_type')->nullable();
                $t->string('make')->nullable();
                $t->string('model')->nullable();
                $t->unsignedSmallInteger('year')->nullable();
                $t->string('axle_configuration')->nullable();
                $t->unsignedInteger('payload_capacity_kg')->nullable();
                $t->string('status', 20)->default('available');
                $t->string('depot')->nullable();
                $t->text('notes')->nullable();
                $t->timestamps();
                $t->softDeletes();
            });
        }

        // Extra logistics data for an existing vehicle (1 row per vehicle).
        if (!Schema::hasTable('vehicle_logistics_profiles')) {
            Schema::create('vehicle_logistics_profiles', function (Blueprint $t) {
                $t->id();
                $t->unsignedBigInteger('vehicle_id')->unique();
                $t->string('engine_number')->nullable();
                $t->string('chassis_number')->nullable();
                $t->string('axle_configuration')->nullable();
                $t->decimal('fuel_tank_capacity_l', 8, 1)->nullable();
                $t->string('ownership_type', 20)->nullable();
                $t->string('depot')->nullable();
                $t->string('logistics_status', 20)->default('available');
                $t->decimal('last_service_odometer', 12, 1)->nullable();
                $t->decimal('next_service_odometer', 12, 1)->nullable();
                $t->date('next_service_date')->nullable();
                $t->text('notes')->nullable();
                $t->timestamps();
            });
        }

        // Driver compliance: licence / passport / medical dates and permitted countries.
        if (!Schema::hasTable('driver_compliance_profiles')) {
            Schema::create('driver_compliance_profiles', function (Blueprint $t) {
                $t->id();
                $t->unsignedBigInteger('user_id')->unique();
                $t->string('employee_number')->nullable();
                $t->string('national_id')->nullable();
                $t->string('passport_number')->nullable();
                $t->date('passport_expiry')->nullable();
                $t->string('licence_number')->nullable();
                $t->string('licence_class', 20)->nullable();
                $t->date('licence_expiry')->nullable();
                $t->date('yellow_fever_expiry')->nullable();
                $t->date('medical_expiry')->nullable();
                $t->jsonb('approved_country_ids')->nullable();
                $t->string('home_depot')->nullable();
                $t->string('driver_status', 20)->default('active');
                $t->string('emergency_contact_name')->nullable();
                $t->string('emergency_contact_phone')->nullable();
                $t->string('payment_details')->nullable(); // bank / mobile money
                $t->text('notes')->nullable();
                $t->timestamps();
            });
        }

        // Generic documents with expiry for vehicles, trailers and drivers.
        if (!Schema::hasTable('compliance_documents')) {
            Schema::create('compliance_documents', function (Blueprint $t) {
                $t->id();
                $t->string('owner_type', 10); // vehicle | trailer | driver
                $t->unsignedBigInteger('owner_id');
                $t->foreignId('document_type_id')->constrained('document_types');
                $t->string('document_number')->nullable();
                $t->date('issue_date')->nullable();
                $t->date('expiry_date')->nullable();
                $t->unsignedSmallInteger('alert_days')->nullable();
                $t->string('file_path')->nullable();
                $t->text('notes')->nullable();
                $t->timestamps();
                $t->index(['owner_type', 'owner_id']);
                $t->index('expiry_date');
            });
        }

        // Tyres, batteries and other serial-numbered items fitted to a truck or trailer.
        if (!Schema::hasTable('vehicle_components')) {
            Schema::create('vehicle_components', function (Blueprint $t) {
                $t->id();
                $t->string('owner_type', 10); // vehicle | trailer
                $t->unsignedBigInteger('owner_id');
                $t->string('component_type', 30); // tyre, battery, spare_wheel, ...
                $t->string('serial_number')->nullable();
                $t->string('brand')->nullable();
                $t->string('position')->nullable(); // e.g. "Steer L", "Drive 2 R"
                $t->string('status', 20)->default('fitted');
                $t->date('fitted_date')->nullable();
                $t->decimal('fitted_odometer', 12, 1)->nullable();
                $t->date('removed_date')->nullable();
                $t->decimal('removed_odometer', 12, 1)->nullable();
                $t->decimal('cost', 14, 2)->nullable();
                $t->string('supplier')->nullable();
                $t->text('notes')->nullable();
                $t->timestamps();
                $t->index(['owner_type', 'owner_id']);
                $t->index('serial_number');
            });
        }
    }

    public function down(): void
    {
        foreach (['vehicle_components', 'compliance_documents', 'driver_compliance_profiles', 'vehicle_logistics_profiles', 'trailers'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
