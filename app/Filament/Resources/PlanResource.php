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
                Forms\Components\TextInput::make('name')->label('Name')->required(),
                Forms\Components\TextInput::make('slug')->required(),
                Forms\Components\TextInput::make('description')->label('description'),
                Forms\Components\Select::make('is_active')->label('State')
                    ->options([
                        0 => 'Disabled',
                        1 => 'Active',
                    ])->default(1)->required(),
                Forms\Components\TextInput::make('price')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('signup_fee')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('currency')
                    ->required()
                    ->default('DA'),
                Forms\Components\TextInput::make('trial_period')
                    ->numeric()
                    ->default(0),
                Forms\Components\Select::make('trial_interval')
                    ->options([
                        'day' => 'Day',
                        'month' => 'Month',
                    ])->default('day'),
                Forms\Components\TextInput::make('invoice_period')
                    ->numeric()
                    ->default(0),
                Forms\Components\Select::make('trial_interval')
                    ->options([
                        'day' => 'Day',
                        'month' => 'Month',
                        'year' => 'Year',
                    ])->default('month'),
                Forms\Components\TextInput::make('grace_period')
                    ->numeric()
                    ->default(0),
                Forms\Components\Select::make('grace_interval')
                    ->options([
                        'day' => 'Day',
                        'month' => 'Month',
                        'year' => 'Year',
                    ])->default('day'),
                Forms\Components\TextInput::make('prorate_day')
                    ->numeric(),
                Forms\Components\TextInput::make('prorate_period')
                    ->numeric(),
                Forms\Components\TextInput::make('prorate_extend_due')
                    ->numeric(),
                Forms\Components\TextInput::make('active_subscribers_limit')
                    ->numeric(),
                Forms\Components\TextInput::make('sort_order')
                    ->numeric()->default(0),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('slug'),
                Tables\Columns\TextColumn::make('description'),

                Tables\Columns\TextColumn::make('is_active')
                    ->label('State')
                    ->badge()
                    ->color(fn ($record) => PlanStateEnum::color($record->is_active))
                    ->formatStateUsing(fn ($state): string => PlanStateEnum::text($state)),
                Tables\Columns\TextColumn::make('price'),
                Tables\Columns\TextColumn::make('signup_fee'),
                Tables\Columns\TextColumn::make('currency'),
                Tables\Columns\TextColumn::make('created_at'),

            ])
            ->filters([
                //
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
