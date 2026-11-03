<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Client invoicing (systemDesign.md 7.11). One invoice has one currency; every amount is stored
 * as a fixed 2-decimal amount (integer minor units in application code). The exchange rate used is
 * copied onto the invoice when it is issued so later rate changes never alter past invoices.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('invoice_sequences')) {
            Schema::create('invoice_sequences', function (Blueprint $t) {
                $t->id();
                $t->string('prefix', 10);
                $t->unsignedSmallInteger('year');
                $t->unsignedBigInteger('last_number')->default(0);
                $t->timestamps();
                $t->unique(['prefix', 'year']);
            });
        }

        if (!Schema::hasTable('invoices')) {
            Schema::create('invoices', function (Blueprint $t) {
                $t->id();
                $t->string('invoice_number', 30)->unique();
                $t->unsignedBigInteger('client_id')->nullable();
                $t->string('client_name');
                $t->string('client_email')->nullable();
                $t->string('client_phone', 30)->nullable();
                $t->text('client_address')->nullable();
                $t->string('status', 20)->default('draft');
                $t->string('currency_code', 3);
                $t->string('currency_symbol', 8)->nullable();
                $t->string('base_currency_code', 3);
                $t->decimal('exchange_rate', 18, 6)->default(1);
                $t->boolean('rate_locked')->default(false);
                $t->decimal('tax_rate', 8, 4)->default(0);
                $t->decimal('subtotal', 18, 2)->default(0);
                $t->decimal('tax_amount', 18, 2)->default(0);
                $t->decimal('total', 18, 2)->default(0);
                $t->decimal('total_base', 18, 2)->default(0);
                $t->decimal('amount_paid', 18, 2)->default(0);
                $t->decimal('balance_due', 18, 2)->default(0);
                $t->date('issue_date')->nullable();
                $t->date('due_date')->nullable();
                $t->timestamp('issued_at')->nullable();
                $t->timestamp('cancelled_at')->nullable();
                $t->text('cancel_reason')->nullable();
                $t->text('payment_instructions')->nullable();
                $t->text('notes')->nullable();
                $t->unsignedBigInteger('created_by')->nullable();
                $t->unsignedBigInteger('issued_by')->nullable();
                $t->unsignedBigInteger('cancelled_by')->nullable();
                $t->timestamps();
                $t->softDeletes();
                $t->index(['client_id', 'status']);
                $t->index('issue_date');
                $t->index('currency_code');
            });
        }

        if (!Schema::hasTable('invoice_lines')) {
            Schema::create('invoice_lines', function (Blueprint $t) {
                $t->id();
                $t->foreignId('invoice_id')->constrained('invoices')->cascadeOnDelete();
                $t->foreignId('trip_id')->nullable()->constrained('trips')->nullOnDelete();
                $t->string('line_type', 30)->default('other');
                $t->string('description');
                $t->decimal('quantity', 18, 4)->default(1);
                $t->decimal('unit_price', 18, 4)->default(0);
                $t->decimal('line_total', 18, 2)->default(0);
                $t->unsignedInteger('sort_order')->default(0);
                $t->timestamps();
                $t->index(['invoice_id', 'sort_order']);
                $t->index('trip_id');
            });
        }

        if (!Schema::hasTable('invoice_payments')) {
            Schema::create('invoice_payments', function (Blueprint $t) {
                $t->id();
                $t->foreignId('invoice_id')->constrained('invoices')->cascadeOnDelete();
                $t->decimal('amount', 18, 2);
                $t->string('currency_code', 3);
                $t->date('paid_at');
                $t->string('method', 30);
                $t->string('reference')->nullable();
                $t->text('notes')->nullable();
                $t->unsignedBigInteger('recorded_by')->nullable();
                $t->timestamps();
                $t->softDeletes();
                $t->index(['invoice_id', 'paid_at']);
            });
        }

        if (!Schema::hasTable('invoice_audit_logs')) {
            Schema::create('invoice_audit_logs', function (Blueprint $t) {
                $t->id();
                $t->foreignId('invoice_id')->constrained('invoices')->cascadeOnDelete();
                $t->unsignedBigInteger('user_id')->nullable();
                $t->string('action', 40);
                $t->jsonb('details')->nullable();
                $t->timestamps();
                $t->index(['invoice_id', 'created_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_audit_logs');
        Schema::dropIfExists('invoice_payments');
        Schema::dropIfExists('invoice_lines');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('invoice_sequences');
    }
};
