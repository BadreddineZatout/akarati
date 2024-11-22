<?php

namespace App\Filament\Exports;

use App\Models\Invoice;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class BillExporter extends Exporter
{
    protected static ?string $model = Invoice::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('#'),
            ExportColumn::make('promotion.fullname'),
            ExportColumn::make('amount')->suffix(' DA'),
            ExportColumn::make('reste')->getStateusing(fn ($record) => $record->amount - $record->paid_amount)->suffix(' DA'),
            ExportColumn::make('invoiced_at'),
            ExportColumn::make('status')
                ->getStateUsing(fn ($record) => $record->status->value),
            ExportColumn::make('comment'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your bill export has completed and '.number_format($export->successful_rows).' '.str('row')->plural($export->successful_rows).' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' '.number_format($failedRowsCount).' '.str('row')->plural($failedRowsCount).' failed to export.';
        }

        return $body;
    }
}
