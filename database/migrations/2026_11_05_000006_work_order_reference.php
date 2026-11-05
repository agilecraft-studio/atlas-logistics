<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Work orders keep the workshop's own job/reference number alongside the
 * auto-increment id. The create/edit forms already collect it.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('work_orders') && !Schema::hasColumn('work_orders', 'reference')) {
            Schema::table('work_orders', function (Blueprint $table) {
                $table->string('reference', 60)->nullable()->index();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('work_orders') && Schema::hasColumn('work_orders', 'reference')) {
            Schema::table('work_orders', function (Blueprint $table) {
                $table->dropColumn('reference');
            });
        }
    }
};
