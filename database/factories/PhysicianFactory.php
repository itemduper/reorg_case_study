<?php

namespace Database\Factories;

use App\Models\Physician;
use Illuminate\Database\Eloquent\Factories\Factory;

use App\Models\Address;

class PhysicianFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Physician::class;

    /**
     * Possible attribute values to randomly choose.
     *
     * @var array
     */
    protected static $attrs = [
        'primary_type' => [
            'Medical Doctor',
            'Doctor of Optometry',
            'Doctor of Dentistry',
            'Chiropractor',
            'Doctor of Osteopathy',
            'Doctor of Podiatric Medicine'
        ],
        'specialty' => [
            'Allopathic & Osteopathic Physicians|Emergency Medicine|Sports Medicine',
            'Ambulatory Health Care Facilities|Clinic/Center|Prison Health',
            'Allopathic & Osteopathic Physicians|Pediatrics|Sleep Medicine',
            'Hospitals|General Acute Care Hospital',
            'Allopathic & Osteopathic Physicians|Obstetrics & Gynecology|Obstetrics'
        ]
    ];

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $output = ['first_name' => $this->faker->firstName()];

        // 50/50 to add a middle name
        if(rand(0,1) === 1) $output['middle_name'] = $this->faker->firstName();

        $output['last_name'] = $this->faker->lastName();

        // 50/50 to add a suffix
        if(rand(0,1) === 1) $output['name_suffix'] = $this->faker->suffix();

        // Add a random primary_type and specialty
        $output['primary_type'] = array_rand(array_flip(self::$attrs['primary_type']));
        $output['specialty'] = array_rand(array_flip(self::$attrs['specialty']));

        // Add a minimum of 1, and randomly up to all 5 license_state_codes
        $output['license_state_code1'] = $this->faker->state();
        if(rand(0,1) === 1) {
            $output['license_state_code2'] = $this->faker->state();
            if(rand(0,1) === 1) {
                $output['license_state_code3'] = $this->faker->state();
                if(rand(0,1) === 1) {
                    $output['license_state_code4'] = $this->faker->state();
                    if(rand(0,1) === 1) {
                        $output['license_state_code5'] = $this->faker->state();
                    }
                }
            }
        }

        // Add a random cms_id
        $output['cms_id'] = rand(100,10000);

        // Generate an address
        $output['address_id'] = Address::factory();

        return $output;
    }
}
