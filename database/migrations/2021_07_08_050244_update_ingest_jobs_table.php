<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateIngestJobsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ingest_jobs', function (Blueprint $table) {
            $table->boolean('is_initial')->default(true);
            $table->timestamp('publication_date')->default(null)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ingest_jobs', function (Blueprint $table) {
            $table->dropColumn('is_initial');
            $table->dropColumn('publication_date');
        });
    }
}
