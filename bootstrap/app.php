<?php

use App\Jobs\productOnHold;
use App\Jobs\Pinterest;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Console\Scheduling\Schedule;
use App\Http\Middleware\BlockIpMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withProviders()
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withSchedule(function (Schedule $schedule) {
        // $schedule->call(new productOnHold)->everyMinute();
        // $schedule->call(function () {
        //     productOnHold::handleMethod(); // Dispatch the job correctly
        // })->everyMinute();

        $schedule->command("product:onhold")
             ->everyFifteenMinutes();

        $schedule->command("email:tracking")
             ->dailyAt('18:00');

        $schedule->command("massmail:newsletter")
            ->monthly()->withoutOverlapping();

        $schedule->command("discount:clear")
            ->dailyAt('23:00');

        $schedule->command("verify:creditcard")
        ->everyMinute();

        // $schedule->call(function () {
        //     Pinterest::handleMethod(); // Dispatch the job correctly
        // })->twiceDaily(9, 20);
    })
    ->withMiddleware(function (Middleware $middleware) {
        //

        $middleware->append(BlockIpMiddleware::class);
        $middleware->alias(
            ['role' => Spatie\Permission\Middleware\RoleMiddleware::class],
            ['permission' => Spatie\Permission\Middleware\PermissionMiddleware::class],
            ['role_or_permission' => Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class],
            ['Excel' => Maatwebsite\Excel\Facades\Excel::class],
        );

    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
