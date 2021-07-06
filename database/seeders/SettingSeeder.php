<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

use App\Models\Setting;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Setting::set('ingest_query_limit',1000,'integer');
        Setting::set('ingest_timeout',300,'integer');
        Setting::set('ingest_max_consecutive_timeouts',3,'integer');
        Setting::set('ingest_max_total_timeouts',10,'integer');
        Setting::set('ingest_max_queries',-1,'integer');
        Setting::set('ingest_event_print',true,'boolean');
    }
}
