<?php

namespace App\Filament\Resources\PromotionResource\Pages;

use App\Filament\Resources\BlockResource;
use App\Filament\Resources\PromotionResource;
use App\Models\Block;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPromotions extends ListRecords
{
    protected static string $resource = PromotionResource::class;

    public Block $block;

    //    protected function getHeaderActions(): array
    //    {
    //        return [
    //            Actions\CreateAction::make(),
    //        ];
    //    }

    public function mount(): void
    {
        parent::mount();
        $this->block = Block::findOrFail(request('record'));
    }

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->url(fn (): string => PromotionResource::getUrl('create', ['block' => request('record')])),
        ];
    }

    public function getBreadcrumbs(): array
    {
        $resource = static::getResource();

        return [
            BlockResource::getUrl() => 'blocks',
            '#' => $this->block->name,
            $resource::getUrl('promotions', ['record' => request('record')]) => $resource::getBreadcrumb(),
            $this->getBreadcrumb(),
        ];
    }
}
