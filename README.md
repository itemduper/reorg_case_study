# Reorg Case Study

Thanks for the opportunity!  Here is my case study.  At this time it is a minimum viable product without tests implemented, due to attempting to solve database write performance issues I ran into in my dev environment under WSL2.  I will be updating this package as I am able to implement the remaining features I have planned.

## Setup

This project was developed using [PHP 7.3](https://www.php.net/manual/en/install.php) and [Node 16.1.0](https://nodejs.org/en/download/package-manager/).  I assume whomever is testing this already has these installed, but in case you run into build errors, I would make sure you are up to at least these versions.

In addition to PHP and Node, this project requires [composer](https://getcomposer.org/download/) to install.

To install dependencies and build the distribution run the following commands.

```shell
composer install && npm install && npm run dev
```

You will need to create a .env file with your database configuration and an application key.  For ease of use, I have provided a shell script that will generate a .env from the provided example and configure it to use SQLite as your database.  If you intend to ingest the entire dataset, I recommend changing this configuration to use [MySQL](https://dev.mysql.com/doc/mysql-installation-excerpt/8.0/en/) or [PostgreSQL](https://www.postgresql.org/docs/13/tutorial-install.html).

```shell
sh generate_env.sh
```

To migrate and seed your database run the following commands.

```shell
php artisan migrate:fresh
php artisan db:seed
```

Once your database is generated, you will need to ingest the dataset.  For initial ingestion, and basic testing, I would recommend running the following command.  This will attempt to ingest the entire dataset, however testing can be completed with any amount of records, so you can interrupt with Ctrl+C after a few thousand records are ingested.

```shell
php artisan data:ingest
```

For ingesting new data on a schedule and continuing to monitor for changes, a Laravel scheduled task has been configured to run hourly.  For configuring this to run via crond, please see the following [documentation](https://laravel.com/docs/8.x/scheduling#running-the-scheduler).  For running locally, you can run the following command.

```shell
php artisan schedule:work
```

Finally, to run the local test server and access the site, you can do so by running the following command and then visiting http://localhost:8000

```shell
php artisan serve
```

## Additional Configuration

The following settings are stored in the database and are seeded with default values.  You can modify these using the `Setting::set()` function inside of Laravel Tinker (`php artisan tinker`).

```php
// ingest_query_limit controls the number of records requested per query.
// There is no explicit limit in the API docs, but the highest I tested was 50000.
Setting::set('ingest_query_limit',1000,'integer');

// ingest_timeout controls the number of seconds before a query times out.
Setting::set('ingest_timeout',300,'integer');

// ingest_max_consecutive_timeouts controls the number of timeouts in a row 
// before an Ingest Job fails.
Setting::set('ingest_max_consecutive_timeouts',3,'integer');

// ingest_max_total_timeouts controls the number of timeouts per attempt to
// run an Ingest Job before it fails.
Setting::set('ingest_max_total_timeouts',10,'integer');

// ingest_max_queries controls the number of queries performed in a row before
// an Ingest Job will terminate.  If set to -1 there is no limit.
Setting::set('ingest_max_queries',-1,'integer');

// ingest_event_print informs the IngestEvent::log() function whether or not it 
// should print events when running via the CLI.
Setting::set('ingest_event_print',true,'boolean');
```
