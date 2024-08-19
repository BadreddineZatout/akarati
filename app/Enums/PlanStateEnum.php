<?php

namespace App\Enums;

enum PlanStateEnum: int
{

    case Able = 1;
    case Disable = 0;
    public static function color($value): string
    {
        return match ((int)$value) {
            self::Disable->value => 'danger',
            self::Able->value => 'success',
        };
    }
    public static function text($value): string{
        return match ((int)$value) {
            self::Disable->value => 'Disabled',
            self::Able->value => 'Active',
        };
    }
}
