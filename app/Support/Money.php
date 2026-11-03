<?php

namespace App\Support;

/**
 * Fixed-point money maths. All amounts are handled as integer minor units (cents) so invoice
 * totals never pass through a float. Two decimal places are used for every enabled currency
 * (MWK, USD, ...); quantities and rates are parsed from their decimal strings as scaled integers.
 */
final class Money
{
    public const SCALE = 2;
    private const FACTOR = 100;

    /** Parse a decimal string/number into minor units, rounding half away from zero. */
    public static function toMinor($amount): int
    {
        return self::decimalToScaled($amount, self::SCALE);
    }

    /** Render minor units as a plain decimal string suitable for a DECIMAL(18,2) column. */
    public static function fromMinor(int $minor): string
    {
        $sign = $minor < 0 ? '-' : '';
        $abs  = abs($minor);
        return $sign . intdiv($abs, self::FACTOR) . '.' . str_pad((string) ($abs % self::FACTOR), 2, '0', STR_PAD_LEFT);
    }

    /** Human display: "MK 1,234.56 MWK" (symbol and code are both shown). */
    public static function format(int $minor, string $code = '', ?string $symbol = null): string
    {
        $sign = $minor < 0 ? '-' : '';
        $abs  = abs($minor);
        $text = $sign . number_format(intdiv($abs, self::FACTOR)) . '.' . str_pad((string) ($abs % self::FACTOR), 2, '0', STR_PAD_LEFT);
        $prefix = trim((string) ($symbol ?: $code));

        return trim(($prefix !== '' ? $prefix . ' ' : '') . $text . ($code !== '' ? ' ' . $code : ''));
    }

    /** Line total = round(unit price in minor units × quantity). */
    public static function multiplyQuantity(int $unitPriceMinor, $quantity): int
    {
        return self::mulDivRound($unitPriceMinor, self::decimalToScaled($quantity, 4), 10000);
    }

    /** Percentage amount, e.g. 16.5% of a subtotal. */
    public static function percent(int $baseMinor, $ratePercent): int
    {
        return self::mulDivRound($baseMinor, self::decimalToScaled($ratePercent, 4), 1000000);
    }

    /** Convert minor units using an exchange rate (base units per one invoice-currency unit). */
    public static function applyRate(int $minor, $rate): int
    {
        return self::mulDivRound($minor, self::decimalToScaled($rate, 6), 1000000);
    }

    /** Convert base-currency minor units into another currency: amount / rate. */
    public static function divideByRate(int $baseMinor, $rate): int
    {
        $scaled = self::decimalToScaled($rate, 6);
        return $scaled === 0 ? 0 : self::mulDivRound($baseMinor, 1000000, $scaled);
    }

    /** Round a decimal string/number to a fixed number of decimals, returned as a decimal string. */
    public static function roundDecimal($value, int $scale): string
    {
        $scaled = self::decimalToScaled($value, $scale);
        $factor = 10 ** $scale;
        $sign = $scaled < 0 ? '-' : '';
        $abs = abs($scaled);
        if ($scale === 0) {
            return $sign . (string) $abs;
        }
        return $sign . intdiv($abs, $factor) . '.' . str_pad((string) ($abs % $factor), $scale, '0', STR_PAD_LEFT);
    }

    private static function decimalToScaled($value, int $scale): int
    {
        if ($value === null || $value === '') {
            return 0;
        }

        $s = trim((string) $value);
        $negative = str_starts_with($s, '-');
        $s = ltrim($s, '+-');
        $s = str_replace([',', ' '], '', $s);

        if (!preg_match('/^(\d*)(?:\.(\d*))?$/', $s, $m)) {
            return 0;
        }

        $whole = $m[1] !== '' ? $m[1] : '0';
        $frac  = $m[2] ?? '';
        $keep  = str_pad(substr($frac, 0, $scale), $scale, '0');
        $scaled = (int) $whole * (10 ** $scale) + (int) $keep;

        // Round half away from zero on the first dropped digit.
        if (strlen($frac) > $scale && (int) $frac[$scale] >= 5) {
            $scaled++;
        }

        return $negative ? -$scaled : $scaled;
    }

    /**
     * (a × b) / denominator rounded half away from zero, without overflowing 64-bit ints.
     * bcmath keeps the intermediate exact; the plain-int path is only used on hosts without it.
     */
    private static function mulDivRound(int $a, int $b, int $denominator): int
    {
        if ($denominator === 0) {
            return 0;
        }

        if (!function_exists('bcmul')) {
            return self::divRound($a * $b, $denominator);
        }

        $product = bcmul((string) $a, (string) $b, 0);
        $negative = str_starts_with($product, '-');
        $abs = ltrim($product, '-');
        $denominatorText = (string) $denominator;
        $quotient = bcdiv($abs, $denominatorText, 0);
        $remainder = bcmod($abs, $denominatorText);

        if (bccomp(bcmul($remainder, '2', 0), $denominatorText, 0) >= 0) {
            $quotient = bcadd($quotient, '1', 0);
        }

        return (int) ($negative ? '-' . $quotient : $quotient);
    }

    private static function divRound(int $numerator, int $denominator): int
    {
        if ($denominator === 0) {
            return 0;
        }
        $negative = $numerator < 0;
        $abs = abs($numerator);
        $quotient = intdiv($abs, $denominator);
        $remainder = $abs % $denominator;
        if ($remainder * 2 >= $denominator) {
            $quotient++;
        }
        return $negative ? -$quotient : $quotient;
    }
}
