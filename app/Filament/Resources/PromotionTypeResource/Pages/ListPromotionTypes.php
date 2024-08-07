<?php

namespace App\Filament\Resources\PromotionTypeResource\Pages;

use App\Filament\Resources\PromotionTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPromotionTypes extends ListRecords
{
    protected static string $resource = PromotionTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
