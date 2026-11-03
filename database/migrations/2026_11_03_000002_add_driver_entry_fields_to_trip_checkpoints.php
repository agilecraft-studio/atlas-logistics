<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Driver checkpoints carry only what the driver can read from the truck: odometer and fuel gauge.
 * The timestamp is always server-generated. client_uuid makes weak-signal retries idempotent.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('trip_checkpoints')) {
            return;
        }

        Schema::table('trip_checkpoints', function (Blueprint $table) {
            if (!Schema::hasColumn('trip_checkpoints', 'kind')) {
                $table->string('kind', 20)->default('stop')->index();
            }
            if (!Schema::hasColumn('trip_checkpoints', 'odometer')) {
                $table->decimal('odometer', 12, 1)->nullable();
            }
            if (!Schema::hasColumn('trip_checkpoints', 'fuel_level')) {
                $table->string('fuel_level', 20)->nullable();
            }
            if (!Schema::hasColumn('trip_checkpoints', 'recorded_by')) {
                $table->unsignedBigInteger('recorded_by')->nullable();
            }
            if (!Schema::hasColumn('trip_checkpoints', 'client_uuid')) {
                $table->uuid('client_uuid')->nullable()->unique();
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('trip_checkpoints')) {
            return;
        }

        Schema::table('trip_checkpoints', function (Blueprint $table) {
            foreach (['client_uuid', 'recorded_by', 'fuel_level', 'odometer', 'kind'] as $column) {
                if (Schema::hasColumn('trip_checkpoints', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
