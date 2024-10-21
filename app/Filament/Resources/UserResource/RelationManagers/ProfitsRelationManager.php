<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ProfitsRelationManager extends RelationManager
{
    protected static string $relationship = 'profits';

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return ! $ownerRecord->hasRole('super_admin');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('id')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('#'),
                Tables\Columns\TextColumn::make('client.fullname'),
                Tables\Columns\TextColumn::make('promotion.fullname'),
                Tables\Columns\TextColumn::make('amount'),
                Tables\Columns\TextColumn::make('paid_at')
                    ->date('d-m-Y')
                    ->sortable(),
            ]);
    }
}
