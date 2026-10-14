<?php

namespace App\Support;

class Money
{
    /** Indian grouping: 82836 → "₹82,836", 110448.5 → "₹1,10,448.50". */
    public static function inr(float $amount): string
    {
        $rounded = round($amount, 2);
        $sign = $rounded < 0 ? '-' : '';
        $whole = (int) floor(abs($rounded));
        $paise = (int) round((abs($rounded) - $whole) * 100);
        $digits = (string) $whole;

        // The last three digits stay together; everything above them pairs off.
        if (strlen($digits) > 3) {
            $digits = preg_replace('/\B(?=(\d{2})+(?!\d))/', ',', substr($digits, 0, -3)).','.substr($digits, -3);
        }

        return '₹'.$sign.$digits.($paise > 0 ? '.'.str_pad((string) $paise, 2, '0', STR_PAD_LEFT) : '');
    }
}
