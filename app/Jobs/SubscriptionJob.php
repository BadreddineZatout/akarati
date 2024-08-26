<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\SubscriptionService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SubscriptionJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $subscriptionService = new SubscriptionService;
        Log::info('Verifying subscriptions...');
        $users = User::get();
        foreach ($users as $user) {
            $subscriptionService->check($user);
        }
        Log::info('Subscription verification completed.');
    }
}
