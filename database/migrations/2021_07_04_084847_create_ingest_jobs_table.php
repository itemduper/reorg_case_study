<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIngestJobsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ingest_jobs', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->unsignedBigInteger('dataset_id');
            $table->unsignedBigInteger('offset')->default(0);
            $table->unsignedBigInteger('starting_offset')->default(0);
            $table->boolean('in_progress')->default(false);
            $table->timestamp('completed_at')->default(null)->nullable();

            $table->foreign('dataset_id')->references('id')->on('datasets');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ingest_jobs');
    }
}
