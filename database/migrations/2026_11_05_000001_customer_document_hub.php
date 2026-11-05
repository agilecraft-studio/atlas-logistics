<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Customer document hub. Final tax invoices stay in the existing invoicing tables; proforma
 * invoices and credit notes are invoice documents that share the same numbering, line and PDF
 * pipeline. Operational documents (delivery notes, dispatch sheets, waybills, quotes and signed
 * proof of delivery) live in customer_documents and always point at a customer, with an optional
 * link to the shipment (trip) whose driver/vehicle/cargo details they print.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $t) {
            if (!Schema::hasColumn('invoices', 'document_type')) {
                $t->string('document_type', 20)->default('tax_invoice')->after('invoice_number');
                $t->index(['client_id', 'document_type', 'status'], 'invoices_client_doctype_status_idx');
            }
            if (!Schema::hasColumn('invoices', 'source_invoice_id')) {
                $t->unsignedBigInteger('source_invoice_id')->nullable()->after('document_type');
                $t->index('source_invoice_id');
            }
            if (!Schema::hasColumn('invoices', 'credit_reason')) {
                $t->text('credit_reason')->nullable()->after('cancel_reason');
            }
        });

        if (!Schema::hasTable('document_sequences')) {
            Schema::create('document_sequences', function (Blueprint $t) {
                $t->id();
                $t->string('prefix', 10);
                $t->unsignedSmallInteger('year');
                $t->unsignedBigInteger('last_number')->default(0);
                $t->timestamps();
                $t->unique(['prefix', 'year']);
            });
        }

        if (!Schema::hasTable('customer_documents')) {
            Schema::create('customer_documents', function (Blueprint $t) {
                $t->id();
                $t->unsignedBigInteger('customer_id')->index();
                $t->foreignId('trip_id')->nullable()->constrained('trips')->nullOnDelete();
                $t->string('type', 30);
                $t->string('document_number', 40)->unique();
                $t->string('status', 20)->default('draft');
                $t->date('issue_date')->nullable();
                $t->date('due_date')->nullable();
                $t->date('valid_until')->nullable();
                $t->decimal('amount', 18, 2)->nullable();
                $t->string('currency_code', 3)->nullable();
                $t->decimal('exchange_rate', 18, 6)->default(1);
                $t->decimal('amount_base', 18, 2)->nullable();
                $t->unsignedBigInteger('driver_id')->nullable();
                $t->unsignedBigInteger('vehicle_id')->nullable();
                $t->foreignId('trailer_id')->nullable()->constrained('trailers')->nullOnDelete();
                $t->string('signed_by')->nullable();
                $t->timestamp('signed_at')->nullable();
                $t->string('file_path')->nullable();
                $t->string('file_name')->nullable();
                $t->string('mime_type', 100)->nullable();
                $t->unsignedBigInteger('file_size')->nullable();
                $t->text('notes')->nullable();
                $t->jsonb('meta')->nullable();
                $t->unsignedBigInteger('created_by')->nullable();
                $t->unsignedBigInteger('updated_by')->nullable();
                $t->timestamps();
                $t->softDeletes();
                $t->index(['customer_id', 'type', 'status']);
                $t->index('issue_date');
                $t->index('trip_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_documents');
        Schema::dropIfExists('document_sequences');

        Schema::table('invoices', function (Blueprint $t) {
            if (Schema::hasColumn('invoices', 'credit_reason')) {
                $t->dropColumn('credit_reason');
            }
            if (Schema::hasColumn('invoices', 'source_invoice_id')) {
                $t->dropColumn('source_invoice_id');
            }
            if (Schema::hasColumn('invoices', 'document_type')) {
                $t->dropColumn('document_type');
            }
        });
    }
};
