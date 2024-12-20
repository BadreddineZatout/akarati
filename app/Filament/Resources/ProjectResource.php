<?php

namespace App\Filament\Resources;

use App\Enums\ProjectStatusEnum;
use App\Filament\Resources\ProjectResource\Pages;
use App\Filament\Resources\ProjectResource\RelationManagers\BillsRelationManager;
use App\Filament\Resources\ProjectResource\RelationManagers\BlocksRelationManager;
use App\Filament\Resources\ProjectResource\RelationManagers\PaymentsRelationManager;
use App\Filament\Resources\ProjectResource\RelationManagers\SupplierInvoicesRelationManager;
use App\Models\Project;
use App\Models\User;
use App\Services\InvoiceService;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ProjectResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = Project::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    public static function getNavigationLabel(): string
    {
        return __('Projects');
    }

    public static function getModelLabel(): string
    {
        return __('Project');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Projects');
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
            'add_invoice',
            'edit_invoice',
            'delete_invoice',
            'generate_invoice',
            'pay_invoice',
        ];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label(__('Name'))
                    ->required(),
                Forms\Components\Select::make('status')
                    ->label(__('Status'))
                    ->options(array_reduce(ProjectStatusEnum::cases(), function ($carry, $state) {
                        $carry[$state->value] = ucfirst(str_replace('_', ' ', $state->name));

                        return $carry;
                    }, []))
                    ->default('not_launched'),
                Forms\Components\Select::make('promoter_id')
                    ->label(__('Promoter'))
                    ->relationship('promoter', 'name')
                    ->getOptionLabelUsing(fn ($value) => User::find($value)?->name)
                    ->options(User::role('promoteur')->pluck('name', 'id')->toArray())
                    ->required()
                    ->hidden(auth()->user()->hasRole('promoteur')),
                Forms\Components\Select::make('chef_id')
                    ->label(__('Chef'))
                    ->relationship('chef', 'name')
                    ->getOptionLabelUsing(fn ($value) => User::find($value)?->name)
                    ->options(User::role('chef chantier')->pluck('name', 'id')->toArray())
                    ->required()
                    ->hidden(auth()->user()->hasRole('chef chantier')),
                Forms\Components\Select::make('accountant_id')
                    ->label(__('Accountant'))
                    ->relationship('accountant', 'name')
                    ->getOptionLabelUsing(fn ($value) => User::find($value)?->name)
                    ->options(User::role('comptable')->pluck('name', 'id')->toArray())
                    ->required(),
                Forms\Components\DatePicker::make('started_at')
                    ->label(__('Starts At')),
                Forms\Components\DatePicker::make('ended_at')
                    ->label(__('Ends At')),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('Name')),
                Tables\Columns\TextColumn::make('promoter.name')
                    ->label(__('Promoter')),
                Tables\Columns\TextColumn::make('chef.name')
                    ->label(__('Chef')),
                Tables\Columns\TextColumn::make('accountant.name')
                    ->label(__('Accountant')),
                Tables\Columns\TextColumn::make('started_at')
                    ->label(__('Starts At'))
                    ->date('d-m-Y'),
                Tables\Columns\TextColumn::make('ended_at')
                    ->label(__('Ends At'))
                    ->date('d-m-Y'),
                Tables\Columns\TextColumn::make('status')
                    ->label(__('Status'))
                    ->badge()
                    ->color(fn ($record) => ProjectStatusEnum::color($record->status)),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('Generate Invoice')
                    ->label(__('Generate Invoice'))
                    ->icon('heroicon-o-inbox-arrow-down')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (Project $record, InvoiceService $invoiceService) {
                        return $invoiceService->downloadGlobalProjectInvoice($record);
                    }),
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
            BlocksRelationManager::class,
            PaymentsRelationManager::class,
            SupplierInvoicesRelationManager::class,
            BillsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProjects::route('/'),
            'view' => Pages\ViewProject::route('/{record}'),

        ];
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Projects Management';
    }
}
