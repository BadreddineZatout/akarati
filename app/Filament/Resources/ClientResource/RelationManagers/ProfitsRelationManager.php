<?php

namespace App\Filament\Resources\ClientResource\RelationManagers;

use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Spatie\Permission\Models\Role;

class ProfitsRelationManager extends RelationManager
{
    protected static string $relationship = 'profits';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('promotion_id')
                    ->label('Promotion')
                    ->options(fn () => $this->ownerRecord->promotions()->get()->pluck('fullname', 'id'))
                    ->required(),
                Forms\Components\DatePicker::make('paid_at'),
                Forms\Components\Select::make('role')
                    ->live()
                    ->label('Role')
                    ->options(fn () => Role::whereNotIn('name', ['super_admin', 'panel_user'])->pluck('name', 'name'))
                    ->dehydrated(false)
                    ->required(),
                Forms\Components\Select::make('paid_to')
                    ->required()
                    ->label('Paid To')
                    ->placeholder(fn (Get $get): string => empty($get('role')) ? 'First select role' : 'Select an option')
                    ->options(function (Get $get) {
                        return User::role($get('role'))->pluck('name', 'id');
                    }),
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
                Tables\Columns\TextColumn::make('paidTo.name'),
                Tables\Columns\TextColumn::make('amount'),
                Tables\Columns\TextColumn::make('paid_at')
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
