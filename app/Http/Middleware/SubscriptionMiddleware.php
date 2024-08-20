<?php

namespace App\Http\Middleware;

use App\Models\Subscription;
use App\Models\User;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use App\Services\SubscriptionService;
class SubscriptionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user= $request->user();
        if ($user && !$this->check($user)){
               return redirect()->route('filament.admin.pages.dashboard')->with('error', 'You must have an active subscription to access this page.');
        }
        return $next($request);
    }
    public function check(User $user) :bool{
        $subscription = Subscription::query()->where('subscriber_id',$user->id)
            ->where('status','active')
            ->first();
        if(Carbon::parse($subscription->ends_at)->isBefore(Carbon::now()))
            $subscription->update(['status' =>'ended']);
        else
            return true;
        return false;
    }
}
