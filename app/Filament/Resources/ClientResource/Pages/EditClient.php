<?php

namespace App\Filament\Resources\ClientResource\Pages;

use App\Enums\ProfitStateEnum;
use App\Filament\Resources\ClientResource;
use App\Models\ClientPromotion;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditClient extends EditRecord
{
    protected static string $resource = ClientResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        ClientPromotion::where('client_id', $this->record->id)
            ->get()
            ->map(function ($client_promotion) {
                if (! $client_promotion->state) {
                    $client_promotion->state = ProfitStateEnum::NOT_PAID->value;
                }
                if (! $client_promotion->rest && $client_promotion->state != ProfitStateEnum::PAID) {
                    $client_promotion->rest = $client_promotion->promotion->selling_price;
                }
                if ($client_promotion->isDirty()) {
                    $client_promotion->save();
                }
            });
    }
}
