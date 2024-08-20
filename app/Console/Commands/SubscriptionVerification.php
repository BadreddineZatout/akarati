<?php

namespace App\Console\Commands;

use App\Jobs\SubscriptionJob;
use App\Models\User;
use App\Services\SubscriptionService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SubscriptionVerification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:subscription-verification';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verify all subscriptions and deactivate expired ones';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $subscriptionService = new SubscriptionService();
        Log::info('Verifying subscriptions...');
        $users = User::get();
        foreach ($users as $user)
            $subscriptionService->check($user);
        Log::info('Subscription verification completed.');    }
}
