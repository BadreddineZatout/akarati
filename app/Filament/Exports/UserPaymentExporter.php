<?php

namespace App\Filament\Exports;

use App\Models\Payment;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class UserPaymentExporter extends Exporter
{
    protected static ?string $model = Payment::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('#'),
            ExportColumn::make('employee.name'),
            ExportColumn::make('project.name'),
            ExportColumn::make('amount')->suffix(' DA'),
            ExportColumn::make('paid_at'),
            ExportColumn::make('status')
                ->getStateUsing(fn ($record) => $record->status->value),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your user payment export has completed and '.number_format($export->successful_rows).' '.str('row')->plural($export->successful_rows).' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' '.number_format($failedRowsCount).' '.str('row')->plural($failedRowsCount).' failed to export.';
        }

        return $body;
    }
}
