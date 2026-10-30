<?php

namespace App\Support;

use InvalidArgumentException;

class Money
{
    /**
     * Format an integer amount into Indonesian Rupiah string (e.g. 'Rp 12.500').
     */
    public static function format(int|float|null $amount): string
    {
        if ($amount === null) {
            return 'Rp 0';
        }

        $intAmount = (int) round($amount);
        if ($intAmount < 0) {
            return '-Rp ' . number_format(abs($intAmount), 0, ',', '.');
        }

        return 'Rp ' . number_format($intAmount, 0, ',', '.');
    }

    /**
     * Parse user input into integer rupiah.
     * Strips 'Rp', dots, spaces. Rejects negative or values > 999.999.999.
     */
    public static function parse(string|int|float|null $input): int
    {
        if ($input === null || $input === '') {
            return 0;
        }

        if (is_int($input)) {
            if ($input < 0 || $input > 999_999_999) {
                throw new InvalidArgumentException("Nilai rupiah di luar batas wajar (0 - 999.999.999).");
            }
            return $input;
        }

        if (is_float($input)) {
            $val = (int) round($input);
            if ($val < 0 || $val > 999_999_999) {
                throw new InvalidArgumentException("Nilai rupiah di luar batas wajar (0 - 999.999.999).");
            }
            return $val;
        }

        $clean = preg_replace('/[^0-9]/', '', (string) $input);
        if ($clean === '') {
            return 0;
        }

        $val = (int) $clean;
        if ($val < 0 || $val > 999_999_999) {
            throw new InvalidArgumentException("Nilai rupiah di luar batas wajar (0 - 999.999.999).");
        }

        return $val;
    }

    /**
     * Round to nearest 100 rupiah.
     */
    public static function roundNearest100(int $amount): int
    {
        return (int) (round($amount / 100) * 100);
    }
}
