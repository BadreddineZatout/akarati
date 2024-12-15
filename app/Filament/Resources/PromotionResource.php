<?php

namespace App\Filament\Resources;

use App\Enums\PromotionStateEnum;
use App\Filament\Resources\PromotionResource\Pages;
use App\Filament\Resources\PromotionResource\RelationManagers\BillsRelationManager;
use App\Filament\Resources\PromotionResource\RelationManagers\SupplierInvoicesRelationManager;
use App\Models\Promotion;
use App\Models\PromotionType;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PromotionResource extends Resource implements HasShieldPermissions
{
    protected static ?string $slug = 'blocks/promotions';

    protected static ?string $model = Promotion::class;

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getPermissionPrefixes(): array
    {
        return [
            'view',
            'view_any',
            'create',
            'update',
            'delete',
            'delete_any',
            'add_invoice',
            'edit_invoice',
            'delete_invoice',
            'generate_invoice',
        ];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label(__('Name'))
                    ->required(),
                Forms\Components\Select::make('promotion_type_id')
                    ->label(__('Promotion Type'))
                    ->relationship('promotion_type', 'name')
                    ->getOptionLabelUsing(fn ($value) => PromotionType::find($value)?->name)
                    ->options(PromotionType::pluck('name', 'id')->toArray())
                    ->required(),
                Forms\Components\Select::make('state')
                    ->label(__('State'))
                    ->options(array_reduce(PromotionStateEnum::cases(), function ($carry, $state) {
                        $carry[$state->value] = ucfirst(str_replace('_', ' ', $state->name));

                        return $carry;
                    }, []))
                    ->default('not_launched'),

                Forms\Components\TextInput::make('selling_price')
                    ->label(__('Selling Price'))
                    ->required()
                    ->numeric(),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('Name'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('promotion_type.name')
                    ->label(__('Promotion Type'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('block.name')
                    ->label(__('Block'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('state')
                    ->label(__('State'))
                    ->badge()
                    ->color(fn ($record) => PromotionStateEnum::color($record->state)),
                Tables\Columns\TextColumn::make('selling_price')
                    ->label(__('Selling Price'))
                    ->numeric(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Pages\CustomDelete::make()->name('custom_delete_action'),
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
            SupplierInvoicesRelationManager::class,
            BillsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPromotions::route('/'),
            'promotions' => Pages\ListPromotions::route('/{record}'),
            'create' => Pages\CreatePromotion::route('/{block}/create'),
            'edit' => Pages\EditPromotion::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('block_id', request('record'));
    }
}
