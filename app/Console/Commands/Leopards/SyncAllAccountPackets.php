<?php

namespace App\Console\Commands\Leopards;

use App\Events\Leopards\BookedPackets\FullSyncPacketStatusFire;
use App\Models\Accounts;
use Illuminate\Console\Command;

class SyncAllAccountPackets extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lcs:sync-all-accounts-packets';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync All Account Packets';

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

            $accounts = Accounts::where([
                'suspended' => 0
            ])->get();

            if($accounts) {
                foreach ($accounts as $account) {
                    event(new FullSyncPacketStatusFire($account));
                }
                echo 'Records found'; exit;
            }

        } catch(\Exception $e) {
            echo "\n";
            echo 'Exception came';
            echo "\n";
            echo "\n";
        }
    }
}
