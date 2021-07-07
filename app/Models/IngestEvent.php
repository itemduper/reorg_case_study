<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\IngestJob;

class IngestEvent extends Model
{
    // TODO
    // use HasFactory;

    /**
     * Fillable attributes
     *
     * @var array
     */
    protected $fillable = ['ingest_job_id', 'content', 'level'];

    /**
     * Event Levels
     *
     * @var array
     */
    protected static $event_levels = ['FAILURE','ERROR','WARN','INFO'];

    /**
     * Creates an IngestEvent
     *
     * @param IngestJob $job Job the event is related to.
     * @param string $content Content of the event
     * @param int $level Event level from 0 to 3, 0 = Failure, 1 = Error, 2 = Warn, 3 = Info
     * @return void
     */
    public static function log(IngestJob $job, string $content, int $level = 3) {
        $event = new self;

        $event->job()->associate($job);
        $event->content = $content;
        $event->level = $level;
        
        // Print the event if the ingest_event_print setting is enabled and the script is running from CLI
        if(Setting::get('ingest_event_print',false) && php_sapi_name() == 'cli') {
            print "[".self::$event_levels[$event->level]."]: $event->content\n";
        }

        $event->save();

        return $event;
    }

    /**
     * Returns the Ingest Job this event belongs to
     *
     * @return BelongsTo
     */
    public function job() {
        return $this->belongsTo(IngestJob::class, 'ingest_job_id');
    }
}
