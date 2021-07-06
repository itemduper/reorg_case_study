<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

use App\Models\Dataset;

class DatasetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Dataset::create(['title' => 'General Payment Data 2013', 'api_endpoint' => 'https://openpaymentsdata.cms.gov/resource/gtwa-6ahd.json', 'ingesting' => false]);
        Dataset::create(['title' => 'General Payment Data 2014', 'api_endpoint' => 'https://openpaymentsdata.cms.gov/resource/k4nv-tsw8.json', 'ingesting' => false]);
        Dataset::create(['title' => 'General Payment Data 2015', 'api_endpoint' => 'https://openpaymentsdata.cms.gov/resource/bcns-iag5.json', 'ingesting' => true]);
        Dataset::create(['title' => 'General Payment Data 2016', 'api_endpoint' => 'https://openpaymentsdata.cms.gov/resource/ejfy-p7re.json', 'ingesting' => false]);
        Dataset::create(['title' => 'General Payment Data 2017', 'api_endpoint' => 'https://openpaymentsdata.cms.gov/resource/svr3-5bg9.json', 'ingesting' => false]);
        Dataset::create(['title' => 'General Payment Data 2018', 'api_endpoint' => 'https://openpaymentsdata.cms.gov/resource/ud7t-2ipu.json', 'ingesting' => false]);
        Dataset::create(['title' => 'General Payment Data 2019', 'api_endpoint' => 'https://openpaymentsdata.cms.gov/resource/qsys-b88w.json', 'ingesting' => false]);
        Dataset::create(['title' => 'General Payment Data 2020', 'api_endpoint' => 'https://openpaymentsdata.cms.gov/resource/txng-a8vj.json', 'ingesting' => false]);
    }
}
