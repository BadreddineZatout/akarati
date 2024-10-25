<?php

namespace App\Filament\Resources\SupplierResource\RelationManagers;

use App\Filament\Exports\UserInvoiceExporter;
use App\Models\Invoice;
use App\Services\InvoiceService;
use Filament\Actions\Exports\Enums\ExportFormat;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Actions\ExportBulkAction;
use Filament\Tables\Table;

class InvoicesRelationManager extends RelationManager
{
    protected static string $relationship = 'invoices';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('id')
                    ->required()
                    ->maxLength(255),
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
                    ->label('receiver'),
                Tables\Columns\TextColumn::make('amount')
                    ->sortable(),
                Tables\Columns\TextColumn::make('invoiced_at')
                    ->sortable()
                    ->date('d-m-Y')
                    ->label('date'),
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
                ExportBulkAction::make()->exporter(UserInvoiceExporter::class) ->formats([
                    ExportFormat::Csv,
                ]),
            ]);
    }
}
