<?php

namespace App\Filament\Resources\ProjectResource\RelationManagers;

use App\Enums\InvoiceTypeEnum;
use App\Enums\PaymentStatusEnum;
use App\Filament\Exports\SupplierInvoiceExporter;
use App\Models\Invoice;
use App\Models\Supplier;
use App\Services\InvoiceService;
use Filament\Actions\Exports\Enums\ExportFormat;
use Filament\Forms;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\ExportBulkAction;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class SupplierInvoicesRelationManager extends RelationManager
{
    protected static string $relationship = 'supplier_invoices';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('supplier_id')
                    ->relationship('supplier', 'id')
                    ->getOptionLabelFromRecordUsing(fn (Supplier $record): string => "{$record->first_name} - {$record->last_name}")
                    ->searchable()
                    ->preload(),
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
                    ->columnSpanFull()
                    ->hiddenOn(['view']),
                Forms\Components\Repeater::make('items')
                    ->relationship('items')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required(),
                        Forms\Components\TextInput::make('price')
                            ->required(),
                    ])
                    ->columns(2)
                    ->columnSpanFull()
                    ->hiddenOn(['create', 'edit']),
                SpatieMediaLibraryFileUpload::make('images')
                    ->disk(env('STORAGE_DISK'))
                    ->openable()
                    ->multiple(),
                Forms\Components\Repeater::make('history')
                    ->schema([
                        Forms\Components\TextInput::make('date'),
                        Forms\Components\TextInput::make('amount'),
                    ])
                    ->columns(2)
                    ->columnSpanFull()
                    ->hidden(fn ($record) => ! $record?->history),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('invoicedBy.name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('promotion.fullname')
                    ->default('---')
                    ->searchable(),
                Tables\Columns\TextColumn::make('supplier.name'),
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
                        $data['type'] = InvoiceTypeEnum::SUPPLIER->value;
                        $data['invoiced_by'] = auth()->id();
                        $data['amount'] = 0;

                        return $data;
                    })->using(function (array $data, string $model): Model {
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
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\Action::make('Generate')
                        ->visible(auth()->user()->can('generate_invoice_project'))
                        ->icon('heroicon-o-inbox-arrow-down')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function (Invoice $record, InvoiceService $invoiceService) {
                            return $invoiceService->downloadSupplierInvoice($record);
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
                        ->action(function ($data, $record) {
                            if ($record->amount < $data['amount'] + $record->paid_amount) {
                                return Notification::make()
                                    ->title('The amount is more than the rest of payment.')
                                    ->danger()
                                    ->send();
                            }
                            $record->increment('paid_amount', $data['amount']);
                            $record->history = [
                                ['date' => now()->format('d-m-Y'), 'amount' => $data['amount']],
                                ...($record->history ?? []),
                            ];
                            if ($record->amount == $record->paid_amount) {
                                $record->status = PaymentStatusEnum::PAID->value;
                            }
                            $record->save();

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
                        })->using(function (Model $record, array $data): Model {
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

                            return $record;
                        }),
                    Tables\Actions\DeleteAction::make()
                        ->visible(auth()->user()->can('delete_invoice_project')),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
                ExportBulkAction::make()->exporter(SupplierInvoiceExporter::class)->formats([
                    ExportFormat::Csv,
                ]),
            ]);
    }
}
