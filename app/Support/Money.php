<?php

namespace App\Support;

class Money
{
    public static function fromInput(mixed $value): int
    {
        if ($value === null || $value === '') {
            return 0;
        }

        $normalized = preg_replace('/[^0-9.\-]/', '', (string) $value);

        if ($normalized === '' || $normalized === '-') {
            return 0;
        }

        return (int) round(((float) $normalized) * 100);
    }

    public static function format(int $cents): string
    {
        return '$'.number_format($cents / 100, 2);
    }
}
