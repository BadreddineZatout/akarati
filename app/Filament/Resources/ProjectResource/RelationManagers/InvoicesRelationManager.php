<?php

namespace App\Filament\Resources\ProjectResource\RelationManagers;

use App\Enums\InvoiceTypeEnum;
use App\Models\Invoice;
use App\Models\User;
use App\Services\InvoiceService;
use Filament\Forms;
use Filament\Forms\Components\MorphToSelect;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
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
                    ->label('receiver')
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
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->modifyQueryUsing(fn (Builder $query) => $query->where('type', InvoiceTypeEnum::PROJECT->value))
            ->columns([
                Tables\Columns\TextColumn::make('invoicable.name')
                    ->searchable()
                    ->label('receiver'),
                Tables\Columns\TextColumn::make('invoicable.roles.name')
                    ->label('role'),
                Tables\Columns\TextColumn::make('amount')
                    ->sortable(),
                Tables\Columns\TextColumn::make('invoiced_at')
                    ->sortable()
                    ->date('d-m-Y')
                    ->label('date'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['type'] = InvoiceTypeEnum::PROJECT->value;
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
                        return $invoiceService->downloadInvoice($record);
                    }),
                Tables\Actions\EditAction::make()
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
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
