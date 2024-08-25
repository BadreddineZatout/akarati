<?php

namespace App\Filament\Resources\SubscriptionResource\Pages;

use App\Filament\Resources\SubscriptionResource;
use App\Models\User;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Laravelcm\Subscriptions\Models\Plan;

class CreateSubscription extends CreateRecord
{
    protected static string $resource = SubscriptionResource::class;


    protected function handleRecordCreation(array $data): Model
    {
        $validator = Validator::make($data, [
            'starts_at' => 'required',
            'plan_id' => 'required|exists:plans,id',
            'subscriber_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            $this->notify('danger', 'Validation failed');
            return [];
        }
        $user = User::find($data['subscriber_id']);
        $plan = Plan::find($data['plan_id']);
        return $user->newPlanSubscription('main', $plan,Carbon::parse($data['starts_at']));
    }

}
