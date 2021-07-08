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
     * Fillable attributes
     *
     * @var array
     */
    protected $fillable = ['ingesting'];

    /**
     * Starts or resumes an Ingestion Job for this Dataset.
     *
     * @return void
     */
    public function ingest() {
        IngestJob::ingest($this);
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
