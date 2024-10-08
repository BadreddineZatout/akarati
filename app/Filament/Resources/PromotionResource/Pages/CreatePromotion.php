<?php

namespace App\Filament\Resources\PromotionResource\Pages;

use App\Filament\Resources\BlockResource;
use App\Filament\Resources\PromotionResource;
use App\Models\Block;
use Filament\Resources\Pages\CreateRecord;

class CreatePromotion extends CreateRecord
{
    public Block $block;

    protected static string $resource = PromotionResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['block_id'] = $this->block->id;

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return PromotionResource::getUrl('promotions', ['record' => $this->block->id]);
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
