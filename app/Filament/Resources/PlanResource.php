<?php

namespace App\Filament\Resources;

use App\Enums\PlanStateEnum;
use App\Filament\Resources\PlanResource\Pages;
use App\Filament\Resources\PlanResource\RelationManagers;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Laravelcm\Subscriptions\Models\Plan;

class PlanResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = Plan::class;

    protected static ?string $navigationIcon = 'heroicon-o-queue-list';

    public static function getNavigationLabel(): string
    {
        return __('Plans');
    }

    public static function getModelLabel(): string
    {
        return __('Plan');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Plans');
    }

    public static function getPermissionPrefixes(): array
    {
        return [
            'view',
            'view_any',
            'create',
            'update',
            'delete',
            'delete_any',
        ];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')->label(__('Name'))->required(),
                Forms\Components\TextInput::make('slug')->label(__('Slug'))->required(),
                Forms\Components\TextInput::make('description')->label('description')
                    ->label(__('Description')),
                Forms\Components\Select::make('is_active')
                    ->label(__('State'))
                    ->options([
                        0 => 'Disabled',
                        1 => 'Active',
                    ])->default(1)->required(),
                Forms\Components\TextInput::make('price')
                    ->label(__('Price'))
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('signup_fee')
                    ->label(__('Signup Fee'))
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('currency')
                    ->label(__('Currency'))
                    ->required()
                    ->default('DA'),
                Forms\Components\TextInput::make('trial_period')
                    ->label(__('Trial Period'))
                    ->numeric()
                    ->default(0),
                Forms\Components\Select::make('trial_interval')
                    ->label(__('Trial Interval'))
                    ->options([
                        'day' => 'Day',
                        'month' => 'Month',
                    ])->default('day'),
                Forms\Components\TextInput::make('invoice_period')
                    ->label(__('Invoice Period'))
                    ->numeric()
                    ->default(0),
                Forms\Components\Select::make('invoice_interval')
                    ->label(__('Invoice Interval'))
                    ->options([
                        'day' => 'Day',
                        'month' => 'Month',
                        'year' => 'Year',
                    ])->default('month'),
                Forms\Components\TextInput::make('grace_period')
                    ->label(__('Grace Period'))
                    ->numeric()
                    ->default(0),
                Forms\Components\Select::make('grace_interval')
                    ->label(__('Grace Interval'))
                    ->options([
                        'day' => 'Day',
                        'month' => 'Month',
                        'year' => 'Year',
                    ])->default('day'),
                Forms\Components\TextInput::make('prorate_day')
                    ->label(__('Prorate Day'))
                    ->numeric(),
                Forms\Components\TextInput::make('prorate_period')
                    ->label(__('Prorate Period'))
                    ->numeric(),
                Forms\Components\TextInput::make('prorate_extend_due')
                    ->label(__('Prorate Extend Due'))
                    ->numeric(),
                Forms\Components\TextInput::make('active_subscribers_limit')
                    ->label(__('Active Subscribers Limit'))
                    ->numeric(),
                Forms\Components\TextInput::make('sort_order')
                    ->label(__('Sort Order'))
                    ->numeric()->default(0),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('Name')),
                Tables\Columns\TextColumn::make('slug')
                    ->label(__('Slug')),
                Tables\Columns\TextColumn::make('description')
                    ->label(__('Description')),

                Tables\Columns\TextColumn::make('is_active')
                    ->label(__('State'))
                    ->label('State')
                    ->badge()
                    ->color(fn ($record) => PlanStateEnum::color($record->is_active))
                    ->formatStateUsing(fn ($state): string => PlanStateEnum::text($state)),
                Tables\Columns\TextColumn::make('price')
                    ->label(__('Price')),
                Tables\Columns\TextColumn::make('signup_fee')
                    ->label(__('Signup Fee')),
                Tables\Columns\TextColumn::make('currency')
                    ->label(__('Currency')),

            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\FeaturesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPlans::route('/'),
            'create' => Pages\CreatePlan::route('/create'),
            'view' => Pages\ViewPlan::route('/{record}'),
            'edit' => Pages\EditPlan::route('/{record}/edit'),
        ];
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Subscriptions Management';
    }
}
