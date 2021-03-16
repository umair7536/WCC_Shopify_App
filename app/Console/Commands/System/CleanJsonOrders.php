<?php

namespace App\Console\Commands\System;

use App\Models\JsonOrders;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CleanJsonOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lcs:clean-jsonorders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean orders junk from json orders table';

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
            $created_at = Carbon::now()->subDays(2)->toDateTimeString();
            JsonOrders::where('created_at', '<=', $created_at)
                ->forceDelete();
        } catch(\Exception $e) {

        }
    }
}
