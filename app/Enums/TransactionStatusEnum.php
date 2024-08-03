<?php

namespace App\Enums;

enum TransactionStatusEnum: string
{
    case PENDING = 'pending';
    case ACCEPTED = 'accepted';
    case REFUSED = 'refused';

    public static function color($value): string
    {
        return match ($value) {
            self::PENDING->value => 'warning',
            self::ACCEPTED->value => 'success',
            self::REFUSED->value => 'danger',
        };
    }
}
