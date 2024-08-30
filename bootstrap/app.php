<?php

use App\Http\Middleware\SubscriptionMiddleware;
use Database\Seeders\ProjectLimitSeeder;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            SubscriptionMiddleware::class,
            ProjectLimitSeeder::class,
        ]);
        $middleware->alias([
            'subscription.verify' => SubscriptionMiddleware::class
            'project.limit' => ProjectLimitSeeder::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
