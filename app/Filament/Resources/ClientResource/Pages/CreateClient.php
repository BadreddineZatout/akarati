<?php

namespace App\Filament\Resources\ClientResource\Pages;

use App\Enums\ProfitStateEnum;
use App\Filament\Resources\ClientResource;
use App\Models\ClientPromotion;
use Filament\Resources\Pages\CreateRecord;

class CreateClient extends CreateRecord
{
    protected static string $resource = ClientResource::class;

    protected function afterCreate(): void
    {
        ClientPromotion::where('client_id', $this->record->id)
            ->get()
            ->map(function ($client_promotion) {
                $client_promotion->update([
                    'state' => ProfitStateEnum::NOT_PAID->value,
                    'rest' => $client_promotion->promotion->selling_price,
                ]);
            });
    }
}
