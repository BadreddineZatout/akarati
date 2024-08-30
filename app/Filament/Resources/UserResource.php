<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers\InvoicesRelationManager;
use App\Filament\Resources\UserResource\RelationManagers\PaymentsRelationManager;
use App\Filament\Resources\UserResource\RelationManagers\ProfitsRelationManager;
use App\Filament\Resources\UserResource\RelationManagers\TransactionsRelationManager;
use App\Models\User;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user';

    public static function getNavigationBadge(): ?string
    {
        return auth()->user()->wallet?->pendingTransactionsCount;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
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
            'add_transaction',
            'accept_transaction',
            'refuse_transaction',
        ];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required(),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->unique(),
                Forms\Components\TextInput::make('password')
                    ->required()
                    ->password()
                    ->maxLength(255)
                    ->rule(Password::default())
                    ->hiddenOn('view'),
                Select::make('roles')
                    ->label('Role')
                    ->required()
                    ->relationship('roles', 'name'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('avatar'),
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('roles.name')
                    ->badge(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime(),
            ])
            ->filters([

                SelectFilter::make('role')
                    ->relationship('roles', 'name'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                //                Action::make('changePassword')
                //                    ->action(function (User $record, array $data): void {
                //                        $record->update([
                //                            'password' => Hash::make($data['new_password']),
                //                        ]);
                //
                //                        Filament::notify('success', 'Password changed successfully.');
                //                    })
                //                    ->form([
                //                        Forms\Components\TextInput::make('new_password')
                //                            ->password()
                //                            ->label('New Password')
                //                            ->required()
                //                            ->rule(Password::default()),
                //                        Forms\Components\TextInput::make('new_password_confirmation')
                //                            ->password()
                //                            ->label('Confirm New Password')
                //                            ->rule('required', fn($get) => ! ! $get('new_password'))
                //                            ->same('new_password'),
                //                    ])
                //                    ->icon('heroicon-o-key'),
                Action::make('deactivate')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->icon('heroicon-o-trash')
                    ->action(fn (User $record) => $record->delete()),
            ])
            ->bulkActions([

                Tables\Actions\DeleteBulkAction::make()
                    ->requiresConfirmation(),

            ]);
    }

    public static function getRelations(): array
    {
        return [
            TransactionsRelationManager::class,
            InvoicesRelationManager::class,
            PaymentsRelationManager::class,
            ProfitsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            //            'edit' => Pages\EditUser::route('/{record}/edit'),
            'view' => Pages\ViewUser::route('/{record}'),

        ];
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Users Management';
    }
}
