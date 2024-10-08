<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use App\Models\Promotion;
use Filament\Tables\Table;
use App\Models\PromotionType;
use Filament\Resources\Resource;
use App\Enums\PromotionStateEnum;
use Filament\Tables\Actions\Action;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\PromotionResource\Pages;
use App\Filament\Resources\PromotionResource\RelationManagers\BillsRelationManager;
use App\Filament\Resources\PromotionResource\RelationManagers\InvoicesRelationManager;
use App\Filament\Resources\PromotionResource\RelationManagers\SupplierInvoicesRelationManager;

class PromotionResource extends Resource
{
    protected static ?string $slug = 'blocks/promotions';

    protected static ?string $model = Promotion::class;

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required(),
                Forms\Components\Select::make('promotion_type_id')
                    ->label(__('Promotion Type'))
                    ->relationship('promotion_type', 'name')
                    ->getOptionLabelUsing(fn ($value) => PromotionType::find($value)?->name)
                    ->options(PromotionType::pluck('name', 'id')->toArray())
                    ->required(),
                Forms\Components\Select::make('state')
                    ->label('State')
                    ->options(array_reduce(PromotionStateEnum::cases(), function ($carry, $state) {
                        $carry[$state->value] = ucfirst(str_replace('_', ' ', $state->name));

                        return $carry;
                    }, []))
                    ->default('not_launched'),

                Forms\Components\TextInput::make('selling_price')
                    ->required()
                    ->numeric(),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('promotion_type.name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('block.name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('state')
                    ->badge()
                    ->color(fn ($record) => PromotionStateEnum::color($record->state)),
                Tables\Columns\TextColumn::make('selling_price')
                    ->numeric(),
            ])
            ->filters([
                //
            ])
            ->actions([
                //                Action::make('sell_promotion')
                //                    ->url(fn (Promotion $record): string => PromotionResource::getUrl('sell', ['record' => $record]))->icon('heroicon-o-shopping-bag')
                //                    ->color('primary'),
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
            InvoicesRelationManager::class,
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
