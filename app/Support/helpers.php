<?php

if (! function_exists('money')) {
    function money($amount, int $decimals = 0, string $symbol = '$'): string
    {
        $value = is_numeric($amount) ? (float) $amount : 0.0;
        $negative = $value < 0;
        $formatted = number_format(abs($value), $decimals, ',', '.');

        return ($negative ? '-' : '') . $symbol . ' ' . $formatted;
    }
}
