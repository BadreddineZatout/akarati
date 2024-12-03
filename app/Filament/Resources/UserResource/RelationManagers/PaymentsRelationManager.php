<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use App\Enums\PaymentStatusEnum;
use App\Filament\Exports\UserPaymentExporter;
use Filament\Actions\Exports\Enums\ExportFormat;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Actions\ExportBulkAction;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class PaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'payments';

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return ! $ownerRecord->hasRole('super_admin');
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('#'),
                Tables\Columns\TextColumn::make('employee.name')
                    ->searchable(['first_name', 'last_name']),
                Tables\Columns\TextColumn::make('project.name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('amount'),
                Tables\Columns\TextColumn::make('paid_at')
                    ->date('d-m-Y'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (PaymentStatusEnum $state): string => PaymentStatusEnum::color($state->value)),
            ])->bulkActions([
                ExportBulkAction::make()->exporter(UserPaymentExporter::class)->formats([
                    ExportFormat::Csv,
                ]),
            ]);
    }
}
