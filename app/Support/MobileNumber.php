<?php

namespace App\Support;

/** Mobile numbers stored one way only — E.164, e.g. "+919876543210". */
class MobileNumber
{
    public const COUNTRY_CODE = '91';

    /** An Indian mobile (10 digits starting 6–9), or any other country's E.164 number. */
    public const PATTERN = '/^\+(?:91[6-9]\d{9}|(?!91)[1-9]\d{6,14})$/';

    /** "98765 43210", "098765 43210" and "+91 98765 43210" all become "+919876543210". */
    public static function normalise(string $mobileNumber): string
    {
        $digits = preg_replace('/\D+/', '', str_replace('(0)', '', $mobileNumber));

        if ($digits === '') {
            return '';
        }

        if (str_starts_with(ltrim($mobileNumber), '+')) {
            $dialled = $digits;
        } elseif (str_starts_with($digits, '00')) {
            $dialled = substr($digits, 2);
        } else {
            // Bare digits are read as Indian, so a mistyped extra digit isn't taken for another country.
            return '+'.self::COUNTRY_CODE.self::national($digits);
        }

        return str_starts_with($dialled, self::COUNTRY_CODE)
            ? '+'.self::COUNTRY_CODE.self::national(substr($dialled, strlen(self::COUNTRY_CODE)))
            : '+'.$dialled;
    }

    /** Drops an Indian number's trunk "0" or "91" — and nothing else, so validation can catch the rest. */
    private static function national(string $digits): string
    {
        if (strlen($digits) === 12 && str_starts_with($digits, self::COUNTRY_CODE)) {
            return substr($digits, 2);
        }

        return strlen($digits) === 11 && $digits[0] === '0' ? substr($digits, 1) : $digits;
    }
}
