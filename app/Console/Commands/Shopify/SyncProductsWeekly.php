<?php

namespace App\Console\Commands\Shopify;

use App\Events\Shopify\Products\SyncProductsFire;
use App\Models\Accounts;
use Illuminate\Console\Command;

class SyncProductsWeekly extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shopify:sync-products-weekly';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync Weekly Products from Shopify server';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {

            $accounts = Accounts::where('suspended', '=', 0)->get();

            if($accounts) {
                foreach ($accounts as $account) {
                    event(new SyncProductsFire($account));
                }
            }


        } catch(\Exception $e) {

        }

        return true;
    }
}
