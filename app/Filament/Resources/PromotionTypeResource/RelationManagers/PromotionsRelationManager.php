<?php

namespace App\Filament\Resources\PromotionTypeResource\RelationManagers;

use App\Enums\PromotionStateEnum;
use App\Models\Block;
use App\Models\Promotion;
use App\Models\PromotionType;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PromotionsRelationManager extends RelationManager
{
    protected function canCreate(): bool
    {
        return false;
    }
    protected function canEdit(Model $record): bool
    {
        return false;
    }

    protected static string $relationship = 'promotions';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('promotion_type_id')
                    ->label(__('Promotion Type'))
                    ->relationship('promotion_type', 'name')
                    ->getOptionLabelUsing(fn ($value) => PromotionType::find($value)?->name)
                    ->options(PromotionType::pluck('name', 'id')->toArray())
                    ->required(),
                Forms\Components\Select::make('block_id')
                    ->label(__('Block'))
                    ->relationship('block', 'name')
                    ->getOptionLabelUsing(fn ($value) => Block::find($value)?->name)
                    ->options(Block::pluck('name', 'id')->toArray())
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
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('promotion_type.name'),
                Tables\Columns\TextColumn::make('block_name'),
                Tables\Columns\TextColumn::make('state')
                    ->badge()
                    ->color(fn ($record) => PromotionStateEnum::color($record->state)),
                Tables\Columns\TextColumn::make('selling_price'),
                Tables\Columns\TextColumn::make('created_at')
                    ->date('d-m-Y'),
                Tables\Columns\TextColumn::make('updated_at')
                    ->date('d-m-Y'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
