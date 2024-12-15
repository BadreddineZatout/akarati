<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClientResource\Pages;
use App\Filament\Resources\ClientResource\RelationManagers\ProfitsRelationManager;
use App\Models\Client;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ClientResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = Client::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    public static function getNavigationLabel(): string
    {
        return __('Clients');
    }

    public static function getModelLabel(): string
    {
        return __('Client');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Clients');
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
            'add_profit',
            'edit_profit',
            'delete_profit',
        ];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('first_name')
                    ->label(__('First Name'))
                    ->required(),
                Forms\Components\TextInput::make('last_name')
                    ->label(__('Last Name'))
                    ->required(),
                Forms\Components\TextInput::make('phone')
                    ->label(__('Phone number'))
                    ->tel()
                    ->required(),
                Forms\Components\TextInput::make('email')
                    ->label(__('Email')),
                Forms\Components\TextInput::make('address')
                    ->label(__('Address')),
                Forms\Components\DatePicker::make('birthday')
                    ->label(__('Birthday')),
                Forms\Components\Select::make('promotions')
                    ->label(__('Promotions'))
                    ->relationship('promotions', 'name')
                    ->required()
                    ->preload()
                    ->multiple()
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->block->project->name.'-'.$record->block->name.'-'.$record->name)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('first_name')
                    ->label(__('First Name'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('last_name')
                    ->label(__('Last Name'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone')
                    ->label(__('Phone Number'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->label(__('Email'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('address')
                    ->label(__('Address'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('birthday')
                    ->label(__('Birthday'))
                    ->date(),
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
            ProfitsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListClients::route('/'),
            'create' => Pages\CreateClient::route('/create'),
            'edit' => Pages\EditClient::route('/{record}/edit'),
            'view' => Pages\ViewClient::route('/{record}'),

        ];
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Users Management';
    }
}
