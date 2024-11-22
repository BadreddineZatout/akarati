<?php

namespace App\Filament\Exports;

use App\Models\Profit;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class UserProfitExporter extends Exporter
{
    protected static ?string $model = Profit::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('#'),
            ExportColumn::make('client.fullname'),
            ExportColumn::make('promotion.fullname'),
            ExportColumn::make('amount')->suffix(' DA'),
            ExportColumn::make('paid_at'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your user profit export has completed and '.number_format($export->successful_rows).' '.str('row')->plural($export->successful_rows).' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' '.number_format($failedRowsCount).' '.str('row')->plural($failedRowsCount).' failed to export.';
        }

        return $body;
    }
}