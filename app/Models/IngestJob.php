<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;

use App\Models\Dataset;
use App\Models\IngestEvent;
use App\Models\Setting;

use App\Models\Payment;
use App\Models\Address;
use App\Models\Physician;
use App\Models\Hospital;

class IngestJob extends Model
{
    // TODO
    // use HasFactory;

    /**
     * Fillable attributes
     *
     * @var array
     */
    protected $fillable = ['dataset_id'];

    /**
     * Cast attributes as the correct type.
     *
     * @var array
     */
    protected $casts = [
        'in_progress' => 'boolean',
        'offset' => 'integer',
        'starting_offset' => 'integer',
    ];

    /**
     * Map of OpenPayments JSON keys to the models we want to store their data in.
     *
     * @var array
     */
    protected static $api_to_model_map = [
        'record_id'                                                         => 'payment',
        'change_type'                                                       => 'payment',
        'covered_recipient_type'	                                        => 'payment',
        'teaching_hospital_ccn'	                                            => 'hospital',
        'teaching_hospital_id'	                                            => 'hospital',
        'teaching_hospital_name'	                                        => 'hospital',
        'physician_profile_id'	                                            => 'physician',
        'physician_first_name'	                                            => 'physician',
        'physician_middle_name'	                                            => 'physician',
        'physician_last_name'	                                            => 'physician',
        'physician_name_suffix'	                                            => 'physician',
        'recipient_primary_business_street_address_line1'	                => 'address',
        'recipient_primary_business_street_address_line2'	                => 'address',
        'recipient_city'	                                                => 'address',
        'recipient_state'	                                                => 'address',
        'recipient_zip_code'	                                            => 'address',
        'recipient_country'	                                                => 'address',
        'recipient_province'	                                            => 'address',
        'recipient_postal_code'	                                            => 'address',
        'physician_primary_type'	                                        => 'physician',
        'physician_specialty'	                                            => 'physician',
        'physician_license_state_code1'	                                    => 'physician',
        'physician_license_state_code2'	                                    => 'physician',
        'physician_license_state_code3'	                                    => 'physician',
        'physician_license_state_code4'	                                    => 'physician',
        'physician_license_state_code5'	                                    => 'physician',
        'submitting_applicable_manufacturer_or_applicable_gpo_name'	        => 'payment',
        'applicable_manufacturer_or_applicable_gpo_making_payment_id'	    => 'payment',
        'applicable_manufacturer_or_applicable_gpo_making_payment_name'	    => 'payment',
        'applicable_manufacturer_or_applicable_gpo_making_payment_state'	=> 'payment',
        'applicable_manufacturer_or_applicable_gpo_making_payment_country'	=> 'payment',
        'total_amount_of_payment_usdollars'                                 => 'payment',
        'date_of_payment'	                                                => 'payment',
        'number_of_payments_included_in_total_amount'	                    => 'payment',
        'form_of_payment_or_transfer_of_value'	                            => 'payment',
        'nature_of_payment_or_transfer_of_value'	                        => 'payment',
        'city_of_travel'	                                                => 'payment',
        'state_of_travel'	                                                => 'payment',
        'country_of_travel'	                                                => 'payment',
        'physician_ownership_indicator'	                                    => 'payment',
        'third_party_payment_recipient_indicator'	                        => 'payment',
        'name_of_third_party_entity_receiving_payment_or_transfer_of_value'	=> 'payment',
        'charity_indicator'	                                                => 'payment',
        'third_party_equals_covered_recipient_indicator'	                => 'payment',
        'contextual_information'	                                        => 'payment',
        'delay_in_publication_indicator'	                                => 'payment',
        'dispute_status_for_publication'	                                => 'payment',
        'product_indicator'	                                                => 'payment',
        'name_of_associated_covered_drug_or_biological1'	                => 'payment',
        'name_of_associated_covered_drug_or_biological2'	                => 'payment',
        'name_of_associated_covered_drug_or_biological3'	                => 'payment',
        'name_of_associated_covered_drug_or_biological4'	                => 'payment',
        'name_of_associated_covered_drug_or_biological5'	                => 'payment',
        'ndc_of_associated_covered_drug_or_biological1'	                    => 'payment',
        'ndc_of_associated_covered_drug_or_biological2'	                    => 'payment',
        'ndc_of_associated_covered_drug_or_biological3'	                    => 'payment',
        'ndc_of_associated_covered_drug_or_biological4'	                    => 'payment',
        'ndc_of_associated_covered_drug_or_biological5'	                    => 'payment',
        'name_of_associated_covered_device_or_medical_supply1'	            => 'payment',
        'name_of_associated_covered_device_or_medical_supply2'	            => 'payment',
        'name_of_associated_covered_device_or_medical_supply3'	            => 'payment',
        'name_of_associated_covered_device_or_medical_supply4'	            => 'payment',
        'name_of_associated_covered_device_or_medical_supply5'	            => 'payment',
        'program_year'	                                                    => 'payment',
        'payment_publication_date'	                                        => 'payment',
    ];

