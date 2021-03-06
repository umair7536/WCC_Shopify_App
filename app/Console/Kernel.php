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
//        '\App\Console\Commands\Shopify\SyncProducts',
        '\App\Console\Commands\Shopify\SyncCustomers',
        '\App\Console\Commands\Leopards\SyncPacketStatus',
        '\App\Console\Commands\Leopards\SyncAllAccountPackets',
//        '\App\Console\Commands\Shopify\SyncProductsWeekly',
//        '\App\Console\Commands\Shopify\UploadVariants',
        '\App\Console\Commands\Shopify\HandleHeavyLifting',
//        '\App\Console\Commands\Shopify\SyncCustomCollections',
//        '\App\Console\Commands\Shopify\SyncCollects',
        '\App\Console\Commands\Shopify\SyncOrders',
        '\App\Console\Commands\Shopify\MarkOrderAsPaid',
        '\App\Console\Commands\System\CleanJsonOrders',
        /*
         * MySQL daily backup command
         */
        '\App\Console\Commands\MySQLDump',
        '\App\Console\Commands\MySQLDumpRemover',
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
         * Mark Paid Orders
         */
//        $schedule->command('shopify:mark-orders-paid')
//            ->withoutOverlapping()
//            ->everyMinute();

        /*
         * Sync Products from Shopify
         */
        $schedule->command('lcs:sync-packet-status')
            ->withoutOverlapping()
            ->everyMinute();

        /*
         * Sync Customers from Shopify
         */
        $schedule->command('shopify:sync-customers')
            ->everyMinute();

        /*
         * Launch Packet Statuses Update
         */
        $schedule->command('lcs:sync-all-accounts-packets')
            ->withoutOverlapping()
            ->cron('0 */6 * * *');

        /*
         * Sync Customers from Shopify
         */
//        $schedule->command('shopify:sync-custom-collections')
//            ->withoutOverlapping()
//            ->everyMinute();

        /*
         * Sync Customers from Shopify
         */
//        $schedule->command('shopify:sync-collects')
//            ->withoutOverlapping()
//            ->everyMinute();

        /*
         * Sync Customers from Shopify
         */
        $schedule->command('shopify:sync-orders')
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
//        $schedule->command('shopify:sync-products-weekly')
//            ->withoutOverlapping()
//            ->saturdays()
//            ->timezone('America/Los_Angeles')
//            ->at('23:59');

        /**
         * =========================
         * Process Leopards Requests
         * =========================
         */

        /*
         * Run daily backup command
         */
        $schedule->command('db:backup')
            ->daily()->withoutOverlapping();

        /*
         * Run old daily backup remover command
         */
        $schedule->command('db:backup-old-remove')
            ->daily()->withoutOverlapping();

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
