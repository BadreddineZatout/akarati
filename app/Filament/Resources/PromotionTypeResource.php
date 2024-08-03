<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PromotionTypeResource\Pages;
use App\Filament\Resources\PromotionTypeResource\RelationManagers;
use App\Models\PromotionType;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PromotionTypeResource extends Resource
{
    protected static ?string $model = PromotionType::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('created_at')
                    ->date('d-m-Y'),
                Tables\Columns\TextColumn::make('updated_at')
                    ->date('d-m-Y'),
            ])
            ->filters([
                //
            ])
            ->actions([

                Tables\Actions\ViewAction::make(),
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
            RelationManagers\PromotionsRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPromotionTypes::route('/'),
//            'create' => Pages\CreatePromotionType::route('/create'),
//            'edit' => Pages\EditPromotionType::route('/{record}/edit'),
            'view' => Pages\ViewPromotionType::route('/{record}'),

        ];
    }
    public static function getNavigationGroup(): ?string
    {
        return 'Projects Management';
    }
}
