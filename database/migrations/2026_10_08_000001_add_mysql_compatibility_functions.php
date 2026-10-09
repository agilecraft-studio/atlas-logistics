<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Atlas Logistics - MySQL -> PostgreSQL compatibility functions.
 *
 * The application contains a large number of legacy raw SQL statements that call
 * MySQL-only scalar functions (year(), month(), monthname(), day(), date_format(),
 * ifnull()). Rewriting every call site would be a large, risky change to working
 * business logic, so equivalent PostgreSQL functions are created instead.
 *
 * All definitions are CREATE OR REPLACE / idempotent and non-destructive.
 */
return new class extends Migration
{
    /**
     * Column types used by date columns across the application.
     *
     * @var array<int, string>
     */
    protected array $dateTypes = ['date', 'timestamp without time zone', 'timestamp with time zone'];

    public function up(): void
    {
        foreach ($this->dateTypes as $type) {
            // MySQL YEAR() -> integer year
            DB::statement("
                CREATE OR REPLACE FUNCTION year({$type}) RETURNS integer
                LANGUAGE sql IMMUTABLE STRICT AS $$
                    SELECT extract(year from $1)::integer
                $$
            ");

            // MySQL MONTH() -> integer month (1-12)
            DB::statement("
                CREATE OR REPLACE FUNCTION month({$type}) RETURNS integer
                LANGUAGE sql IMMUTABLE STRICT AS $$
                    SELECT extract(month from $1)::integer
                $$
            ");

            // MySQL DAY() -> integer day of month
            DB::statement("
                CREATE OR REPLACE FUNCTION day({$type}) RETURNS integer
                LANGUAGE sql IMMUTABLE STRICT AS $$
                    SELECT extract(day from $1)::integer
                $$
            ");

            // MySQL MONTHNAME() -> full, unpadded month name ('January')
            // FM suppresses the space padding that to_char('Month') would add,
            // so the value matches the month keys used by the application.
            DB::statement("
                CREATE OR REPLACE FUNCTION monthname({$type}) RETURNS text
                LANGUAGE sql IMMUTABLE STRICT AS $$
                    SELECT to_char($1, 'FMMonth')
                $$
            ");

            // MySQL DATE_FORMAT(value, '%d-%m-%Y %h:%i %p')
            DB::statement("
                CREATE OR REPLACE FUNCTION date_format({$type}, text) RETURNS text
                LANGUAGE plpgsql IMMUTABLE STRICT AS $$
                DECLARE
                    fmt text := $2;
                BEGIN
                    -- Multi-character specifiers are translated first.
                    fmt := replace(fmt, '%Y', 'YYYY');
                    fmt := replace(fmt, '%y', 'YY');
                    fmt := replace(fmt, '%m', 'MM');
                    fmt := replace(fmt, '%c', 'FMMM');
                    fmt := replace(fmt, '%d', 'DD');
                    fmt := replace(fmt, '%e', 'FMDD');
                    fmt := replace(fmt, '%H', 'HH24');
                    fmt := replace(fmt, '%k', 'FMHH24');
                    fmt := replace(fmt, '%h', 'HH12');
                    fmt := replace(fmt, '%l', 'FMHH12');
                    fmt := replace(fmt, '%i', 'MI');
                    fmt := replace(fmt, '%s', 'SS');
                    fmt := replace(fmt, '%S', 'SS');
                    fmt := replace(fmt, '%p', 'AM');
                    fmt := replace(fmt, '%M', 'FMMonth');
                    fmt := replace(fmt, '%b', 'Mon');
                    fmt := replace(fmt, '%W', 'FMDay');
                    fmt := replace(fmt, '%a', 'Dy');
                    fmt := replace(fmt, '%j', 'DDD');
                    fmt := replace(fmt, '%%', '%');

                    RETURN to_char($1, fmt);
                END
                $$
            ");
        }

        // MySQL IFNULL(a, b).
        // plpgsql (not sql) because SQL functions are STRICT by default, which
        // would return NULL instead of falling back to the second argument.
        DB::statement("
            CREATE OR REPLACE FUNCTION ifnull(anyelement, anyelement) RETURNS anyelement
            LANGUAGE plpgsql IMMUTABLE AS $$
            BEGIN
                RETURN COALESCE($1, $2);
            END
            $$
        ");
    }

    public function down(): void
    {
        foreach ($this->dateTypes as $type) {
            DB::statement("DROP FUNCTION IF EXISTS year({$type})");
            DB::statement("DROP FUNCTION IF EXISTS month({$type})");
            DB::statement("DROP FUNCTION IF EXISTS day({$type})");
            DB::statement("DROP FUNCTION IF EXISTS monthname({$type})");
            DB::statement("DROP FUNCTION IF EXISTS date_format({$type}, text)");
        }

        DB::statement('DROP FUNCTION IF EXISTS ifnull(anyelement, anyelement)');
    }
};
