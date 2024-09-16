<?php

namespace App\Filament\Resources\ProjectResource\RelationManagers;

use Filament\Forms;
use App\Models\User;
use Filament\Tables;
use App\Models\Invoice;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Enums\InvoiceTypeEnum;
use App\Services\WalletService;
use App\Services\InvoiceService;
use Illuminate\Database\Eloquent\Model;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\CreateAction;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\MorphToSelect;
use Filament\Resources\RelationManagers\RelationManager;

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
                    ->label('User'),
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
                    })->before(function(CreateAction $action, $data): void{
                        $amount = array_sum(array_map(fn($item) => $item['price'],$data['items']));
                        $wallet = User::find($data['invoicable_id'])->wallet;
                        if (!$wallet || !$wallet->hasEnoughBalance($amount)) {
                            Notification::make()
                                ->danger()
                                ->title('You don\'t have enough balance!')
                                ->send();

                            $action->halt();
                        }
                    })
                    ->using(function (array $data, string $model, WalletService $walletService): Model {
                        $items = $data['items'];
                        unset($data['items']);
                        $invoice = $this->ownerRecord->invoices()->create($data);
                        foreach ($items as $item) {
                            $item = $invoice->items()->create($item);
                            $invoice->increment('amount', $item->price);
                        }

                        $walletService->subAmount($invoice->invoicable->wallet, $invoice->amount);

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
                ->after(function ($record, WalletService $walletService){
                    $walletService->addAmount($record->invoicable->wallet, $record->amount);
                }),
            ]);
    }
}
