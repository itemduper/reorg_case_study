<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\Dataset;

class Payment extends Model
{
    // TODO
    // use HasFactory;

    /**
     * Guarded Attributes
     *
     * @var array
     */
    protected $guarded = ['id', 'created_at', 'updated_at'];

    /**
     * Attributes to remove when applicable.
     *
     * @var array
     */
    protected static $except = ['created_at', 'updated_at', 'dataset_id', 'physician_id', 'hospital_id'];

    /**
     * Array used to associate JSON keys from the OpenPayments API to attributes
     *
     * @var array
     */
    protected static $api_to_attribute_map = [
        'dataset_id'                                                        => 'dataset_id',
        'record_id'                                                         => 'record_id',
        'physician_id'                                                      => 'physician_id',
        'hospital_id'                                                       => 'hospital_id',
        'change_type'                                                       => 'change_type',
        'covered_recipient_type'                                            => 'covered_recipient_type',
        'submitting_applicable_manufacturer_or_applicable_gpo_name'	        => 'submitting_manufacturer_name',
        'applicable_manufacturer_or_applicable_gpo_making_payment_id'	    => 'applicable_manufacturer_id',
        'applicable_manufacturer_or_applicable_gpo_making_payment_name'	    => 'applicable_manufacturer_name',
        'applicable_manufacturer_or_applicable_gpo_making_payment_state'	=> 'applicable_manufacturer_state',
        'applicable_manufacturer_or_applicable_gpo_making_payment_country'	=> 'applicable_manufacturer_country',
        'total_amount_of_payment_usdollars'                                 => 'total_payment_usd',
        'date_of_payment'	                                                => 'date_of_payment',
        'number_of_payments_included_in_total_amount'	                    => 'number_of_payments',
        'form_of_payment_or_transfer_of_value'	                            => 'form_of_payment',
        'nature_of_payment_or_transfer_of_value'	                        => 'nature_of_payment',
        'city_of_travel'	                                                => 'city_of_travel',
        'state_of_travel'	                                                => 'state_of_travel',
        'country_of_travel'	                                                => 'country_of_travel',
        'physician_ownership_indicator'	                                    => 'physician_ownership_indicator',
        'third_party_payment_recipient_indicator'	                        => 'third_party_payment_recipient_indicator',
        'name_of_third_party_entity_receiving_payment_or_transfer_of_value'	=> 'name_of_third_party_receiving_payment',
        'charity_indicator'	                                                => 'charity_indicator',
        'third_party_equals_covered_recipient_indicator'	                => 'third_party_equals_covered_recipient_indicator',
        'contextual_information'	                                        => 'contextual_information',
        'delay_in_publication_indicator'	                                => 'delay_in_publication',
        'dispute_status_for_publication'	                                => 'dispute_status_for_publication',
        'product_indicator'	                                                => 'product_indicator',
        'name_of_associated_covered_drug_or_biological1'	                => 'name_of_drug_or_biological1',
        'name_of_associated_covered_drug_or_biological2'	                => 'name_of_drug_or_biological2',
        'name_of_associated_covered_drug_or_biological3'	                => 'name_of_drug_or_biological3',
        'name_of_associated_covered_drug_or_biological4'	                => 'name_of_drug_or_biological4',
        'name_of_associated_covered_drug_or_biological5'	                => 'name_of_drug_or_biological5',
        'ndc_of_associated_covered_drug_or_biological1'	                    => 'ndc_of_drug_or_biological1',
        'ndc_of_associated_covered_drug_or_biological2'	                    => 'ndc_of_drug_or_biological2',
        'ndc_of_associated_covered_drug_or_biological3'	                    => 'ndc_of_drug_or_biological3',
        'ndc_of_associated_covered_drug_or_biological4'	                    => 'ndc_of_drug_or_biological4',
        'ndc_of_associated_covered_drug_or_biological5'	                    => 'ndc_of_drug_or_biological5',
        'name_of_associated_covered_device_or_medical_supply1'	            => 'name_of_device_or_supply1',
        'name_of_associated_covered_device_or_medical_supply2'	            => 'name_of_device_or_supply2',
        'name_of_associated_covered_device_or_medical_supply3'	            => 'name_of_device_or_supply3',
        'name_of_associated_covered_device_or_medical_supply4'	            => 'name_of_device_or_supply4',
        'name_of_associated_covered_device_or_medical_supply5'	            => 'name_of_device_or_supply5',
        'program_year'	                                                    => 'program_year',
        'payment_publication_date'	                                        => 'payment_publication_date',
    ];

    /**
     * Generates or finds a Payment from JSON data provided by the OpenPayments API.
     *
     * @param array $api_data Payment data from the OpenPayments API
     * @return Payment
     */
    public static function updateOrCreateFromAPI($api_data) {
        $record = [];

        foreach($api_data as $key => $value) {
            $record[self::$api_to_attribute_map[$key]] = $value;
        }

        $payment = Payment::firstOrNew(['dataset_id' => $record['dataset_id'], 'record_id' => $record['record_id']]);

        foreach($record as $key => $value) {
            $payment->{$key} = $value;
        }

        $payment->save();

        return $payment;
    }
    
    /**
     * Gets the Dataset the Payment belongs to
     *
     * @return BelongsTo
     */
    public function dataset() {
        return $this->belongsTo(Dataset::class);
    }

    /**
     * Gets the Physician associated with the Payment.
     *
     * @return BelongsTo
     */
    public function physician() {
        return $this->belongsTo(Physician::class);
    }

    /**
     * Gets the Hospital associated with the Payment.
     *
     * @return BelongsTo
     */
    public function hospital() {
        return $this->belongsTo(Hospital::class);
    }

    /**
     * Returns the Recipient associated with the Payment, either a Physician or a Hospital
     *
     * @return BelongsTo
     */
    public function recipient() {
        if($this->physician_id != null) {
            return $this->belongsTo(Physician::class, 'physician_id');
        } else {
            return $this->belongsTo(Hospital::class, 'hospital_id');
        }
    }

    /**
     * Formats a Payment into an Array for end-user consumption.
     *
     * @param boolean $exclude_id If true removes the 'id' key
     * @return array
     */
    public function toRecord($exclude_id = false) {
        $output = array_diff_key($this->attributesToArray(), array_flip(self::$except));

        // Convert Address to a record and prepend 'address_' to its keys, then merge with output.
        $address = $this->recipient->address->toRecord(true);
        foreach($address as $key => $value) {
            unset($address[$key]);
            $address['address_'.$key] = $value;
        }
        $output = array_merge($address,$output);

        // Convert Recipient to a record and prepend 'recipient_' to its keys, then merge with output.
        $recipient = $this->recipient->toRecord(true);
        foreach($recipient as $key => $value) {
            unset($recipient[$key]);
            $recipient['recipient_'.$key] = $value;
        }
        $output = array_merge($recipient,$output);
        
        // Unset 'id' key if we want to exclude it, otherwise set it to the first column.
        if($exclude_id) {
            unset($output['id']);
        } else {
            $output = array_merge(['id' => $this->id],$output);
        }

        return $output;
    }
}
