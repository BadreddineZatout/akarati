<?php

namespace App\Filament\Resources\PromotionTypeResource\Pages;

use App\Filament\Resources\PromotionTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPromotionType extends EditRecord
{
    protected static string $resource = PromotionTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
