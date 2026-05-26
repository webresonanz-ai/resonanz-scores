<?php

declare(strict_types=1);

namespace App\Support;

final class Currency
{
    public const CODE = 'IDR';

    public static function formatStorage(float $amount): string
    {
        return number_format((float) round($amount), 0, '.', '');
    }
}
