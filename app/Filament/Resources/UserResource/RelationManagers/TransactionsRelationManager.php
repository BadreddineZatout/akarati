<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use App\Enums\TransactionStatusEnum;
use App\Models\Transaction;
use App\Services\TransactionService;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class TransactionsRelationManager extends RelationManager
{
    protected static string $relationship = 'transactions';

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return ! $ownerRecord->hasRole('super_admin');
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('amount')
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('#'),
                Tables\Columns\TextColumn::make('amount'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (TransactionStatusEnum $state): string => TransactionStatusEnum::color($state->value)),
            ])
            ->actions([
                Action::make('Accept')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(fn (Transaction $record, TransactionService $transactionService) => $transactionService->acceptTransaction($record))
                    ->visible(fn ($record) => ($record->status === TransactionStatusEnum::PENDING) && auth()->user()->can('accept_transaction_user')),
                Action::make('Refuse')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(fn (Transaction $record, TransactionService $transactionService) => $transactionService->refuseTransaction($record))
                    ->visible(fn ($record) => ($record->status === TransactionStatusEnum::PENDING) && auth()->user()->can('refuse_transaction_user')),
            ]);
    }
}
