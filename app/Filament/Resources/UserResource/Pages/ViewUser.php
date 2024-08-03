<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Filament\Resources\UserResource\Widgets\WalletBalance;
use App\Services\TransactionService;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\ViewRecord;

class ViewUser extends ViewRecord
{
    protected static string $resource = UserResource::class;

    public function getHeaderWidgets(): array
    {
        return [
            WalletBalance::class,
        ];
    }

    public function getHeaderActions(): array
    {
        return $this->record->wallet ? [
            Action::make('Add Transaction')
                ->form([
                    TextInput::make('amount')
                        ->minValue(1)
                        ->required(),
                ])
                ->action(function (array $data, TransactionService $transactionService) {
                    $transactionService->addTransaction($this->record->wallet->id, $data['amount']);
                })
                ->visible(fn () => auth()->user()->can('add_transaction_user') && $this->record->wallet && $this->record->id != auth()->id()),
        ] : [];
    }
}
