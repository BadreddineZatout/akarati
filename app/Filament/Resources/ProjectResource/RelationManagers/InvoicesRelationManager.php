<?php

namespace App\Filament\Resources\ProjectResource\RelationManagers;

use App\Enums\InvoiceTypeEnum;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\MorphToSelect;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class InvoicesRelationManager extends RelationManager
{
    protected static string $relationship = 'invoices';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\MorphToSelect::make('invoicable')
                    ->label('receiver')
                    ->types([
                        MorphToSelect\Type::make(User::class)
                            ->titleAttribute('name'),
                    ])
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('amount')
                    ->required()
                    ->minValue(1),
                Forms\Components\DatePicker::make('invoiced_at')
                    ->required()
                    ->label('date'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->modifyQueryUsing(fn (Builder $query) => $query->where('type', InvoiceTypeEnum::PROJECT->value))
            ->columns([
                Tables\Columns\TextColumn::make('invoicable.name')
                    ->searchable()
                    ->label('receiver'),
                Tables\Columns\TextColumn::make('invoicable.roles.name')
                    ->label('role'),
                Tables\Columns\TextColumn::make('amount')
                    ->sortable(),
                Tables\Columns\TextColumn::make('invoiced_at')
                    ->sortable()
                    ->label('date'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['type'] = InvoiceTypeEnum::PROJECT->value;

                        return $data;
                    }),
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
