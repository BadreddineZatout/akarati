<?php

namespace App\Services;

use App\Models\Subscription;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SubscriptionService
{
    public function check(User $user) :bool{
        $subscription = Subscription::query()->where('subscriber_id',$user->id)
            ->where('status','active')
            ->first();
        if($subscription && Carbon::parse($subscription->ends_at) < Carbon::now())
            $subscription->update(['status' =>'ended']);
        else
            return true;
        return false;
    }
}
