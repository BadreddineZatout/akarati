<?php

namespace App\Filament\Resources\EmployeeResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Services\WalletService;
use App\Enums\PaymentStatusEnum;
use App\Models\Payment;
use Filament\Tables\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\CreateAction;
use Filament\Resources\RelationManagers\RelationManager;

class PaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'payments';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('project_id')
                    ->relationship('project', 'name', fn ($query) => $query->whereHas('employees', fn ($query) => $query->whereKey($this->ownerRecord->id)))
                    ->preload()
                    ->searchable()
                    ->required(),
                Forms\Components\TextInput::make('amount')
                    ->required()
                    ->maxLength(255),
                Forms\Components\DatePicker::make('paid_at')
                    ->required(),
                Forms\Components\Textarea::make('description'),

            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('amount')
            ->columns([
                Tables\Columns\TextColumn::make('paidBy.name'),
                Tables\Columns\TextColumn::make('project.name'),
                Tables\Columns\TextColumn::make('amount'),
                Tables\Columns\TextColumn::make('paid_at')
                    ->date('d-m-Y'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (PaymentStatusEnum $state): string => PaymentStatusEnum::color($state->value)),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->visible(auth()->user()->can('add_payment_employee'))
                    ->before(function (CreateAction $action, $data) {
                        $wallet = auth()->user()->wallet;
                        if (! $wallet || $wallet->hasEnoughBalance($data['amount'])) {
                            Notification::make()
                                ->danger()
                                ->title('You don\'t have enough balance!')
                                ->send();

                            $action->halt();
                        }
                    })
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['paid_by'] = auth()->id();
                        $data['status'] = PaymentStatusEnum::NOT_PAID->value;

                        return $data;
                    })->after(function(Payment $record, WalletService $walletService){
                        $walletService->subAmount(auth()->user()->wallet, $record->amount);
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Action::make('Paid')
                    ->color('success')
                    ->icon('heroicon-o-check')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => ($record->status != PaymentStatusEnum::PAID) && auth()->user()->can('mark_payment_as_paid_employee'))
                    ->action(function ($record) {
                        $record->status = PaymentStatusEnum::PAID->value;
                        $record->save();

                        return Notification::make()
                            ->title('Payment Updated')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn () => auth()->user()->can('remove_payment_employee'))
                    ->before(function(Payment $record, WalletService $walletService){
                        $walletService->addAmount(auth()->user()->wallet, $record->amount);
                    }),
            ]);
    }
}
