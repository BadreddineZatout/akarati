<?php

namespace App\Filament\Resources\ProjectResource\RelationManagers;

use App\Enums\InvoiceTypeEnum;
use App\Enums\PaymentStatusEnum;
use App\Models\Invoice;
use App\Models\User;
use App\Services\InvoiceService;
use App\Services\WalletService;
use Filament\Forms;
use Filament\Forms\Components\MorphToSelect;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class InvoicesRelationManager extends RelationManager
{
    protected static string $relationship = 'invoices';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\MorphToSelect::make('invoicable')
                    ->label('User')
                    ->types([
                        MorphToSelect\Type::make(User::class)
                            ->titleAttribute('name'),
                    ])
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\DatePicker::make('invoiced_at')
                    ->required()
                    ->label('date'),
                Forms\Components\Repeater::make('items')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required(),
                        Forms\Components\TextInput::make('price')
                            ->required(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
                SpatieMediaLibraryFileUpload::make('images')
                    ->multiple(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->modifyQueryUsing(fn (Builder $query) => $query->where('type', InvoiceTypeEnum::PROJECT->value))
            ->columns([
                Tables\Columns\TextColumn::make('promotion.fullname')
                    ->default('---')
                    ->searchable(),
                Tables\Columns\TextColumn::make('invoicable.name')
                    ->searchable()
                    ->label('User'),
                Tables\Columns\TextColumn::make('invoicable.roles.name')
                    ->label('role'),
                Tables\Columns\TextColumn::make('amount')
                    ->sortable(),
                Tables\Columns\TextColumn::make('reste')
                    ->getStateusing(fn ($record) => $record->amount - $record->paid_amount)
                    ->suffix(' DA'),
                Tables\Columns\TextColumn::make('invoiced_at')
                    ->sortable()
                    ->date('d-m-Y')
                    ->label('date'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (PaymentStatusEnum $state): string => PaymentStatusEnum::color($state->value)),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->visible(auth()->user()->can('add_invoice_project'))
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['type'] = InvoiceTypeEnum::PROJECT->value;
                        $data['amount'] = 0;

                        return $data;
                    })
                    ->using(function (array $data, string $model): Model {
                        $items = $data['items'];
                        unset($data['items']);
                        $invoice = $this->ownerRecord->invoices()->create($data);
                        foreach ($items as $item) {
                            $item = $invoice->items()->create($item);
                            $invoice->increment('amount', $item->price);
                        }

                        return $invoice;
                    }),
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\Action::make('Generate')
                        ->visible(auth()->user()->can('generate_invoice_project'))
                        ->icon('heroicon-o-inbox-arrow-down')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function (Invoice $record, InvoiceService $invoiceService) {
                            return $invoiceService->downloadInvoice($record);
                        }),
                    Tables\Actions\Action::make('Pay')
                        ->color('success')
                        ->icon('heroicon-o-check')
                        ->requiresConfirmation()
                        ->visible(fn ($record) => ($record->status != PaymentStatusEnum::PAID) && auth()->user()->can('pay_invoice_project'))
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
                            if ($record->amount == $record->paid_amount) {
                                $record->status = PaymentStatusEnum::PAID->value;
                            }
                            $record->save();

                            $walletService->subAmount($record->invoicable->wallet, $data['amount']);

                            return Notification::make()
                                ->title('Invoice Updated')
                                ->success()
                                ->send();
                        }),
                    Tables\Actions\EditAction::make()
                        ->visible(auth()->user()->can('edit_invoice_project'))
                        ->mutateRecordDataUsing(function (array $data, $record): array {
                            $data['items'] = $record->items->map(fn ($item) => ['name' => $item->name, 'price' => $item->price]);

                            return $data;
                        })->using(function (Model $record, array $data, WalletService $walletService): Model {
                            $items = $data['items'];
                            unset($data['items']);
                            $record->update($data);

                            $record->items()->delete();
                            $record->amount = 0;
                            $record->save();
                            foreach ($items as $item) {
                                $item = $record->items()->create($item);
                                $record->increment('amount', $item->price);
                            }

                            $walletService->subAmount($record->invoicable->wallet, $record->amount);

                            return $record;
                        }),
                    Tables\Actions\DeleteAction::make()
                        ->visible(auth()->user()->can('delete_invoice_project'))
                        ->after(function ($record, WalletService $walletService) {
                            $walletService->addAmount($record->invoicable->wallet, $record->amount);
                        }),
                ]),
            ]);
    }
}
