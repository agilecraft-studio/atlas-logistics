<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Atlas Logistics - trips, border checkpoints, expenses, driver advances (allowances), incidents
 * (systemDesign.md sections 7.5, 7.9, 7.10). Money is numeric; every amount keeps its original
 * currency, the exchange rate used, and the converted base-currency amount.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('trips')) {
            Schema::create('trips', function (Blueprint $t) {
                $t->id();
                $t->string('trip_number')->unique();
                $t->unsignedBigInteger('customer_id')->nullable()->index();
                $t->unsignedBigInteger('vehicle_id')->index();
                $t->foreignId('trailer_id')->nullable()->constrained('trailers')->nullOnDelete();
                $t->unsignedBigInteger('driver_id')->index();
                $t->unsignedBigInteger('co_driver_id')->nullable();
                $t->string('status', 20)->default('planned')->index();
                $t->string('trip_type', 20)->nullable();
                $t->text('cargo_description')->nullable();
                $t->decimal('cargo_weight_kg', 12, 2)->nullable();
                $t->string('consignee_name')->nullable();
                $t->string('consignee_phone')->nullable();
                $t->foreignId('origin_country_id')->nullable()->constrained('countries')->nullOnDelete();
                $t->string('origin_city')->nullable();
                $t->foreignId('destination_country_id')->nullable()->constrained('countries')->nullOnDelete();
                $t->string('destination_city')->nullable();
                $t->jsonb('route_country_ids')->nullable();
                $t->timestamp('planned_start_at')->nullable();
                $t->timestamp('planned_end_at')->nullable();
                $t->timestamp('actual_start_at')->nullable();
                $t->timestamp('actual_end_at')->nullable();
                $t->decimal('start_odometer', 12, 1)->nullable();
                $t->decimal('end_odometer', 12, 1)->nullable();
                $t->decimal('distance_km', 12, 1)->nullable();
                $t->decimal('revenue_amount', 18, 2)->default(0);
                $t->string('revenue_currency', 3)->default('MWK');
                $t->decimal('revenue_rate', 18, 6)->default(1);
                $t->decimal('revenue_amount_base', 18, 2)->default(0);
                $t->string('pod_path')->nullable();
                $t->text('override_reason')->nullable();
                $t->text('notes')->nullable();
                $t->timestamp('closed_at')->nullable();
                $t->unsignedBigInteger('created_by')->nullable();
                $t->timestamps();
                $t->softDeletes();
            });
        }

        if (!Schema::hasTable('trip_checkpoints')) {
            Schema::create('trip_checkpoints', function (Blueprint $t) {
                $t->id();
                $t->foreignId('trip_id')->constrained('trips')->cascadeOnDelete();
                $t->foreignId('border_post_id')->nullable()->constrained('border_posts')->nullOnDelete();
                $t->string('location')->nullable();
                $t->timestamp('arrived_at');
                $t->timestamp('departed_at')->nullable();
                $t->text('notes')->nullable();
                $t->timestamps();
            });
        }

        if (!Schema::hasTable('trip_expenses')) {
            Schema::create('trip_expenses', function (Blueprint $t) {
                $t->id();
                $t->foreignId('trip_id')->constrained('trips')->cascadeOnDelete();
                $t->foreignId('category_id')->constrained('trip_expense_categories');
                $t->date('expense_date');
                $t->foreignId('country_id')->nullable()->constrained('countries')->nullOnDelete();
                $t->decimal('amount', 18, 2);
                $t->string('currency', 3);
                $t->decimal('exchange_rate', 18, 6)->default(1);
                $t->decimal('amount_base', 18, 2);
                $t->string('paid_by', 20)->default('driver_cash'); // driver_cash | company
                $t->decimal('litres', 10, 2)->nullable();
                $t->decimal('odometer', 12, 1)->nullable();
                $t->string('reference')->nullable();
                $t->string('receipt_path')->nullable();
                $t->string('status', 20)->default('pending')->index(); // pending | approved | rejected
                $t->unsignedBigInteger('approved_by')->nullable();
                $t->timestamp('approved_at')->nullable();
                $t->string('rejection_reason')->nullable();
                $t->text('notes')->nullable();
                $t->unsignedBigInteger('created_by')->nullable();
                $t->timestamps();
                $t->softDeletes();
            });
        }

        if (!Schema::hasTable('driver_advances')) {
            Schema::create('driver_advances', function (Blueprint $t) {
                $t->id();
                $t->foreignId('trip_id')->constrained('trips')->cascadeOnDelete();
                $t->unsignedBigInteger('driver_id')->index();
                $t->decimal('amount', 18, 2);
                $t->string('currency', 3);
                $t->decimal('exchange_rate', 18, 6)->default(1);
                $t->decimal('amount_base', 18, 2);
                $t->date('issued_at');
                $t->string('purpose', 20)->default('allowance'); // allowance | fuel | border | other
                $t->string('method', 20)->nullable();
                $t->string('reference')->nullable();
                $t->text('notes')->nullable();
                $t->unsignedBigInteger('issued_by')->nullable();
                $t->timestamps();
            });
        }

        if (!Schema::hasTable('trip_incidents')) {
            Schema::create('trip_incidents', function (Blueprint $t) {
                $t->id();
                $t->foreignId('trip_id')->constrained('trips')->cascadeOnDelete();
                $t->string('type', 20); // breakdown | accident | delay | theft | other
                $t->timestamp('occurred_at');
                $t->string('location')->nullable();
                $t->foreignId('country_id')->nullable()->constrained('countries')->nullOnDelete();
                $t->text('description')->nullable();
                $t->decimal('cost', 18, 2)->default(0);
                $t->string('currency', 3)->default('MWK');
                $t->decimal('exchange_rate', 18, 6)->default(1);
                $t->decimal('cost_base', 18, 2)->default(0);
                $t->timestamp('resolved_at')->nullable();
                $t->decimal('downtime_hours', 8, 2)->nullable();
                $t->unsignedBigInteger('created_by')->nullable();
                $t->timestamps();
            });
        }
    }

    public function down(): void
    {
        foreach (['trip_incidents', 'driver_advances', 'trip_expenses', 'trip_checkpoints', 'trips'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
