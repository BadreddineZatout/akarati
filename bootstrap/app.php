<?php

use App\Http\Middleware\ProjectLimitMiddleware;
use App\Http\Middleware\SubscriptionMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

define('BASE_DIR', realpath(__DIR__.'/../../'));

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            SubscriptionMiddleware::class,
            ProjectLimitMiddleware::class,
        ]);
        $middleware->alias([
            'subscription.verify' => SubscriptionMiddleware::class,
            'project.limit' => ProjectLimitMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
