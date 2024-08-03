<?php

namespace App\Filament\Resources\PromotionResource\Pages;

use App\Filament\Resources\BlockResource;
use App\Filament\Resources\PromotionResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePromotion extends CreateRecord
{
    protected static string $resource = PromotionResource::class;
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $pathElements = explode('/', request()->path());
        $data['block_id'] = $pathElements[3];
        return $data;
    }

    protected function getRedirectUrl(): string
    {
        $pathElements = explode('/', request()->path());
        return PromotionResource::getUrl('promotions', ['record' => $pathElements[3]]);
    }

    public function getBreadcrumbs(): array
    {
        $resource = static::getResource();
        return [
            BlockResource::getUrl() => 'blocks',
            '#' => $this->block->name,
            $resource::getUrl('promotions', ['record' => $this->block]) => $resource::getBreadcrumb(),
            $this->getBreadcrumb(),
        ];
    }
}
