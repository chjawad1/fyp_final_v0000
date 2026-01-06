<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Console\Scheduling\Schedule;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => \App\Http\Middleware\CheckRole::class,
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'supervisor' => \App\Http\Middleware\SupervisorMiddleware::class,
            'evaluator' => \App\Http\Middleware\EnsureUserIsEvaluator::class,
        ]);
        
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->withSchedule(function (Schedule $schedule) {
        $schedule->command('templates:purge --days=30')->dailyAt('02:15');
        
        // Add defence session status update
        $schedule->command('defence:update-status')
                 ->daily()
                 ->at('00:01')
                 ->withoutOverlapping()
                 ->appendOutputTo(storage_path('logs/defence-status-updates.log'));
    })->create();