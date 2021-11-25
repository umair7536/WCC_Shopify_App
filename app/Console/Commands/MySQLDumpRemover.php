<?php

namespace App\Console\Commands;

use App\Models\DBBackups;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Config;
use Illuminate\Support\Facades\Storage;

class MySQLDumpRemover extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:backup-old-remove';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Runs the mysqldump utility using info from .env to remove old backups';

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
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try {
            $db_backups = DBBackups::whereDate('created_at', '<=', Carbon::parse(Carbon::now())->subDays(30)->toDateString())
                ->get();
            if($db_backups) {
                foreach ($db_backups as $db_backup) {
                    /**
                     * Delete file from S3
                     */
                    $storage = Storage::createS3Driver([
                        'driver' => 's3',
                        'key'    => env('DO_SPACES_KEY'),
                        'secret' => env('DO_SPACES_SECRET'),
                        'endpoint' => env('DO_SPACES_ENDPOINT'),
                        'region' => env('DO_SPACES_REGION'),
                        'bucket' => env('DO_SPACES_BUCKET'),
                    ]);

                    $storage->delete(env('DO_FOLDER') . '/' . $db_backup->file);
                    DBBackups::where('id', $db_backup->id)->delete();
                }
            }
        } catch (\Exception $exception) {}
    }
}
