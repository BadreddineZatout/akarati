<?php

namespace App\Enums;

enum ProfitStateEnum: string
{
    case PAID = 'paid';
    case NOT_PAID = 'not paid';

    public static function color($value): string
    {
        return match ($value) {
            self::PAID->value => 'success',
            self::NOT_PAID->value => 'danger',
        };
    }
}
