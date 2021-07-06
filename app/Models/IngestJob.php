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
    use HasFactory;

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
     * Runs an Ingest Job in the selected mode.
     *
     * @param mixed $mode Mode to run an ingest job in, can be an integer or string matching any of the switched options.
     * @return void
     */
    public function run($mode = null) {
        if($mode === null) $mode = Setting::get('ingest_default_mode','serial');

        switch(strval($mode)) {
            case '0':
            case 'serial':
                $this->run_serial();
                break;
        }
    }

    /**
     * Runs ingest job in Serial Mode
     * This is *slow*, and the function is unnecessarily complex to save on database reads/writes.
     * This is *especially slow* running in WSL2 off of a mechanical hard disk.
     * 
     * TODO: Build a cache likely around Redis.
     * Also TODO: Learn how to use Redis because I've never had a reason to touch it before this.
     * 
     * Assumptions made that could potentially lead to inaccuracy:
     * 1) Physician and Hospital information will not change mid ingestion
     * 2) Physician and Hospital Addresses will remain the same for a given physician_profile_id or teaching_hospital_id
     * 
     * Doing this the more accurate and easier to read way will take two weeks to write to the database on my computer.
     *
     * @return bool Returns True on a successful job and False if a Fatal Error occurs.
     */
    public function run_serial() {
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

        // Initialize map for known Physician and Hospital ID's
        $physician_id_map = [];
        $hospital_id_map = [];

        // Mark Ingest Job as In Progress
        $this->in_progress = true;
        $this->save();

        do {
            try {
                // Build Query Arguments
                $arguments = '?$order=record_id&$limit='.$query_limit.'&$offset='.intval($this->offset);

                IngestEvent::log($this,"Requesting at offset ".intval($this->offset)." - Query: $endpoint$arguments",3);

                // Initialize HTTP Request
                $response = Http::timeout($timeout)->acceptJson()->get($endpoint.$arguments);
    
                IngestEvent::log($this,"Response at offset ".intval($this->offset)." received, writing payments to table - Query: $endpoint$arguments");

                // Attempt to create payments for JSON objects in $response, log errors on failure.
                foreach($response->json() as $record) {
                    try {
                        // Map contents of API data to the individual models the data will be stored in.
                        $address_data = [];
                        $physician_data = [];
                        $hospital_data = [];
                        $payment_data = [];
                
                        foreach($record as $key => $value) {
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

                        // Check if record is a physician or a hospital
                        if(isset($record['physician_profile_id'])) {
                            // If Physician Profile ID is stored in the ID map, skip creating/checking for this run and append ID to payment data.
                            if(isset($physician_id_map[$record['physician_profile_id']])) {
                                $payment_data += ['physician_id' => $physician_id_map[$record['physician_profile_id']]];
                            } else {
                                // Find or Create Address
                                $address = Address::firstOrCreateFromAPI($address_data);

                                // Find or Create Physician
                                $physician = Physician::updateOrCreateFromAPI(['address_id' => $address->id] + $physician_data);
                                $payment_data += ['physician_id' => $physician->id];
                                $physician_id_map[$physician->cms_id] = $physician->id;
                            }
                        } else if(isset($record['teaching_hospital_id'])) {
                            // If Teaching Hospital ID is stored in the ID map, skip creating/checking for this run and append ID to payment data.
                            if(isset($hospital_id_map[$record['teaching_hospital_id']])) {
                                $payment_data += ['hospital_id' => $hospital_id_map[$record['teaching_hospital_id']]];
                            } else {
                                // Find or Create Address
                                $address = Address::firstOrCreateFromAPI($address_data);

                                // Find or Create Hospital
                                $hospital = Hospital::updateOrCreateFromAPI(['address_id' => $address->id] + $hospital_data);
                                $payment_data += ['hospital_id' => $hospital->id];
                                $hospital_id_map[$hospital->cms_id] = $hospital->id;
                            }
                        }

                        // Write Payment
                        Payment::updateOrCreateFromAPI(['dataset_id' => $this->dataset->id] + $payment_data);
                    } catch(\Exception $e) {
                        IngestEvent::log($this,"Error while ingesting payment: $e",1);
                    }
                }
    
                IngestEvent::log($this,"Completed writing payments at offset ".intval($this->offset)." from query $endpoint$arguments",3);

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
                IngestEvent::log($this,"Timed out at offset ".intval($this->offset)." on query $endpoint$arguments - Consecutive Timeouts: $timeouts - Total Timeouts: $total_timeouts",2);

                // Set $response_count to match $query_limit so the loop retries
                $response_count = $query_limit;
            } catch(\Exception $e) {
                // A fatal error occurred, log it and stop running.
                IngestEvent::log($this,"Fatal Error: $e",0);
                return false;
            }
        } while($response_count >= $query_limit && $timeouts < $max_consecutive_timeouts && $total_timeouts < $max_total_timeouts && ($max_queries == -1 || $queries < $max_queries));

        // Mark Ingest Job as no longer in progress and completed if this didn't complete due to max_queries.
        if($max_queries != -1 && $queries >= $max_queries) {
            IngestEvent::log($this,"Ingest Job ended due to hitting max queries ($max_queries) at offset ".intval($this->offset).".");
        } else {
            $this->completeJob();
        }

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

// ABANDON ALL HOPE YE WHO ENTER HERE
    // /**
    //  * Runs this Ingest Job.
    //  *
    //  * @return bool Returns True on a successful job and False if a Fatal Error occurs.
    //  */
    // public function run() {
    //     $endpoint = $this->dataset->api_endpoint;

    //     if($this->offset > $this->starting_offset) {
    //         IngestEvent::log($this,"Resuming Ingest Job on endpoint ".$this->dataset->api_endpoint);
    //     } else if($this->starting_offset > 0) {
    //         IngestEvent::log($this,"Starting Ingest Job at offset ".intval($this->starting_offset)." on endpoint ".$this->dataset->api_endpoint);
    //     } else {
    //         IngestEvent::log($this,"Starting Ingest Job on endpoint ".$this->dataset->api_endpoint);
    //     }

    //     $query_limit = Setting::get('ingest_query_limit',1000);
    //     $timeout = Setting::get('ingest_timeout',300);
    //     $max_consecutive_timeouts = Setting::get('ingest_max_consecutive_timeouts',3);
    //     $max_total_timeouts = Setting::get('ingest_max_total_timeouts',10);
    //     $timeouts = 0;
    //     $total_timeouts = 0;
    //     $queries = 0;
    //     $max_queries = Setting::get('ingest_max_queries',-1);

    //     // Mark Ingest Job as In Progress
    //     $this->in_progress = true;
    //     $this->save();

    //     do {
    //         try {
    //             // Build Query Arguments
    //             $arguments = '?$order=record_id&$limit='.$query_limit.'&$offset='.intval($this->offset);

    //             IngestEvent::log($this,"Requesting at offset ".intval($this->offset)." - Query: $endpoint$arguments",3);

    //             // Initialize HTTP Request
    //             $response = Http::timeout($timeout)->acceptJson()->get($endpoint.$arguments);
    
    //             IngestEvent::log($this,"Response at offset ".intval($this->offset)." received, writing payments to table - Query: $endpoint$arguments");

    //             // Attempt to create payments for JSON objects in $response, log errors on failure.
    //             foreach($response->json() as $api_data) {
    //                 try {
    //                     $this->ingestPayment($api_data);
    //                 } catch(\Exception $e) {
    //                     IngestEvent::log($this,"Error while ingesting payment: $e",1);
    //                 }
    //             }
    
    //             IngestEvent::log($this,"Completed writing payments at offset ".intval($this->offset)." from query $endpoint$arguments",3);

    //             // Update Offset in case job fails and is resumed later.
    //             $response_count = count($response->json());
    //             $this->offset += $response_count;
    //             $this->save();

    //             // Reset Consecutive Timeouts
    //             $timeouts = 0;

    //             // Increment Queries
    //             $queries++;
    //         } catch(\Illuminate\Http\Client\ConnectionException $e) {
    //             // A timeout occurred, increment timeouts and log a warning.
    //             $timeouts++;
    //             $total_timeouts++;
    //             IngestEvent::log($this,"Timed out at offset ".intval($this->offset)." on query $endpoint$arguments - Consecutive Timeouts: $timeouts - Total Timeouts: $total_timeouts",2);

    //             // Set $response_count to match $query_limit so the loop retries
    //             $response_count = $query_limit;
    //         } catch(\Exception $e) {
    //             // A fatal error occurred, log it and stop running.
    //             IngestEvent::log($this,"Fatal Error: $e",0);
    //             return false;
    //         }
    //     } while($response_count >= $query_limit && $timeouts < $max_consecutive_timeouts && $total_timeouts < $max_total_timeouts && ($max_queries == -1 || $queries < $max_queries));

    //     // Mark Ingest Job as no longer in progress and completed if this didn't complete due to max_queries.
    //     if($max_queries != -1 && $queries >= $max_queries) {
    //         IngestEvent::log($this,"Ingest Job ended due to hitting max queries ($max_queries) at offset ".intval($this->offset).".");
    //     } else {
    //         $this->completeJob();
    //     }

    //     return true;
    // }

    // /**
    //  * Ingests an individual Payment and its associated models from JSON data provided by the OpenPayments API
    //  *
    //  * @param array $api_data API Data from the OpenPayments API
    //  * @return void
    //  */
    // public function ingestPayment($api_data) {
    //     // Map contents of API data to the individual models the data will be stored in.
    //     $address_data = [];
    //     $physician_data = [];
    //     $hospital_data = [];
    //     $payment_data = [];

    //     foreach($api_data as $key => $value) {
    //         switch(self::$api_to_model_map[$key]) {
    //             case 'address':
    //                 $address_data[$key] = $value;
    //                 break;
    //             case 'physician':
    //                 $physician_data[$key] = $value;
    //                 break;
    //             case 'hospital':
    //                 $hospital_data[$key] = $value;
    //                 break;
    //             case 'payment':
    //                 $payment_data[$key] = $value;
    //                 break;
    //         }
    //     }

    //     $address = Address::firstOrCreateFromAPI($address_data);
    //     $address_id = $address->id;

    //     $physician = null;
    //     if(count($physician_data) > 0) {
    //         $physician = Physician::updateOrCreateFromAPI(['address_id' => $address_id] + $physician_data);
    //     }

    //     $hospital = null;
    //     if(count($hospital_data) > 0) {
    //         $hospital = Hospital::updateOrCreateFromAPI(['address_id' => $address_id] + $hospital_data);
    //     }

    //     if($physician != null) $payment_data += ['physician_id' => $physician->id];
    //     if($hospital != null) $payment_data += ['hospital_id' => $hospital->id];
    //     Payment::updateOrCreateFromAPI(['dataset_id' => $this->dataset->id] + $payment_data);
    // }