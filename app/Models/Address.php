<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    use HasFactory;

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
    protected static $except = ['created_at', 'updated_at'];

    /**
     * Map used to associate JSON keys from the OpenPayments API to attributes
     *
     * @var array
     */
    protected static $api_to_attributes_map = [
        'recipient_primary_business_street_address_line1'   => 'street_line1',
        'recipient_primary_business_street_address_line2'   => 'street_line2',
        'recipient_city'                                    => 'city',
        'recipient_state'                                   => 'state',
        'recipient_zip_code'                                => 'postal_code',
        'recipient_country'                                 => 'country',
        'recipient_province'                                => 'province',
        'recipient_postal_code'                             => 'postal_code',
    ];

    /**
     * Creates or locates an Address from JSON data provided by the OpenPayments API
     *
     * @param array $api_data Address data from the OpenPayments API
     * @return Address
     */
    public static function firstOrCreateFromAPI($api_data) {
        $record = [];

        foreach($api_data as $key => $value) {
            $record[self::$api_to_attributes_map[$key]] = $value;
        }

        return self::firstOrCreate($record);
    }

    /**
     * Format address into a single line
     *
     * @param boolean $include_country If true, includes the country at the end of the address
     * @return string
     */
    public function formatAddress($include_country = true) {
        $address = $this->street_line1;
        if(strlen($this->street_line2) > 0) $address.= ' '.$this->street_line2;

        $address.= ' '.$this->city;

        if(strlen($this->state) > 0) {
            $address.= ' '.$this->state;
        } else if(strlen($this->province) > 0) {
            $address.= ' '.$this->province;
        }

        $address.= ' '.$this->postal_code;
        if($include_country) $address.= ' '.$this->country;

        return $address;
    }

    /**
     * Formats an Address into an Array for end-user consumption.
     *
     * @param boolean $exclude_id If true removes the 'id' key
     * @param boolean $include_type If true includes a 'type' key with a value of 'address'
     * @return array
     */
    public function toRecord($exclude_id = false, $include_type = false) {
        $output = array_diff_key($this->attributesToArray(), array_flip(self::$except));

        if($exclude_id) unset($output['id']);
        if($include_type) $output['type'] = 'address';

        return $output;
    }
}
