<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->unsignedBigInteger('dataset_id');

            $table->unsignedBigInteger('record_id');
            $table->unsignedBigInteger('physician_id')->nullable();
            $table->unsignedBigInteger('hospital_id')->nullable();
            
            $table->text('change_type')->nullable();
            $table->text('covered_recipient_type')->nullable();
            $table->text('submitting_manufacturer_name')->nullable();
            $table->text('applicable_manufacturer_id')->nullable();
            $table->text('applicable_manufacturer_name')->nullable();
            $table->text('applicable_manufacturer_state')->nullable();
            $table->text('applicable_manufacturer_country')->nullable();
            $table->decimal('total_payment_usd',12,2)->nullable();
            $table->timestamp('date_of_payment')->nullable();
            $table->unsignedInteger('number_of_payments')->nullable();
            $table->text('form_of_payment')->nullable();
            $table->text('nature_of_payment')->nullable();
            $table->text('city_of_travel')->nullable();
            $table->text('state_of_travel')->nullable();
            $table->text('country_of_travel')->nullable();
            $table->text('physician_ownership_indicator')->nullable();
            $table->text('third_party_payment_recipient_indicator')->nullable();
            $table->text('name_of_third_party_receiving_payment')->nullable();
            $table->text('charity_indicator')->nullable();
            $table->text('third_party_equals_covered_recipient_indicator')->nullable();
            $table->text('contextual_information')->nullable();
            $table->text('delay_in_publication')->nullable();
            $table->text('dispute_status_for_publication')->nullable();
            $table->text('product_indicator')->nullable();
            $table->text('name_of_drug_or_biological1')->nullable();
            $table->text('name_of_drug_or_biological2')->nullable();
            $table->text('name_of_drug_or_biological3')->nullable();
            $table->text('name_of_drug_or_biological4')->nullable();
            $table->text('name_of_drug_or_biological5')->nullable();
            $table->text('ndc_of_drug_or_biological1')->nullable();
            $table->text('ndc_of_drug_or_biological2')->nullable();
            $table->text('ndc_of_drug_or_biological3')->nullable();
            $table->text('ndc_of_drug_or_biological4')->nullable();
            $table->text('ndc_of_drug_or_biological5')->nullable();
            $table->text('name_of_device_or_supply1')->nullable();
            $table->text('name_of_device_or_supply2')->nullable();
            $table->text('name_of_device_or_supply3')->nullable();
            $table->text('name_of_device_or_supply4')->nullable();
            $table->text('name_of_device_or_supply5')->nullable();
            $table->unsignedInteger('program_year')->nullable();
            $table->timestamp('payment_publication_date')->nullable();

            $table->foreign('dataset_id')->references('id')->on('datasets');
            $table->foreign('physician_id')->references('id')->on('physicians');
            $table->foreign('hospital_id')->references('id')->on('hospitals');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payments');
    }
}
