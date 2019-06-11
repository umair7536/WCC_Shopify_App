<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
        /*
         * Shopify Commands
         */
        '\App\Console\Commands\Shopify\SyncProducts',
        '\App\Console\Commands\Shopify\SyncCustomers',
        '\App\Console\Commands\Shopify\SyncProductsWeekly',
        '\App\Console\Commands\Shopify\UploadVariants',
        '\App\Console\Commands\Shopify\HandleHeavyLifting',
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {

        /**
         * =========================
         * Process Shpify Requests
         * =========================
         */

        /*
         * Sync Products from Shopify
         */
        $schedule->command('shopify:sync-products')
            ->withoutOverlapping()
            ->everyMinute();

        /*
         * Sync Customers from Shopify
         */
        $schedule->command('shopify:sync-customers')
            ->withoutOverlapping()
            ->everyMinute();

        /*
         * Sync Products from Shopify
         */
//        $schedule->command('shopify:upload-variants')
//            ->withoutOverlapping()
//            ->everyMinute();

        /*
         * Sync Weekly Products from Shopify
         */
        $schedule->command('shopify:sync-products-weekly')
            ->withoutOverlapping()
            ->saturdays()
            ->timezone('America/Los_Angeles')
            ->at('23:59');

    }

    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        require base_path('routes/console.php');
    }
}
