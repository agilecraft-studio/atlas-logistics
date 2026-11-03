<?php

namespace Database\Seeders;

use App\Model\Currency;
use Illuminate\Database\Seeder;

class InvoicingSeeder extends Seeder
{
    /**
     * Ensure the launch currencies exist. Rates are entered by finance under
     * Master Data > Exchange Rates; the base currency always resolves to 1.
     * Idempotent: safe to run repeatedly.
     */
    public function run(): void
    {
        $currencies = [
            ['code' => 'MWK', 'name' => 'Malawi Kwacha', 'symbol' => 'MK'],
            ['code' => 'USD', 'name' => 'US Dollar', 'symbol' => '$'],
        ];

        foreach ($currencies as $row) {
            Currency::firstOrCreate(
                ['code' => $row['code']],
                ['name' => $row['name'], 'symbol' => $row['symbol'], 'is_active' => true]
            );
        }
    }
}