    /**
     * Status Codes for Ingest Jobs
     *
     * @var array
     */
    protected static $status_codes = [
        'complete' => 0,
        'failed' => 1,
        'in_progress' => 2,
        'hit_max_queries' => 3,
        'new_publication_date' => 4,
    ];

    /**
     * Initiates ingestion for a given Dataset
     *
     * @param Dataset $dataset Dataset to ingest
     * @return void
     */
    public static function ingest(Dataset $dataset) {
        // Check for an incomplete job, and run it if one is found.
        $job = $dataset->ingest_jobs()->where('in_progress',true);

        if($job->exists()) {
            $job->first()->run();
        } else {
            $job = new IngestJob;

            // Determine if jobs have previously been completed for this dataset
            // Set offset to where the last completed job ended at
            $last_complete_job = $dataset->ingest_jobs()->orderByDesc('completed_at');
            if($last_complete_job->exists()) {
                $job->offset = $last_complete_job->first()->offset;
            } else {
                $job->offset = 0;
            }

            $job->starting_offset = $job->offset;
            $job->dataset()->associate($dataset);

            $job->save();

            $job->run();
        }
    }

    /**
     * Determine the arguments for and run this Ingest Job.
     *
     * @return void
     */
    public function run() {
        $status = -1;

        do {
            if($this->is_initial) {
                $additional_arguments = '';
            } else {
                // If this is not an initial ingest, we only care about change_types CHANGED, ADD, and NEW
                $additional_arguments = '&$where=change_type+in(\'CHANGED\',\'ADD\',\'NEW\')';
            }

            $status = $this->run_serial($additional_arguments);

            // Check to see if $status is one where we need to adjust the IngestJob
            switch($status) {
                // new_publication_date
                case 4:
                    IngestEvent::log($this,'Restarting Job due to new payment_publication_date being detected.');

                    // If this is not an initial ingestion, check to see if a complete job exists at this publicaton_date
                    if(!$this->is_initial) {
                        $complete_job = IngestJob::where('publication_date',$this->publication_date)->whereNotNull('completed_at');

                        // If no complete job exists of the last known publication_date, we need to re-ingest the entire dataset to be sure nothing was missed.
                        if(!$complete_job->exists()) {
                            IngestEvent::log($this,"No previously completed jobs exist for publication_date '$this->publication_date', re-ingesting the entire dataset.",2);
                            $this->is_initial = true;
                        }
                    }

                    $this->publication_date = null;
                    $this->offset = 0;
                    $this->starting_offset = 0;

                    $this->save();

                    break;
                // Something went wrong.  Set $status to failed (1)
                case -1:
                    $status = 1;
                    break;
            }

        // Re-run job while status is not complete, failed, or hit_max_queries
        } while(!in_array($status,[0,1,3]));
    }

