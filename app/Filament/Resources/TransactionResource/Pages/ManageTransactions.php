<?php

namespace App\Filament\Resources\TransactionResource\Pages;

use App\Enums\TransactionStatusEnum;
use App\Filament\Resources\TransactionResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageTransactions extends ManageRecords
{
    protected static string $resource = TransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->mutateFormDataUsing(function (array $data): array {
                    $data['issued_by'] = auth()->id();
                    $data['status'] = TransactionStatusEnum::PENDING;

                    return $data;
                }),
        ];
    }
}
