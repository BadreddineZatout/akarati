<?php

namespace App\Filament\Resources\UserResource\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Model;

class WalletBalance extends BaseWidget
{
    public ?Model $record = null;

    protected function getStats(): array
    {
        if ($this->record->wallet) {
            return [
                Stat::make('Current Balance', $this->record->wallet?->balance),
            ];
        }

        return [];
    }
}
