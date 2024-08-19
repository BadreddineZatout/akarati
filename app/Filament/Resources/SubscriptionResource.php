<?php

namespace App\Filament\Resources;

use App\Enums\SubscriptionStateEnum;
use App\Filament\Resources\SubscriptionResource\Pages;
use App\Filament\Resources\SubscriptionResource\RelationManagers;
use App\Models\Subscription;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Laravelcm\Subscriptions\Models\Plan;

class SubscriptionResource extends Resource
{
    protected static ?string $model = Subscription::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('subscriber_id')
                    ->label(__('Subscriber'))
                    ->getOptionLabelUsing(fn ($value) => User::find($value)?->name)
                    ->options(User::pluck('name', 'id')->toArray())
                    ->searchable()
                    ->required(),
                Forms\Components\Select::make('plan_id')
                    ->label(__('Plan'))
                    ->relationship('plan', 'name')
                    ->getOptionLabelUsing(fn ($value) => Plan::find($value)?->name)
                    ->options(Plan::pluck('name','id')->toArray())
                    ->searchable()
                    ->required(),
                Forms\Components\DateTimePicker::make('starts_at')
                        ->required(),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('plan.name')
                ->label('Plan'),
                Tables\Columns\TextColumn::make('subscriber.name')
                ->label('Subscriber'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn ($record) => SubscriptionStateEnum::color($record->status)),
                Tables\Columns\TextColumn::make('starts_at'),
                Tables\Columns\TextColumn::make('ends_at'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('renew')->icon('heroicon-m-arrow-path')
                        ->action(function (array $data, Subscription $record): void {
                            User::find($record->subscriber_id)->planSubscription($record->slug)->renew();
                            Notification::make()
                                ->success()
                                ->title('Renewed subscription successfully');
                        }),
                Tables\Actions\Action::make('changePlan')->icon('heroicon-m-pencil-square')
                    ->form([
                        Forms\Components\Select::make('plan_id')
                            ->label(__('Plan'))
                            ->relationship('plan', 'name')
                            ->getOptionLabelUsing(fn ($value) => Plan::find($value)?->name)
                            ->options(Plan::pluck('name','id')->toArray())
                            ->searchable()
                            ->required(),
                    ]) ->fillForm(fn (Subscription $record): array => [
                        'plan_id' => $record->plan_id,
                    ])
                        ->action(function (array $data, Subscription $record): void {
                            $record->changePlan(Plan::find($data['plan_id']));
                            Notification::make()
                                ->success()
                                ->title('Plan changed successfully');
                        })->after(function (array $data, Subscription $record): void {
                        Notification::make()
                            ->success()
                            ->title('Plan changed successfully');
                    }),
//                Tables\Actions\Action::make('cancel')->icon('heroicon-m-backspace')->color('danger')
//                    ->action(function (array $data, Subscription $record): void {
//                        User::find($record->subscriber_id)->planSubscription($record->slug)->cancel();
//                        $record->update(['status' => 'cancel']);
//                        Notification::make()
//                            ->success()
//                            ->title('canceled subscription ');
//                    }),
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSubscriptions::route('/'),
            'create' => Pages\CreateSubscription::route('/create'),
            'edit' => Pages\EditSubscription::route('/{record}/edit'),
        ];
    }
    public static function getNavigationGroup(): ?string
    {
        return 'Subscriptions Management';
    }
}
