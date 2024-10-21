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
        if ($this->record) {
            if ($this->record->wallet) {
                return [
                    Stat::make('Current Balance', $this->record->wallet?->balance),
                ];
            }

            return [];
        }

        if (auth()->user()->wallet) {
            return [
                Stat::make('Current Balance', auth()->user()->wallet?->balance),
            ];
        }
    }
}
