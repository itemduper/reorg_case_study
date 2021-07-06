<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIngestEventsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Log Levels
        // 0 - Fatal	One or more key business functionalities are not working and the whole system doesn’t fulfill the business functionalities.
        // 1 - Error	One or more functionalities are not working, preventing some functionalities from working correctly.
        // 2 - Warn	    Unexpected behavior happened inside the application, but it is continuing its work and the key business features are operating as expected.
        // 3 - Info	    An event happened, the event is purely informative and can be ignored during normal operations.

        Schema::create('ingest_events', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->unsignedBigInteger('ingest_job_id');
            $table->unsignedInteger('level')->default(3);
            $table->text('content');

            $table->foreign('ingest_job_id')->references('id')->on('ingest_jobs');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ingest_events');
    }
}
