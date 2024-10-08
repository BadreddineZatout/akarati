<?php

namespace App\Filament\Resources\ClientResource\RelationManagers;

use Filament\Forms;
use App\Models\User;
use Filament\Tables;
use Filament\Forms\Get;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Enums\ProfitStateEnum;
use App\Models\ClientPromotion;
use App\Services\WalletService;
use Spatie\Permission\Models\Role;
use Filament\Notifications\Notification;
use Filament\Tables\Filters\SelectFilter;
use Filament\Resources\RelationManagers\RelationManager;

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
                    ->before(function ($action, $data) {
                        $promotion = ClientPromotion::where([
                            'client_id' => $this->ownerRecord->id,
                            'promotion_id' => $data['promotion_id'],
                        ])->first();
                        if ($data['amount'] > $promotion->rest) {
                            Notification::make()
                                ->danger()
                                ->title('The amount is more than the rest of payment')
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
            ]);
    }
}
