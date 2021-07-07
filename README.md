# Reorg Case Study

Thanks for the opportunity!  Here is my case study.  At this time it is a minimum viable product without tests implemented, due to attempting to solve database write performance issues I ran into in my dev environment under WSL2.  I will be updating this package as I am able to implement the remaining features I have planned.

This was developed under using PHP 7.3, Laravel 8, and Node 16.1.0.

This requires [composer](https://getcomposer.org/download/) and [npm](https://nodejs.org/en/download/package-manager/) to install.

To install dependencies and compile the code you can run the following commands.

```
composer install && npm install && npm run dev
```

You will need to configure a .env file with your database and an application key.  For ease of use, I have provided a shell script that will generate a .env from the provided example and configure it to use an sqlite database.  If you intend to ingest the entire dataset however, I recommend changing this configuration to use MySQL or PostgreSQL.

```
sh generate_env.sh
```

To migrate and seed your database run the following commands.

```
php artisan migrate:fresh
php artisan db:seed
```

Once your database is generated, you will need to ingest the data.  For initial ingestion, and basic testing, I would recommend running the following command.  This will attempt to ingest the entire dataset, however testing can be completed with any amount of records, so you can interrupt with Ctrl+C after a few thousand records are ingested.

```
php artisan data:ingest
```

For ingesting new data on a schedule and continuing to monitor for changes, a laravel scheduled task has been configured to run hourly.  For configuring this to run via crond, please see the following [documentation](https://laravel.com/docs/8.x/scheduling#running-the-scheduler).  For running locally, you can run the following command.

```
php artisan schedule:work
```

Finally, to run the local test server and access the site, you can do so by running the following command and then visiting http://localhost:8000

```
php artisan serve
```