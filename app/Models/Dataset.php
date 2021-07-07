<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\IngestJob;

class Dataset extends Model
{
    // TODO (maybe?)
    // use HasFactory;

    /**
     * Cast attributes as the correct type.
     *
     * @var array
     */
    protected $casts = [
        'ingesting' => 'boolean',
        'offset' => 'integer',
    ];

    /**
     * Starts or resumes an Ingestion Job for this Dataset.
     *
     * @return void
     */
    public function ingest() {
        // Check for an incomplete job, and run it if one is found.
        $job = $this->ingest_jobs()->whereNull('completed_at');

        if($job->exists()) {
            $job->first()->run();
        } else {
            $job = new IngestJob;

            // Determine if jobs have previously been completed for this dataset
            // Set offset to where the last completed job ended at
            $last_complete_job = $this->ingest_jobs()->orderByDesc('completed_at');
            if($last_complete_job->exists()) {
                $job->offset = $last_complete_job->first()->offset;
            } else {
                $job->offset = 0;
            }

            $job->starting_offset = $job->offset;
            $job->dataset()->associate($this);

            $job->save();

            $job->run();
        }
    }

    /**
     * Gets Ingestion Jobs for this Dataset.
     *
     * @return HasMany
     */
    public function ingest_jobs() {
        return $this->hasMany(IngestJob::class);
    }

    /**
     * Gets Payments associated with this Dataset.
     *
     * @return HasMany
     */
    public function payments() {
        return $this->hasMany(Payment::class);
    }
}
