<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Atlas Logistics - extra MySQL -> PostgreSQL compatibility functions.
 *
 * Complements 2026_10_08_000001_add_mysql_compatibility_functions:
 *   CURDATE()  - used in whereDate('date', DB::raw('CURDATE()'))
 *   DAYNAME()  - used in report queries ('Monday', 'Tuesday', ...)
 *
 * Idempotent and non-destructive.
 */
return new class extends Migration
{
    protected array $dateTypes = ['date', 'timestamp without time zone', 'timestamp with time zone'];

    public function up(): void
    {
        DB::statement("
            CREATE OR REPLACE FUNCTION curdate() RETURNS date
            LANGUAGE sql STABLE AS $$
                SELECT current_date
            $$
        ");

        foreach ($this->dateTypes as $type) {
            DB::statement("
                CREATE OR REPLACE FUNCTION dayname({$type}) RETURNS text
                LANGUAGE sql IMMUTABLE STRICT AS $$
                    SELECT to_char($1, 'FMDay')
                $$
            ");
        }
    }

    public function down(): void
    {
        DB::statement('DROP FUNCTION IF EXISTS curdate()');
        foreach ($this->dateTypes as $type) {
            DB::statement("DROP FUNCTION IF EXISTS dayname({$type})");
        }
    }
};
