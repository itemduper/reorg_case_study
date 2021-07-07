<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\Address;

class Physician extends Model
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
        'address_id'                    => 'address_id',
        'physician_profile_id'          => 'cms_id',
        'physician_first_name'          => 'first_name',
        'physician_middle_name'         => 'middle_name',
        'physician_last_name'           => 'last_name',
        'physician_name_suffix'         => 'name_suffix',
        'physician_primary_type'        => 'primary_type',
        'physician_specialty'           => 'specialty',
        'physician_license_state_code1' => 'license_state_code1',
        'physician_license_state_code2' => 'license_state_code2',
        'physician_license_state_code3' => 'license_state_code3',
        'physician_license_state_code4' => 'license_state_code4',
        'physician_license_state_code5' => 'license_state_code5',
    ];

    /**
     * Creates or locates a Physician from JSON data provided by the OpenPayments API
     *
     * @param array $api_data Physician data from the OpenPayments API
     * @return Physician
     */
    public static function updateOrCreateFromAPI($api_data) {
        $record = [];

        foreach($api_data as $key => $value) {
            $record[self::$api_to_attributes_map[$key]] = $value;
        }

        $physician = self::firstOrNew(['cms_id' => $record['cms_id']]);

        foreach($record as $key => $value) {
            $physician->{$key} = $value;
        }

        $physician->save();

        return $physician;
    }

    /**
     * Returns the address of the Physician.
     *
     * @return BelongsTo
     */
    public function address() {
        return $this->belongsTo(Address::class);
    }

    /**
     * Returns Payments associated with the Physician
     *
     * @return HasMany
     */
    public function payments() {
        return $this->hasMany(Payment::class);
    }

    public function getName() {
        $name = $this->first_name;
        if(strlen($this->middle_name) > 0) $name.= " $this->middle_name";
        $name.= " $this->last_name";
        if(strlen($this->name_suffix) > 0) $name.= " $this->name_suffix";

        return $name;
    }

    /**
     * Formats a Physician into an Array for end-user consumption.
     *
     * @param boolean $exclude_id If true removes the 'id' key
     * @param boolean $include_type If true includes a 'type' key with a value of 'physician'
     * @return array
     */
    public function toRecord($exclude_id = false, $include_type = false) {
        $output = array_diff_key($this->attributesToArray(), array_flip(array_merge(self::$except,['first_name', 'middle_name', 'last_name', 'name_suffix'])));

        // Reorder Array to fit 'name' key after 'cms_id'
        $output = array_merge(['id' => $this->id, 'cms_id' => $this->cms_id, 'name' => $this->getName()], $output);

        if($exclude_id) unset($output['id']);
        if($include_type) $output['type'] = 'physician';

        return $output;
    }
}
