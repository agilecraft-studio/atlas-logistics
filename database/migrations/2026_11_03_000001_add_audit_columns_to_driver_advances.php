<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Advances are office-recorded cash records. They are never acknowledged by the driver, so the
 * audit trail lives entirely on the record: who recorded it (issued_by) and, if removed, who
 * removed it. Soft deletes keep the history instead of erasing it.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('driver_advances')) {
            return;
        }

        Schema::table('driver_advances', function (Blueprint $table) {
            if (!Schema::hasColumn('driver_advances', 'deleted_at')) {
                $table->softDeletes();
            }
            if (!Schema::hasColumn('driver_advances', 'deleted_by')) {
                $table->unsignedBigInteger('deleted_by')->nullable();
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('driver_advances')) {
            return;
        }

        Schema::table('driver_advances', function (Blueprint $table) {
            if (Schema::hasColumn('driver_advances', 'deleted_by')) {
                $table->dropColumn('deleted_by');
            }
            if (Schema::hasColumn('driver_advances', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
        });
    }
};
