<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\Address;

class Hospital extends Model
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
    protected static $except = ['created_at', 'updated_at', 'address_id'];

    /**
     * Map used to associate JSON keys from the OpenPayments API to attributes
     *
     * @var array
     */
    protected static $api_to_attributes_map = [
        'address_id'                => 'address_id',
        'teaching_hospital_id'      => 'cms_id',
        'teaching_hospital_ccn'     => 'ccn',
        'teaching_hospital_name'    => 'name',
    ];

    /**
     * Updates or creates a Hospital from JSON data provided by the OpenPayments API
     *
     * @param array $api_data Hospital data from the OpenPayments API
     * @return Hospital
     */
    public static function updateOrCreateFromAPI($api_data) {
        $record = [];

        foreach($api_data as $key => $value) {
            $record[self::$api_to_attributes_map[$key]] = $value;
        }

        $hospital = self::firstOrNew(['cms_id' => $record['cms_id']]);

        foreach($record as $key => $value) {
            $hospital->{$key} = $value;
        }

        $hospital->save();

        return $hospital;
    }

    /**
     * Returns the address of the hospital.
     *
     * @return BelongsTo
     */
    public function address() {
        return $this->belongsTo(Address::class);
    }

    /**
     * Returns Payments associated with the Hospital
     *
     * @return HasMany
     */
    public function payments() {
        return $this->hasMany(Payment::class);
    }

    /**
     * Formats a Hospital into an Array for end-user consumption.
     *
     * @param boolean $exclude_id If true removes the 'id' key
     * @param boolean $include_type If true includes a 'type' key with a value of 'hospital'
     * @return array
     */
    public function toRecord($exclude_id = false, $include_type = false) {
        $output = array_diff_key($this->attributesToArray(), array_flip(self::$except));

        if($exclude_id) unset($output['id']);
        if($include_type) $output['type'] = 'hospital';

        return $output;
    }
}
