<?php

namespace App\Filament\Resources\PromotionResource\Pages;

use App\Enums\ProfitStateEnum;
use App\Filament\Resources\PromotionResource;
use App\Models\Client;
use App\Models\Promotion;
use App\Models\User;
use Livewire\Component as Livewire;
use Filament\Forms\Get;
use Filament\Forms\Components\Section;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class SellPromotion extends Page
{
    use InteractsWithForms;
    protected static string $resource = PromotionResource::class;

    protected static string $view = 'filament.resources.promotion-resource.pages.sell-promotion';
    public Promotion $promotion;
    public ?array $sellingData = [];
    public $selectedOption = null;

    public function mount(Promotion $promotion)
    {
        $this->promotion = $promotion;
//        $this->sellingData = $promotion->toArray();

    }

    protected function getFormSchema(): array
    {
        return [
            Section::make('Selling Information ')
                ->schema([
                    Select::make('client_id')
                        ->label(__('Client'))
                        ->relationship('client', 'last_name')
                        ->getOptionLabelUsing(fn ($value) => Client::find($value)?->last_name)
                        ->options(Client::pluck('last_name', 'id')->toArray())
                        ->afterStateUpdated(function (?string $state, ?string $old) {
                            echo $state;

                        })
                        ->required(),
                    Select::make('state')
                        ->label('State')
                        ->options(array_reduce(ProfitStateEnum::cases(), function ($carry, $state) {
                            $carry[$state->value] = ucfirst(str_replace('_', ' ', $state->name));
                            return $carry;
                        }, []))
                        ->default('not paid'),
                    Select::make('payable_type')
                        ->reactive()
                        ->label('Payable Type')
                        ->dehydrated(false)
                        ->options([
                            'promoter' => 'Promoter',
                            'accountant' => 'Accountant',
                        ])
                        ->required()
                        ->afterStateUpdated(function (?string $state, ?string $old) {
                            Log::info($state);
                            Log::info($old);
                        }),
                    Select::make('payable_id')
                        ->required()
                        ->label('Payable')
                        ->getOptionLabelUsing(fn ($value) => User::find($value)?->name)
                        ->placeholder(fn (Get $get): string => empty($get('payable_type')) ? 'First select type' : 'Select an option')
                        ->options(function (Get $get) {
                            return User:: role($get('payable_type'))->pluck('name', 'id');
                        }),
                    TextInput::make('amount')
                        ->required()
                        ->numeric(),
                ])->columns(2)

        ];
    }

    public function submit(): void
    {
        $data = $this->sellingForm->getState();
        // Handle data submission and saving to the database
//        $this->handleSellingData($this->promotion, $data);
        $this->sendSuccessNotification();
    }

    private function sendSuccessNotification(): void
    {
        Notification::make()
            ->success()
            ->title(__('Saved successfully'))
            ->send();
    }

//    private function handleSellingData(Model $record, array $data): Model
//    {
//        $record->update($data);
//        return $record;
//    }




}
