<?php

namespace App\Console\Commands;

use App\Models\DBBackups;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class MySQLDump extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:backup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Runs the mysqldump utility using info from .env';

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
            $ds = DIRECTORY_SEPARATOR;
            $host = env('DB_HOST');
            $username = env('DB_USERNAME');
            $password = env('DB_PASSWORD');
            $database = env('DB_DATABASE');

            $ts = time();
            $path = database_path() . $ds . 'backups' . $ds;
            $file = date('Y-m-d-His', $ts) . '-dump-' . $database . '.sql.gz';

            if(env('APP_ENV') == 'local') {
                $command = sprintf('/Applications/MAMP/Library/bin/mysqldump  --single-transaction --quick -h %s -u %s -p\'%s\' %s | gzip -9 -c > %s', $host, $username, $password, $database, $path . $file);
            } else {
                $command = sprintf('mysqldump --single-transaction --quick -h %s -u %s -p\'%s\' %s | gzip -9 -c > %s', $host, $username, $password, $database, $path . $file);
            }

            is_dir($path) ?: mkdir($path, 0755, true);

            exec($command);

            DBBackups::create(array(
               'path' => $path,
               'file' => $file
            ));

            $storage = Storage::createS3Driver([
                'driver' => 's3',
                'key'    => env('DO_SPACES_KEY'),
                'secret' => env('DO_SPACES_SECRET'),
                'endpoint' => env('DO_SPACES_ENDPOINT'),
                'region' => env('DO_SPACES_REGION'),
                'bucket' => env('DO_SPACES_BUCKET'),
            ]);

            /**
             * Put backup to AWS S3
             */
            $storage->put(env('DO_FOLDER') . '/' . $file, fopen($path . $file, 'r+'));
            @unlink($path . $ds . $file);
        } catch (\Exception $exception) {
            echo $exception->getMessage();
        }
    }
}
