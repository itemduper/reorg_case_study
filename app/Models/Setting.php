<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    // TODO
    // use HasFactory;

    /**
     * Fillable attributes
     *
     * @var array
     */
    protected $fillable = ['name', 'value', 'type'];

    /**
     * Valid Types matched up to shorthand for easy use in Tinker.
     *
     * @var array
     */
    protected static $valid_types = [
        'int'       => 'integer',
        'integer'   => 'integer',
        'dec'       => 'double',
        'decimal'   => 'double',
        'float'     => 'double',
        'double'    => 'double',
        'bool'      => 'boolean',
        'boolean'   => 'boolean',
        'str'       => 'string',
        'string'    => 'string'
    ];

    /**
     * Sets a Setting of a given name with a value of a specified type.
     * 
     * @param string $name Name of the Setting.
     * @param string $value Value of the Setting.
     * @param string $type Type of the Setting.
     * @return Setting
     * @throws \TypeError Thrown if $value does not match the provided $type.
     * @throws \TypeError Thrown if $type is not in the $valid_types array.
     */
    public static function set($name, $value, $type = 'string') {
        if(isset(self::$valid_types[$type])) {
            // Normalize Shorthand Types
            $type = self::$valid_types[strtolower($type)];

            // Validate type matches
            if(self::validateType($value,$type)) {
                $setting = self::where('name',$name);
        
                // If setting already exists update it, otherwise create it.
                if($setting->exists()) {
                    $setting->first()->update(['value' => $value, 'type' => $type]);
        
                    return $setting->first();
                } else {
                    return self::create(['name' => $name, 'value' => $value, 'type' => $type]);
                }
            } else {
                throw new \TypeError("Value does not match the given type.");
            }
        } else {
            throw new \TypeError("Type '$type' is not valid.");
        }
    }

    /**
     * Unsets a Setting with a given name.
     *
     * @param string $name Name of Setting to Unset.
     * @param bool $errorIfNotFound When True, will throw a ModelNotFoundException if a Setting is not found under the given $name.
     * @return void
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException Thrown when $errorIfNotFound is set to true and a Setting is not found under the given $name.
     */
    public static function unset($name, $errorIfNotFound = false) {
        $setting = Setting::where('name',$name);

        if($setting->exists()) {
            $setting->first()->delete();
        } else if($errorIfNotFound) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException('This setting does not exist');
        }
    }

    /**
     * Gets the value of a Setting, or a default value if specified.
     * 
     * @param string $name Name of the Setting.
     * @param mixed $default Default value if the Setting does not exist.
     * @param bool $errorIfNotFound When True, will throw a ModelNotFoundException if a Setting is not found under the given $name.
     * @return mixed Value of the Setting.
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException Thrown when $errorIfNotFound is set to true and a Setting is not found under the given $name.
     */
    public static function get($name, $default = null, $errorIfNotFound = false) {
        $setting = Setting::where('name',$name);

        if($setting->exists()) {
            return $setting->first()->valueOf();
        } else if($errorIfNotFound) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException('This setting does not exist');
        } else {
            return $default;
        }
    }

    /**
     * Validates that a value can be parsed as the given type.
     * 
     * @param mixed $value Value to validate.
     * @param string $type Type to validate against.
     * @return bool
     */
    public static function validateType($value, $type) {
        try {
            switch(self::$valid_types[strtolower($type)]) {
                case 'integer':
                    return is_integer($value);
                    break;
                case 'double':
                    return is_double($value);
                    break;
                case 'boolean':
                    return is_bool($value);
                    break;
                case 'string':
                    return is_string($value);
                    break;
                default:
                    return false;
            }
        } catch(\Exception $e) {
            return false;
        }
    }

    /**
     * Retrieves the value of a Setting cast as the correct type.
     * 
     * @return mixed Value of the Setting.
     * @throws \TypeError Thrown when the type of the Setting does not match a known type.
     */
    public function valueOf() {
        switch($this->type) {
            case 'integer':
                return intval($this->value);
                break;
            case 'double':
                return floatval($this->value);
                break;
            case 'boolean':
                return boolval($this->value);
                break;
            case 'string':
                return $this->value;
                break;
            default:
                throw new \TypeError("Unable to return a value of the given type: $this->type");
        }
    }
}
