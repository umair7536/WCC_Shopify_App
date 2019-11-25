### Developify App for Shopify

#### Usage

This is not a package - it's a full Laravel project that you should use as a starter boilerplate, and then add your own custom functionality.


##### Application Setup

- Clone the repository with `git clone`
- Install `redis` server using any helpful article
- Copy `.env.example` file to `.env` and edit database credentials there
- Run `composer install`
- Run `php artisan key:generate`
- Run `php artisan migrate --seed` (it has some seeded data - see below)
- That's it: launch the main URL and login with default credentials `admin@admin.com` - `password`
- Two cron jobs are provided with this system. You need to add the following Cron entries to your server.
    - `* * * * * php /path-to-your-project/artisan schedule:run >> /dev/null 2>&1`
    - `* * * * * php /path-to-your-project/artisan shopify:handle-heavy-lifting >> /dev/null 2>&1`
    - `* * * * * php /path-to-your-project/artisan lcs:sync-packet-status >> /dev/null 2>&1`
- If you are setting up redis queue don't forgot to create `storage/logs/supervisor` folder and assign permissions appropriately.

##### Setup Redis Server for Queue

- First download the [REDIS](http://redis.io/download) to your system (if you haven't already installed it).
- Go to your .env file and add Queue driver `QUEUE_DRIVER=redis`
- Whenever you make changes live don't forgot to run `php artisan queue:restart`

##### Setup Redis Queue on Suervision

Install Supervisor from online documentation. Here I'll cover install and setup supervisor on Ubuntu only. Then consult Laravel Official Documentation for [Supervisor Configuration](https://laravel.com/docs/5.7/queues#supervisor-configuration)

###### Useful commands for Supervisor
```
sudo apt-get purge supervisor
sudo apt-get install supervisor
sudo service supervisor start
sudo service supervisor stop
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start <job_name>:*
```

###### Sample Worker File

This should be saved in `/etc/supervisor/conf.d/<job_name>.conf`

```
[program:<job_name>]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/laravel/project/artisan queue:work database --sleep=3 --tries=3 --daemon --queue=high,default,low
autostart=true
autorestart=true
user=root
numprocs=1
redirect_stderr=true
stdout_logfile=/path/to/laravel/project/storage/logs/supervisor/queue.log
```

###### 'default' queue

```
[program:queue-default]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/laravel/project/artisan queue:work redis --sleep=3 --tries=3 --daemon --queue=default
autostart=true
autorestart=true
user=root
numprocs=1
redirect_stderr=true
stdout_logfile=/path/to/laravel/project/storage/logs/supervisor/queue-default.log
```

###### 'shopify' queue

```
[program:queue-shopify]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/laravel/project/artisan queue:work redis --sleep=3 --tries=3 --daemon --queue=shopify
autostart=true
autorestart=true
user=root
numprocs=1
redirect_stderr=true
stdout_logfile=/path/to/laravel/project/storage/logs/supervisor/queue-shopify.log
```