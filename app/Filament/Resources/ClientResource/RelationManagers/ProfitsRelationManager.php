<?php

namespace App\Filament\Resources\ClientResource\RelationManagers;

use App\Enums\ProfitStateEnum;
use App\Filament\Exports\ProfitExporter;
use App\Models\ClientPromotion;
use App\Models\User;
use App\Services\WalletService;
use Filament\Actions\Exports\Enums\ExportFormat;
use Filament\Forms;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Actions\ExportBulkAction;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Spatie\Permission\Models\Role;

class ProfitsRelationManager extends RelationManager
{
    protected static string $relationship = 'profits';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('promotion_id')
                    ->label('Promotion')
                    ->options(fn () => $this->ownerRecord->promotions()->wherePivot('state', ProfitStateEnum::NOT_PAID->value)->get()->pluck('fullname', 'id'))
                    ->required()
                    ->hiddenOn('edit'),
                Forms\Components\DatePicker::make('paid_at')
                    ->required(),
                Forms\Components\Select::make('role')
                    ->live()
                    ->label('Role')
                    ->options(fn () => Role::whereNotIn('name', ['super_admin', 'panel_user'])->pluck('name', 'name'))
                    ->dehydrated(false)
                    ->required()
                    ->hiddenOn('edit'),
                Forms\Components\Select::make('paid_to')
                    ->required()
                    ->label('Paid To')
                    ->placeholder(fn (Get $get): string => empty($get('role')) ? 'First select role' : 'Select an option')
                    ->options(function (Get $get) {
                        return User::role($get('role'))->pluck('name', 'id');
                    })
                    ->hiddenOn('edit'),
                Forms\Components\TextInput::make('amount')
                    ->required()
                    ->numeric(),
                SpatieMediaLibraryFileUpload::make('images')
                    ->disk(env('STORAGE_DISK'))
                    ->openable()
                    ->multiple(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('promotion.fullname'),
                Tables\Columns\TextColumn::make('paidTo.name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('amount'),
                Tables\Columns\TextColumn::make('paid_at')
                    ->date('d-m-Y')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('promotion_id')
                    ->label('Promotion')
                    ->options(fn () => $this->ownerRecord->promotions()->get()->pluck('fullname', 'id')),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->visible(auth()->user()->can('add_profit_client'))
                    ->before(function ($action, $data) {
                        $promotion = ClientPromotion::where([
                            'client_id' => $this->ownerRecord->id,
                            'promotion_id' => $data['promotion_id'],
                        ])->first();
                        if ($data['amount'] > $promotion->rest) {
                            Notification::make()
                                ->danger()
                                ->title('The amount is more than the rest of payment.')
                                ->send();

                            $action->halt();
                        }
                    })
                    ->after(function ($record, WalletService $walletService) {
                        $walletService->addAmount($record->paidTo->wallet, $record->amount);
                        $promotion = ClientPromotion::where([
                            'client_id' => $this->ownerRecord->id,
                            'promotion_id' => $record->promotion_id,
                        ])->first();
                        $promotion->decrement('rest', $record->amount);
                        if ($promotion->rest <= 0) {
                            if ($promotion->rest < 0) {
                                $promotion->rest = 0;
                            }
                            $promotion->state = ProfitStateEnum::PAID->value;
                        }
                        $promotion->save();
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->visible(auth()->user()->can('edit_profit_client'))
                    ->before(function ($action, $record, $data, WalletService $walletService) {
                        if ($record->amount != $data['amount']) {
                            $walletService->subAmount($record->paidTo->wallet, $record->amount);
                            $walletService->addAmount($record->paidTo->wallet, $data['amount']);
                            $promotion = ClientPromotion::where([
                                'client_id' => $this->ownerRecord->id,
                                'promotion_id' => $record->promotion_id,
                            ])->first();

                            $promotion->increment('rest', $record->amount);

                            if ($data['amount'] > $promotion->rest) {
                                Notification::make()
                                    ->danger()
                                    ->title('The amount is more than the rest of payment')
                                    ->send();

                                $action->halt();
                            }
                            $promotion->decrement('rest', $data['amount']);

                            if ($promotion->state == ProfitStateEnum::PAID && $promotion->rest > 0) {
                                $promotion->state = ProfitStateEnum::NOT_PAID;
                            }

                            if ($promotion->rest <= 0) {
                                if ($promotion->rest < 0) {
                                    $promotion->rest = 0;
                                }
                                $promotion->state = ProfitStateEnum::PAID->value;
                            }
                            $promotion->save();
                        }
                    }),
                Tables\Actions\DeleteAction::make()
                    ->visible(auth()->user()->can('delete_profit_client'))
                    ->after(function ($record, WalletService $walletService) {
                        $walletService->subAmount($record->paidTo->wallet, $record->amount);
                        $promotion = ClientPromotion::where([
                            'client_id' => $this->ownerRecord->id,
                            'promotion_id' => $record->promotion_id,
                        ])->first();
                        $promotion->increment('rest', $record->amount);
                        if ($promotion->state == ProfitStateEnum::PAID) {
                            $promotion->state = ProfitStateEnum::NOT_PAID->value;
                        }
                        $promotion->save();
                    }),
            ])->bulkActions([
                ExportBulkAction::make()->exporter(ProfitExporter::class)->formats([
                    ExportFormat::Csv,
                ]),
            ]);
    }
}
