<?php

namespace App\Filament\Resources\ProjectResource\RelationManagers;

use App\Enums\PaymentStatusEnum;
use App\Filament\Exports\PaymentExporter;
use App\Models\Payment;
use App\Services\WalletService;
use Filament\Actions\Exports\Enums\ExportFormat;
use Filament\Forms;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ExportBulkAction;
use Filament\Tables\Table;

class PaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'payments';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('employee_id')
                    ->label('Employee')
                    ->relationship('employee', 'id', fn ($query) => $query->whereHas('projects', fn ($query) => $query->whereKey($this->ownerRecord->id)))
                    ->getOptionLabelFromRecordUsing(fn ($record) => "$record->first_name $record->last_name")
                    ->preload()
                    ->searchable()
                    ->required(),
                Forms\Components\TextInput::make('amount')
                    ->required()
                    ->maxLength(255),
                Forms\Components\DatePicker::make('paid_at')
                    ->required(),
                Forms\Components\Textarea::make('description'),
                SpatieMediaLibraryFileUpload::make('images')
                    ->disk(env('STORAGE_DISK'))
                    ->multiple(),

            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('amount')
            ->columns([
                Tables\Columns\TextColumn::make('paidBy.name'),
                Tables\Columns\TextColumn::make('employee.name'),
                Tables\Columns\TextColumn::make('amount')->suffix(' DA'),
                Tables\Columns\TextColumn::make('reste')
                    ->getStateusing(fn ($record) => $record->amount - $record->paid_amount)
                    ->suffix(' DA'),
                Tables\Columns\TextColumn::make('paid_at')
                    ->date('d-m-Y'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (PaymentStatusEnum $state): string => PaymentStatusEnum::color($state->value)),
            ])
            ->filters([
                //
            ])
            ->bulkActions([
                ExportBulkAction::make()->exporter(PaymentExporter::class)->formats([
                    ExportFormat::Csv,
                ]),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->visible(auth()->user()->can('add_payment_employee'))
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['paid_by'] = auth()->id();
                        $data['status'] = PaymentStatusEnum::NOT_PAID->value;

                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Action::make('Pay')
                    ->color('success')
                    ->icon('heroicon-o-check')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => ($record->status != PaymentStatusEnum::PAID) && auth()->user()->can('mark_payment_as_paid_employee'))
                    ->form([
                        TextInput::make('amount')
                            ->label('Amount')
                            ->numeric()
                            ->minValue(0)
                            ->required(),
                    ])
                    ->action(function ($data, $record, WalletService $walletService) {
                        if ($record->amount < $data['amount'] + $record->paid_amount) {
                            return Notification::make()
                                ->title('The amount is more than the rest of payment.')
                                ->danger()
                                ->send();
                        }
                        $record->increment('paid_amount', $data['amount']);
                        $record->paid_at = now();
                        if ($record->amount == $record->paid_amount) {
                            $record->status = PaymentStatusEnum::PAID->value;
                        }
                        $record->save();

                        $walletService->subAmount(auth()->user()->wallet, $data['amount']);

                        return Notification::make()
                            ->title('Payment Updated')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn () => auth()->user()->can('remove_payment_employee'))
                    ->before(function (Payment $record, WalletService $walletService) {
                        $walletService->addAmount(auth()->user()->wallet, $record->amount);
                    }),
            ]);
    }
}
