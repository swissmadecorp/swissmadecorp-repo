<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Models\Product;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        // '\App\Console\Commands\productOnHold',
        '\App\Console\Commands\GoogleShopping',
        '\App\Console\Commands\ImportToShopify',
        '\App\Console\Commands\ProcessNewsLetter',
        '\App\Console\Commands\FacebookFeed',
        '\App\Console\Commands\EmailTracking',
        '\App\Console\Commands\EmailCustomerReview',
        '\App\Console\Commands\ClearDiscountPrice',
        '\App\Console\Commands\UsaePayOrderChecker'
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')->hourly();
        // $schedule->command("product:onhold")
        //     ->everyFifteenMinutes();

        $schedule->command("product:export")
            ->twiceDaily(10, 18)->withoutOverlapping();

        // $schedule->command("pinterest:export")
        //     ->twiceDaily(9, 20)->withoutOverlapping();

        $schedule->command("facebook:feed")
            ->twiceDaily(7, 19)->withoutOverlapping();

        // $schedule->command("massmail:newsletter")
        //     ->monthly()->withoutOverlapping();

        // $schedule->command("email:tracking")
        //     ->dailyAt('18:00');

        // $schedule->command("email:review")
        //     ->dailyAt('19:00');

        // $schedule->command("shopify:export", ["sold"]) // with paramaters
        //     ->dailyAt('20:00');

        // $schedule->command("shopify:export", ["new"]) // with paramaters
        //     ->dailyAt('20:30');

        $schedule->command("discount:clear")
            ->dailyAt('23:00');

        // $schedule->command("verify:creditcard")
        // ->everyMinute();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
