<?php

namespace App\Filament\Resources\PromotionResource\Pages;

use App\Enums\ProfitStateEnum;
use App\Filament\Resources\BlockResource;
use App\Filament\Resources\PromotionResource;
use App\Models\ClientPromotion;
use App\Models\Promotion;
use Filament\Actions;
use Filament\Notifications\Notification;
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
            //            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return PromotionResource::getUrl('promotions', ['record' => $this->record->block]);
    }

    protected function beforeSave(): void
    {
        if ($this->data['selling_price'] != $this->record->selling_price) {
            $payment = ClientPromotion::where('promotion_id', $this->record->id)->first();
            if ($payment) {
                if ($payment->state = ProfitStateEnum::PAID) {
                    Notification::make()
                        ->danger()
                        ->title('This promotion is already sold.')
                        ->send();
                    $this->halt();

                    return;
                }
                if ($this->data['selling_price'] - $payment->rest < 0) {
                    Notification::make()
                        ->danger()
                        ->title('The amount is invalid with the existing payments.')
                        ->send();
                    $this->halt();

                    return;
                }
                $payment->rest = $this->data['selling_price'] - $payment->rest;
                if ($payment->rest == 0) {
                    $payment->state = ProfitStateEnum::PAID->value;
                }
                $payment->save();
            }
        }
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
