<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Asset hubs for trucks, drivers and trailers.
 *
 * The legacy schema already carries vehicles, users (drivers), trailers, trips, work orders,
 * fuel, compliance documents and driver advances. This migration only adds the data that was
 * missing for the profile hubs: GPS pings, trailer pairings, driver incidents/finances/ratings
 * and trailer-aware maintenance. IDs that point at legacy int4 tables are indexed but not
 * hard-constrained, matching the convention used by the earlier fleet migrations.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('work_orders')) {
            Schema::table('work_orders', function (Blueprint $t) {
                if (!Schema::hasColumn('work_orders', 'trailer_id')) {
                    $t->unsignedInteger('trailer_id')->nullable()->index();
                }
                if (!Schema::hasColumn('work_orders', 'maintenance_type')) {
                    $t->string('maintenance_type', 30)->default('repair')->index();
                }
                if (!Schema::hasColumn('work_orders', 'completed_on')) {
                    $t->date('completed_on')->nullable();
                }
                if (!Schema::hasColumn('work_orders', 'resolution')) {
                    $t->text('resolution')->nullable();
                }
            });
        }

        if (Schema::hasTable('compliance_documents') && !Schema::hasColumn('compliance_documents', 'archived_at')) {
            Schema::table('compliance_documents', function (Blueprint $t) {
                $t->timestamp('archived_at')->nullable()->index();
            });
        }

        if (!Schema::hasTable('vehicle_telematics')) {
            Schema::create('vehicle_telematics', function (Blueprint $t) {
                $t->id();
                $t->unsignedInteger('vehicle_id')->index();
                $t->timestamp('recorded_at')->index();
                $t->decimal('latitude', 10, 7)->nullable();
                $t->decimal('longitude', 10, 7)->nullable();
                $t->decimal('speed_kph', 6, 2)->nullable();
                $t->decimal('odometer', 12, 1)->nullable();
                $t->decimal('fuel_level', 5, 2)->nullable();
                $t->boolean('ignition')->nullable();
                $t->unsignedSmallInteger('heading')->nullable();
                $t->string('source', 30)->nullable();
                $t->jsonb('raw')->nullable();
                $t->timestamps();
                $t->index(['vehicle_id', 'recorded_at']);
            });
        }

        if (!Schema::hasTable('trailer_assignments')) {
            Schema::create('trailer_assignments', function (Blueprint $t) {
                $t->id();
                $t->foreignId('trailer_id')->constrained('trailers')->cascadeOnDelete();
                $t->unsignedInteger('vehicle_id')->nullable()->index();
                $t->unsignedInteger('driver_id')->nullable()->index();
                $t->date('assigned_at');
                $t->date('released_at')->nullable();
                $t->decimal('odometer_out', 12, 1)->nullable();
                $t->decimal('odometer_in', 12, 1)->nullable();
                $t->text('notes')->nullable();
                $t->unsignedBigInteger('created_by')->nullable();
                $t->timestamps();
                $t->softDeletes();
                $t->index(['trailer_id', 'released_at']);
            });
        }

        if (!Schema::hasTable('driver_incidents')) {
            Schema::create('driver_incidents', function (Blueprint $t) {
                $t->id();
                $t->unsignedInteger('driver_id')->index();
                $t->foreignId('trip_id')->nullable()->constrained('trips')->nullOnDelete();
                $t->string('type', 30); // violation | accident | weighbridge | disciplinary | other
                $t->timestamp('occurred_at');
                $t->string('location')->nullable();
                $t->text('description')->nullable();
                $t->decimal('fine_amount', 18, 2)->nullable();
                $t->string('currency_code', 3)->default('MWK');
                $t->decimal('exchange_rate', 18, 6)->default(1);
                $t->decimal('fine_amount_base', 18, 2)->nullable();
                $t->unsignedSmallInteger('points')->nullable();
                $t->string('status', 20)->default('open')->index(); // open | under_review | resolved | contested
                $t->text('action_taken')->nullable();
                $t->timestamp('resolved_at')->nullable();
                $t->timestamp('archived_at')->nullable()->index();
                $t->unsignedBigInteger('created_by')->nullable();
                $t->timestamps();
            });
        }

        if (!Schema::hasTable('driver_payroll_profiles')) {
            Schema::create('driver_payroll_profiles', function (Blueprint $t) {
                $t->id();
                $t->unsignedInteger('user_id')->unique();
                $t->decimal('salary_amount', 18, 2)->nullable();
                $t->string('currency_code', 3)->default('MWK');
                $t->string('pay_frequency', 20)->default('monthly'); // monthly | fortnightly | weekly | per_trip
                $t->decimal('daily_allowance', 18, 2)->nullable();
                $t->decimal('fuel_allowance', 18, 2)->nullable();
                $t->string('bank_name')->nullable();
                $t->string('bank_account')->nullable();
                $t->text('notes')->nullable();
                $t->timestamps();
            });
        }

        if (!Schema::hasTable('driver_deductions')) {
            Schema::create('driver_deductions', function (Blueprint $t) {
                $t->id();
                $t->unsignedInteger('driver_id')->index();
                $t->string('type', 20); // loan | advance_recovery | fine | other
                $t->string('description');
                $t->decimal('amount', 18, 2);
                $t->string('currency_code', 3)->default('MWK');
                $t->decimal('exchange_rate', 18, 6)->default(1);
                $t->decimal('amount_base', 18, 2)->nullable();
                $t->decimal('balance', 18, 2)->nullable();
                $t->date('incurred_on');
                $t->string('status', 20)->default('active')->index(); // active | settled | waived
                $t->timestamp('archived_at')->nullable()->index();
                $t->unsignedBigInteger('created_by')->nullable();
                $t->timestamps();
            });
        }

        if (!Schema::hasTable('driver_ratings')) {
            Schema::create('driver_ratings', function (Blueprint $t) {
                $t->id();
                $t->unsignedInteger('driver_id')->index();
                $t->foreignId('trip_id')->nullable()->constrained('trips')->nullOnDelete();
                $t->unsignedBigInteger('customer_id')->nullable();
                $t->unsignedSmallInteger('rating'); // 1..5
                $t->text('feedback')->nullable();
                $t->date('rated_at');
                $t->unsignedBigInteger('created_by')->nullable();
                $t->timestamps();
                $t->index(['driver_id', 'rated_at']);
            });
        }

        $this->seedDocumentTypes();
    }

    /** Extra compliance types used by the hub filters. */
    private function seedDocumentTypes(): void
    {
        if (!Schema::hasTable('document_types')) {
            return;
        }

        $now = now();
        $types = [
            ['RTSA Certificate of Fitness', 'vehicle', true, 30],
            ['RTSA Road Safety Compliance', 'vehicle', true, 30],
            ['Safety Inspection Certificate', 'trailer', true, 30],
        ];

        foreach ($types as [$name, $appliesTo, $critical, $alert]) {
            if (!DB::table('document_types')->where('name', $name)->where('applies_to', $appliesTo)->exists()) {
                DB::table('document_types')->insert([
                    'name' => $name, 'applies_to' => $appliesTo, 'is_critical' => $critical,
                    'default_alert_days' => $alert, 'is_active' => true,
                    'created_at' => $now, 'updated_at' => $now,
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('driver_ratings');
        Schema::dropIfExists('driver_deductions');
        Schema::dropIfExists('driver_payroll_profiles');
        Schema::dropIfExists('driver_incidents');
        Schema::dropIfExists('trailer_assignments');
        Schema::dropIfExists('vehicle_telematics');

        if (Schema::hasColumn('compliance_documents', 'archived_at')) {
            Schema::table('compliance_documents', fn (Blueprint $t) => $t->dropColumn('archived_at'));
        }

        if (Schema::hasTable('work_orders')) {
            Schema::table('work_orders', function (Blueprint $t) {
                foreach (['trailer_id', 'maintenance_type', 'completed_on', 'resolution'] as $col) {
                    if (Schema::hasColumn('work_orders', $col)) {
                        $t->dropColumn($col);
                    }
                }
            });
        }
    }
};
