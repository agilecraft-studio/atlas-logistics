<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Maintenance & Work Orders hub + Fuel Management hub.
 *
 * Work orders gain the driver they were raised for and parts-used rows record
 * when their stock was returned, so archiving is idempotent and auditable.
 * Fuel gains a real vendor link and fuel-card accounts; the legacy free-text
 * `vendor_name` column stays for historical rows.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('work_orders') && !Schema::hasColumn('work_orders', 'driver_id')) {
            Schema::table('work_orders', function (Blueprint $t) {
                $t->unsignedInteger('driver_id')->nullable()->index();
            });
        }

        if (Schema::hasTable('parts_used') && !Schema::hasColumn('parts_used', 'restored_at')) {
            Schema::table('parts_used', function (Blueprint $t) {
                $t->timestamp('restored_at')->nullable();
            });
        }

        if (!Schema::hasTable('fuel_cards')) {
            Schema::create('fuel_cards', function (Blueprint $t) {
                $t->id();
                $t->string('label');
                $t->string('card_number')->unique();
                $t->unsignedInteger('vendor_id')->nullable()->index();
                $t->unsignedInteger('vehicle_id')->nullable()->index();
                $t->unsignedInteger('driver_id')->nullable()->index();
                $t->string('currency_code', 3)->default('MWK');
                $t->decimal('monthly_limit', 18, 2)->default(0);
                $t->decimal('balance', 18, 2)->default(0);
                $t->string('status', 20)->default('active')->index(); // active | suspended | expired
                $t->date('expires_on')->nullable();
                $t->text('note')->nullable();
                $t->unsignedBigInteger('created_by')->nullable();
                $t->timestamps();
                $t->softDeletes();
            });
        }

        if (Schema::hasTable('fuel')) {
            Schema::table('fuel', function (Blueprint $t) {
                if (!Schema::hasColumn('fuel', 'vendor_id')) {
                    $t->unsignedInteger('vendor_id')->nullable()->index();
                }
                if (!Schema::hasColumn('fuel', 'fuel_card_id')) {
                    $t->unsignedBigInteger('fuel_card_id')->nullable()->index();
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('fuel')) {
            Schema::table('fuel', function (Blueprint $t) {
                foreach (['vendor_id', 'fuel_card_id'] as $col) {
                    if (Schema::hasColumn('fuel', $col)) {
                        $t->dropColumn($col);
                    }
                }
            });
        }

        Schema::dropIfExists('fuel_cards');

        if (Schema::hasTable('parts_used') && Schema::hasColumn('parts_used', 'restored_at')) {
            Schema::table('parts_used', fn (Blueprint $t) => $t->dropColumn('restored_at'));
        }

        if (Schema::hasTable('work_orders') && Schema::hasColumn('work_orders', 'driver_id')) {
            Schema::table('work_orders', fn (Blueprint $t) => $t->dropColumn('driver_id'));
        }
    }
};
