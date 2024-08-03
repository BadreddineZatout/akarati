<?php

namespace App\Filament\Resources;

use App\Enums\PromotionStateEnum;
use App\Filament\Resources\PromotionResource\Pages;
use App\Filament\Resources\PromotionResource\RelationManagers;
use App\Models\Block;
use App\Models\Promotion;
use App\Models\PromotionType;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

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
                //
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPromotions::route('/'),
            'promotions' => Pages\ListPromotions::route('/{record}'),
            'create' => Pages\CreatePromotion::route('/{record}/create'),
            'edit' => Pages\EditPromotion::route('/{record}/edit'),


        ];
    }
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('block_id', request('record'));
    }
}
