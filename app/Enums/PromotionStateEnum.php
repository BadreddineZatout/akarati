<?php

namespace App\Enums;

enum PromotionStateEnum :string
{
    case PENDING = 'pending';
    case NotLaunched = 'not_launched';
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case Archived = 'archived';

    public static function color($value): string
    {
        return match ($value) {
            self::PENDING->value => 'warning',
            self::NotLaunched->value => 'danger',
            self::InProgress->value => 'primary',
            self::Completed->value => 'success',
            self::Archived->value => 'secondary',
        };
    }
}
