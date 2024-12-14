<?php

namespace App\Filament\Resources;

use App\Enums\TransactionStatusEnum;
use App\Filament\Resources\TransactionResource\Pages;
use App\Models\Transaction;
use App\Models\User;
use App\Services\TransactionService;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TransactionResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = Transaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    public static function getNavigationLabel(): string
    {
        return __('Transactions');
    }

    public static function getModelLabel(): string
    {
        return __('Transaction');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Transactions');
    }

    public static function getPermissionPrefixes(): array
    {
        return [
            'view',
            'view_any',
            'create',
            'update',
            'delete',
            'delete_any',
            'accept_transaction',
            'refuse_transaction',
        ];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('wallet_id')
                    ->relationship('wallet', 'id', fn ($query) => $query->whereIn('user_id', User::withoutRole('super_admin')->pluck('id'))->where('user_id', '<>', auth()->id()))
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->user->name)
                    ->preload()
                    ->searchable()
                    ->required(),
                Forms\Components\TextInput::make('amount')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('status')
                    ->hiddenOn(['create', 'edit']),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                TextEntry::make('issuedBy.name')
                    ->label('Sent From'),
                TextEntry::make('wallet.user.name')
                    ->label('User'),
                TextEntry::make('amount'),
                TextEntry::make('status')
                    ->badge()
                    ->color(fn (TransactionStatusEnum $state): string => TransactionStatusEnum::color($state->value)),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('issuedBy.name')
                    ->label('Sent From'),
                Tables\Columns\TextColumn::make('wallet.user.name'),
                Tables\Columns\TextColumn::make('amount')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (TransactionStatusEnum $state): string => TransactionStatusEnum::color($state->value)),
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                    Action::make('Accept')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(fn (Transaction $record, TransactionService $transactionService) => $transactionService->acceptTransaction($record))
                        ->visible(fn ($record) => ($record->status === TransactionStatusEnum::PENDING) && auth()->user()->can('accept_transaction_transaction') && $record->wallet_id === auth()->user()->wallet?->id),
                    Action::make('Refuse')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(fn (Transaction $record, TransactionService $transactionService) => $transactionService->refuseTransaction($record))
                        ->visible(fn ($record) => ($record->status === TransactionStatusEnum::PENDING) && auth()->user()->can('refuse_transaction_transaction') && $record->wallet_id === auth()->user()->wallet?->id),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])->modifyQueryUsing(function (Builder $query) {
                if (auth()->user()->hasRole('super_admin')) {
                    return $query;
                }

                return $query->where('wallet_id', auth()->user()->wallet?->id)
                    ->orWhere('issued_by', auth()->id());
            });
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageTransactions::route('/'),
        ];
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Users Management';
    }
}
