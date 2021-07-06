<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePhysiciansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('physicians', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->unsignedBigInteger('address_id')->nullable();
            $table->unsignedBigInteger('cms_id');
            $table->text('first_name');
            $table->text('middle_name')->nullable();
            $table->text('last_name');
            $table->text('name_suffix')->nullable();
            $table->text('primary_type')->nullable();
            $table->text('specialty')->nullable();
            $table->text('license_state_code1')->nullable();
            $table->text('license_state_code2')->nullable();
            $table->text('license_state_code3')->nullable();
            $table->text('license_state_code4')->nullable();
            $table->text('license_state_code5')->nullable();

            $table->foreign('address_id')->references('id')->on('addresses');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('physicians');
    }
}
