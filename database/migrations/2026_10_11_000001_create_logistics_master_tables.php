<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Atlas Logistics - master data for cross-border operations (systemDesign.md section 7.1).
 * Countries, currencies, exchange rates, border posts, trip expense categories, document types.
 * Seeded with SADC defaults. Safe to run once; skips tables that already exist.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('countries')) {
            Schema::create('countries', function (Blueprint $t) {
                $t->id();
                $t->string('name');
                $t->string('iso2', 2)->unique();
                $t->string('default_currency', 3)->nullable();
                $t->boolean('is_active')->default(true);
                $t->timestamps();
            });
        }

        if (!Schema::hasTable('currencies')) {
            Schema::create('currencies', function (Blueprint $t) {
                $t->id();
                $t->string('code', 3)->unique();
                $t->string('name');
                $t->string('symbol', 8)->nullable();
                $t->boolean('is_active')->default(true);
                $t->timestamps();
            });
        }

        if (!Schema::hasTable('exchange_rates')) {
            Schema::create('exchange_rates', function (Blueprint $t) {
                $t->id();
                $t->string('currency_code', 3);
                $t->string('base_currency_code', 3);
                $t->decimal('rate', 18, 6); // 1 unit of currency_code = rate units of base currency
                $t->date('effective_date');
                $t->string('source')->nullable();
                $t->text('notes')->nullable();
                $t->unsignedBigInteger('created_by')->nullable();
                $t->timestamps();
                $t->index(['currency_code', 'effective_date']);
            });
        }

        if (!Schema::hasTable('border_posts')) {
            Schema::create('border_posts', function (Blueprint $t) {
                $t->id();
                $t->string('name');
                $t->foreignId('country_id')->nullable()->constrained('countries')->nullOnDelete();
                $t->foreignId('neighbour_country_id')->nullable()->constrained('countries')->nullOnDelete();
                $t->string('corridor')->nullable();
                $t->boolean('is_active')->default(true);
                $t->timestamps();
            });
        }

        if (!Schema::hasTable('trip_expense_categories')) {
            Schema::create('trip_expense_categories', function (Blueprint $t) {
                $t->id();
                $t->string('name');
                $t->string('code', 30)->unique();
                $t->boolean('requires_receipt')->default(false);
                $t->boolean('is_active')->default(true);
                $t->timestamps();
            });
        }

        if (!Schema::hasTable('document_types')) {
            Schema::create('document_types', function (Blueprint $t) {
                $t->id();
                $t->string('name');
                $t->string('applies_to', 10); // vehicle | trailer | driver
                $t->boolean('is_critical')->default(true); // expired critical docs block dispatch
                $t->unsignedSmallInteger('default_alert_days')->default(30);
                $t->boolean('is_active')->default(true);
                $t->timestamps();
                $t->unique(['name', 'applies_to']);
            });
        }

        $this->seed();
    }

    protected function seed(): void
    {
        $now = now();

        if (DB::table('currencies')->count() === 0) {
            $rows = [
                ['MWK', 'Malawi Kwacha', 'MK'], ['USD', 'US Dollar', '$'], ['ZAR', 'South African Rand', 'R'],
                ['ZMW', 'Zambian Kwacha', 'ZK'], ['TZS', 'Tanzanian Shilling', 'TSh'], ['MZN', 'Mozambican Metical', 'MT'],
                ['BWP', 'Botswana Pula', 'P'], ['NAD', 'Namibian Dollar', 'N$'], ['LSL', 'Lesotho Loti', 'L'],
                ['SZL', 'Eswatini Lilangeni', 'E'], ['AOA', 'Angolan Kwanza', 'Kz'], ['CDF', 'Congolese Franc', 'FC'],
            ];
            foreach ($rows as [$c, $n, $s]) {
                DB::table('currencies')->insert(['code' => $c, 'name' => $n, 'symbol' => $s, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now]);
            }
        }

        if (DB::table('countries')->count() === 0) {
            $rows = [
                ['Malawi', 'MW', 'MWK'], ['Zambia', 'ZM', 'ZMW'], ['Democratic Republic of Congo', 'CD', 'CDF'],
                ['Tanzania', 'TZ', 'TZS'], ['Mozambique', 'MZ', 'MZN'], ['Zimbabwe', 'ZW', 'USD'],
                ['South Africa', 'ZA', 'ZAR'], ['Botswana', 'BW', 'BWP'], ['Namibia', 'NA', 'NAD'],
                ['Eswatini', 'SZ', 'SZL'], ['Lesotho', 'LS', 'LSL'], ['Angola', 'AO', 'AOA'],
            ];
            foreach ($rows as [$n, $i, $c]) {
                DB::table('countries')->insert(['name' => $n, 'iso2' => $i, 'default_currency' => $c, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now]);
            }
        }

        if (DB::table('border_posts')->count() === 0) {
            $id = DB::table('countries')->pluck('id', 'iso2');
            $rows = [
                ['Mwanza', 'MW', 'MZ'], ['Mloka', 'MW', 'MZ'], ['Songwe', 'MW', 'TZ'], ['Mchinji', 'MW', 'ZM'],
                ['Tunduma', 'TZ', 'ZM'], ['Nakonde', 'ZM', 'TZ'], ['Kasumbalesa', 'ZM', 'CD'], ['Chirundu', 'ZM', 'ZW'],
                ['Machipanda', 'MZ', 'ZW'], ['Beitbridge', 'ZW', 'ZA'], ['Ramatlabama', 'BW', 'ZA'],
            ];
            foreach ($rows as [$n, $a, $b]) {
                DB::table('border_posts')->insert([
                    'name' => $n, 'country_id' => $id[$a] ?? null, 'neighbour_country_id' => $id[$b] ?? null,
                    'is_active' => true, 'created_at' => $now, 'updated_at' => $now,
                ]);
            }
        }

        if (DB::table('trip_expense_categories')->count() === 0) {
            $rows = [
                ['Fuel', 'fuel', true], ['Tolls', 'tolls', true], ['Parking', 'parking', false], ['Border Charges', 'border', true],
                ['Permits', 'permits', true], ['Loading / Unloading', 'loading', false], ['Driver Allowance', 'allowance', false],
                ['Repairs & Parts', 'repairs', true], ['Fines & Penalties', 'fines', true], ['Miscellaneous', 'misc', false],
            ];
            foreach ($rows as [$n, $c, $r]) {
                DB::table('trip_expense_categories')->insert(['name' => $n, 'code' => $c, 'requires_receipt' => $r, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now]);
            }
        }

        if (DB::table('document_types')->count() === 0) {
            $rows = [
                ['Registration Book', 'vehicle', true], ['Insurance Certificate', 'vehicle', true], ['COMESA Yellow Card', 'vehicle', true],
                ['Roadworthy Certificate', 'vehicle', true], ['Cross-Border Permit', 'vehicle', true], ['Fitness Certificate', 'vehicle', false],
                ['Operating Licence', 'vehicle', true], ['Dangerous Goods Permit', 'vehicle', false],
                ['Registration Book', 'trailer', true], ['Roadworthy Certificate', 'trailer', true], ['Insurance Certificate', 'trailer', true],
                ['National ID', 'driver', false], ['Driving Licence', 'driver', true], ['Passport', 'driver', true],
                ['Visa / Entry Permit', 'driver', false], ['Yellow Fever Card', 'driver', false], ['Defensive Driving Certificate', 'driver', false],
                ['Dangerous Goods Certificate', 'driver', false], ['Medical Certificate', 'driver', false],
            ];
            foreach ($rows as [$n, $a, $c]) {
                DB::table('document_types')->insert(['name' => $n, 'applies_to' => $a, 'is_critical' => $c, 'default_alert_days' => 30, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now]);
            }
        }
    }

    public function down(): void
    {
        foreach (['document_types', 'trip_expense_categories', 'border_posts', 'exchange_rates', 'currencies', 'countries'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
