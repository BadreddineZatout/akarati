<?php

namespace App\Filament\Resources\SupplierResource\RelationManagers;

use App\Enums\InvoiceTypeEnum;
use App\Filament\Exports\UserInvoiceExporter;
use App\Models\Invoice;
use App\Services\InvoiceService;
use Filament\Actions\Exports\Enums\ExportFormat;
use Filament\Forms;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Actions\ExportBulkAction;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class InvoicesRelationManager extends RelationManager
{
    protected static string $relationship = 'invoices';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('project_id')
                    ->label('Project')
                    ->relationship('project', 'name'),
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
                    ->disk(env('STORAGE_DISK'))
                    ->multiple(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('#'),
                Tables\Columns\TextColumn::make('project.name')
                    ->label('Project'),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Sum of owned total')
                    ->sortable(),
                Tables\Columns\TextColumn::make('invoiced_at')
                    ->sortable()
                    ->date('d-m-Y')
                    ->label('date'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->visible(auth()->user()->can('add_invoice_project'))
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['type'] = InvoiceTypeEnum::SUPPLIER->value;
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
                Tables\Actions\Action::make('Generate')
                    ->icon('heroicon-o-inbox-arrow-down')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (Invoice $record, InvoiceService $invoiceService) {
                        return $invoiceService->downloadSupplierInvoice($record);
                    }),
            ])->bulkActions([
                ExportBulkAction::make()->exporter(UserInvoiceExporter::class)->formats([
                    ExportFormat::Csv,
                ]),
            ]);
    }
}
