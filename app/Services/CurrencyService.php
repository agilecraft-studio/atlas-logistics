<?php

namespace App\Services;

use App\Model\ExchangeRate;
use Illuminate\Validation\ValidationException;

class CurrencyService
{
    public static function base(): string
    {
        return strtoupper(config('logistics.base_currency', 'MWK'));
    }

    /** Latest rate on or before $date: how many base-currency units one unit of $currency is worth. */
    public function rateFor(string $currency, $date = null): ?float
    {
        $currency = strtoupper($currency);
        if ($currency === self::base()) {
            return 1.0;
        }

        $rate = ExchangeRate::where('currency_code', $currency)
            ->where('base_currency_code', self::base())
            ->whereDate('effective_date', '<=', $date ?: now()->toDateString())
            ->orderByDesc('effective_date')
            ->orderByDesc('id')
            ->first();

        return $rate ? (float) $rate->rate : null;
    }

    /**
     * Convert an amount to the base currency.
     * Uses $rate when the user typed one, otherwise the stored rate for that date.
     * Returns [rate, amount_base]. Throws a validation error when no rate can be found.
     */
    public function toBase(float $amount, string $currency, $rate = null, $date = null, string $rateField = 'exchange_rate'): array
    {
        $currency = strtoupper($currency);
        if ($currency === self::base()) {
            return [1.0, round($amount, 2)];
        }

        $rate = ($rate !== null && $rate !== '' && (float) $rate > 0) ? (float) $rate : $this->rateFor($currency, $date);

        if (!$rate) {
            throw ValidationException::withMessages([
                $rateField => "No exchange rate found for {$currency}. Enter a rate here or add one under Master Data > Exchange Rates.",
            ]);
        }

        return [$rate, round($amount * $rate, 2)];
    }
}
