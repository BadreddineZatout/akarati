<?php

namespace App\Filament\Resources;

use App\Enums\ProjectStatusEnum;
use App\Filament\Resources\ProjectResource\Pages;
use App\Filament\Resources\ProjectResource\RelationManagers;
use App\Filament\Resources\ProjectResource\RelationManagers\BlocksRelationManager;
use App\Models\Project;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProjectResource extends Resource
{
    protected static ?string $model = Project::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required(),
                Forms\Components\Select::make('status')
                    ->label('State')
                    ->options(array_reduce(ProjectStatusEnum::cases(), function ($carry, $state) {
                        $carry[$state->value] = ucfirst(str_replace('_', ' ', $state->name));
                        return $carry;
                    }, []))
                    ->default('not_launched'),
                Forms\Components\Select::make('promoter_id')
                    ->label(__('Promoter'))
                    ->relationship('promoter', 'name')
                    ->getOptionLabelUsing(fn ($value) => User::find($value)?->name)
                    ->options(User::role('promoter')->pluck('name', 'id')->toArray())
                    ->required(),
                Forms\Components\Select::make('accountant_id')
                    ->label(__('Accountant'))
                    ->relationship('accountant', 'name')
                    ->getOptionLabelUsing(fn ($value) => User::find($value)?->name)
                    ->options(User::role('Accountant')->pluck('name', 'id')->toArray())
                    ->required(),
                Forms\Components\DatePicker::make('started_at'),
                Forms\Components\DatePicker::make('ended_at'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('accountant.name'),
                Tables\Columns\TextColumn::make('promoter.name'),
                Tables\Columns\TextColumn::make('started_at')
                    ->date('d-m-Y'),
                Tables\Columns\TextColumn::make('ended_at')
                    ->date('d-m-Y'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn ($record) => ProjectStatusEnum::color($record->status)),
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
            BlocksRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProjects::route('/'),
//            'create' => Pages\CreateProject::route('/create'),
//            'edit' => Pages\EditProject::route('/{record}/edit'),
            'view' => Pages\ViewProject::route('/{record}'),

        ];
    }
    public static function getNavigationGroup(): ?string
    {
        return 'Projects Management';
    }
}