    /**
     * Runs ingest job in Serial Mode
     *
     * @param string $additional_arguments Additional Arguments to provide on the query
     * @return bool Returns True on a successful job and False if a Fatal Error occurs.
     */
    public function run_serial($additional_arguments = '') {
        $endpoint = $this->dataset->api_endpoint;

        if($this->offset > $this->starting_offset) {
            IngestEvent::log($this,"Resuming Serial Ingest Job on endpoint ".$this->dataset->api_endpoint);
        } else if($this->starting_offset > 0) {
            IngestEvent::log($this,"Starting Serial Ingest Job at offset ".intval($this->starting_offset)." on endpoint ".$this->dataset->api_endpoint);
        } else {
            IngestEvent::log($this,"Starting Serial Ingest Job on endpoint ".$this->dataset->api_endpoint);
        }

        $query_limit = Setting::get('ingest_query_limit',1000);
        $timeout = Setting::get('ingest_timeout',300);
        $max_consecutive_timeouts = Setting::get('ingest_max_consecutive_timeouts',3);
        $max_total_timeouts = Setting::get('ingest_max_total_timeouts',10);
        $timeouts = 0;
        $total_timeouts = 0;
        $queries = 0;
        $max_queries = Setting::get('ingest_max_queries',-1);

        // Mark Ingest Job as In Progress
        $this->in_progress = true;
        $this->save();

        do {
            try {
                // Build Query Arguments
                $offset = intval($this->offset);
                $arguments = '?$order=record_id&$limit='.$query_limit.'&$offset='.$offset.$additional_arguments;

                IngestEvent::log($this,"Requesting at offset ".$offset." - Query: $endpoint$arguments",3);

                // Initialize HTTP Request
                $response = Http::timeout($timeout)->acceptJson()->get($endpoint.$arguments);
    
                IngestEvent::log($this,"Response at offset ".$offset." received, writing payments to table - Query: $endpoint$arguments");

                // Attempt to create payments for JSON objects in $response, log errors on failure.
                foreach($response->json() as $record) {
                    try {
                        if($record['payment_publication_date'] != $this->publication_date) {
                            if($this->publication_date == null) {
                                IngestEvent::log($this,"Setting publication_date for Ingest Job to: $record[payment_publication_date]");

                                $this->publication_date = $record['payment_publication_date'];
                                $this->save();
                            } else {
                                IngestEvent::log($this,"New payment_publication_date detected: $record[payment_publication_date]");
                                return self::$status_codes['new_publication_date'];
                            }
                        }

                        // Attempt to ingest payment, if it fails consider that a fatal error.
                        if(!$this->ingestPayment($record)) {
                            IngestEvent::($this,'Unable to ingest payment: '.print_r($record,true),0);
                            return self::$status_codes['failed'];
                        }
                    } catch(\Exception $e) {
                        IngestEvent::log($this,"Error while ingesting payment: $e",1);

                        try {
                            IngestEvent::log($this,"Record being ingested when previous error occurred: ".print_r($record,true),1);
                        } catch(\Exception $e) {
                            IngestEvent::log($this,"Unable to log the record that was being ingested on the previous error - Exception: $e",1);
                        }
                    }
                }
    
                IngestEvent::log($this,"Completed writing payments at offset ".$offset." from query $endpoint$arguments",3);

                // Update Offset in case job fails and is resumed later.
                $response_count = count($response->json());
                $this->offset += $response_count;
                $this->save();

                // Reset Consecutive Timeouts
                $timeouts = 0;

                // Increment Queries
                $queries++;
            } catch(\Illuminate\Http\Client\ConnectionException $e) {
                // A timeout occurred, increment timeouts and log a warning.
                $timeouts++;
                $total_timeouts++;
                IngestEvent::log($this,"Timed out at offset ".$offset." on query $endpoint$arguments - Consecutive Timeouts: $timeouts - Total Timeouts: $total_timeouts",2);

                // Set $response_count to match $query_limit so the loop retries
                $response_count = $query_limit;
            } catch(\Exception $e) {
                // A fatal error occurred, log it and stop running.
                IngestEvent::log($this,"Fatal Error: $e",0);
                return self::$status_codes['failed'];
            }
        } while($response_count >= $query_limit && $timeouts < $max_consecutive_timeouts && $total_timeouts < $max_total_timeouts && ($max_queries == -1 || $queries < $max_queries));

        // Mark Ingest Job as no longer in progress and completed if this didn't complete due to max_queries.
        if($max_queries != -1 && $queries >= $max_queries) {
            IngestEvent::log($this,"Ingest Job ended due to hitting max queries ($max_queries) at offset ".$offset.".");
            return self::$status_codes['hit_max_queries'];
        } else {
            $this->completeJob();
            return self::$status_codes['complete'];
        }
    }

    /**
     * Ingests an individual Payment and its associated models from JSON data provided by the OpenPayments API
     *
     * @param array $api_data API Data from the OpenPayments API
     * @return boolean Returns false if unable to ingest the payment.
     */
    public function ingestPayment($api_data) {
        // Map contents of API data to the individual models the data will be stored in.
        $address_data = [];
        $physician_data = [];
        $hospital_data = [];
        $payment_data = [];

        foreach($api_data as $key => $value) {
            if(!isset(self::$api_to_model_map[$key])) {
                IngestEvent::log($this,"Unknown key in record: $key",0);
                return false;
            }

            switch(self::$api_to_model_map[$key]) {
                case 'address':
                    $address_data[$key] = $value;
                    break;
                case 'physician':
                    $physician_data[$key] = $value;
                    break;
                case 'hospital':
                    $hospital_data[$key] = $value;
                    break;
                case 'payment':
                    $payment_data[$key] = $value;
                    break;
            }
        }

        $address = Address::firstOrCreateFromAPI($address_data);

        $physician = null;
        if(count($physician_data) > 0) {
            $physician = Physician::updateOrCreateFromAPI(['address_id' => $address->id] + $physician_data);
        }

        $hospital = null;
        if(count($hospital_data) > 0) {
            $hospital = Hospital::updateOrCreateFromAPI(['address_id' => $address->id] + $hospital_data);
        }

        if($physician != null) $payment_data += ['physician_id' => $physician->id];
        if($hospital != null) $payment_data += ['hospital_id' => $hospital->id];
        Payment::updateOrCreateFromAPI(['dataset_id' => $this->dataset->id] + $payment_data);

        return true;
    }

    /**
     * Mark this Ingest Job as complete.
     *
     * @return void
     */
    public function completeJob() {
        $this->in_progress = false;
        $this->completed_at = now();
        $this->save();

        IngestEvent::log($this,"Ingest Job completed at offset ".intval($this->offset).".");
    }

    /**
     * Returns the Dataset associated with this job.
     *
     * @return BelongsTo
     */
    public function dataset() {
        return $this->belongsTo(Dataset::class);
    }

    /**
     * Returns Ingest Events associated with this job.
     *
     * @return HasMany
     */
    public function events() {
        return $this->hasMany(IngestEvent::class);
    }
}
