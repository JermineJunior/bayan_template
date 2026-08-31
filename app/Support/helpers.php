<?php

use Illuminate\Support\Number;

if (! function_exists('money')) {
    /**
     * Format a numeric amount for display as money.
     *
     * Strips trailing ".00" when the value is whole (e.g. 100000 -> "100,000"),
     * but keeps decimals when present (e.g. 1.25 -> "1.25").
     */
    function money(mixed $value = null, int $precision = 2): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        $number = (float) $value;

        $precision = (float) $number === floor((float) $number)
            ? 0
            : $precision;

        return Number::format($number, $precision);
    }
}
