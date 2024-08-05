<?php

namespace App\Filament\Resources\PromotionResource\Pages;

use App\Filament\Resources\BlockResource;
use App\Filament\Resources\PromotionResource;
use App\Models\Promotion;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPromotion extends EditRecord
{
    protected static string $resource = PromotionResource::class;

    public function mount($record): void
    {
        $this->record = Promotion::find($record);

        $this->fillForm();
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return PromotionResource::getUrl('promotions', ['record' => $this->record->block]);
    }

    public function getBreadcrumbs(): array
    {
        $resource = static::getResource();
        return [
            BlockResource::getUrl() => 'blocks',
            '#' => $this->record->block->name,
            $resource::getUrl('promotions', ['record' => $this->record->block]) => $resource::getBreadcrumb(),
            $this->getBreadcrumb(),
        ];
    }
}
