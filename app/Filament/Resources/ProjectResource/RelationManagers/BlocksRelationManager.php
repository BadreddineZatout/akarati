<?php

namespace App\Filament\Resources\ProjectResource\RelationManagers;

use App\Enums\BlockStatusEnum;
use App\Filament\Resources\PromotionResource;
use App\Models\Block;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class BlocksRelationManager extends RelationManager
{
    protected static string $relationship = 'blocks';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('state')
                    ->label('State')
                    ->options(array_reduce(BlockStatusEnum::cases(), function ($carry, $state) {
                        $carry[$state->value] = ucfirst(str_replace('_', ' ', $state->name));

                        return $carry;
                    }, []))
                    ->default('not_launched'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('project.name'),
                Tables\Columns\TextColumn::make('state')
                    ->badge()
                    ->color(fn ($record) => BlockStatusEnum::color($record->state)),
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
                Tables\Actions\Action::make('View Promotions')
                    ->color('success')
                    ->icon('heroicon-o-home')
                    ->url(fn (Block $record): string => PromotionResource::getUrl('promotions', ['record' => $record])),

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
