<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Dataset;

class DataIngestCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'data:ingest';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Starts ingesting data from all datasets marked to ingest.';

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
     * @return int
     */
    public function handle()
    {
        $datasets = Dataset::where('ingesting', true)->get();

        foreach($datasets as $dataset) {
            $dataset->ingest();
        }

        return 0;
    }
}
