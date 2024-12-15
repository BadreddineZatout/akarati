<?php

namespace App\Enums;

enum SubscriptionStateEnum: string
{
    case ACTIVE = 'active';
    case CANCELED = 'canceled';
    case SUSPENDED = 'suspended';
    case ENDED = 'ended';
    case TRAIL = 'onTrial';

    public static function color($value): string
    {
        return match ($value) {
            self::CANCELED->value => 'warning',
            self::SUSPENDED->value => 'danger',
            self::ENDED->value => 'danger',
            self::TRAIL->value => 'primary',
            self::ACTIVE->value => 'success',
        };
    }
}
