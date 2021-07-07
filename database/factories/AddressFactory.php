<?php

namespace Database\Factories;

use App\Models\Address;
use Illuminate\Database\Eloquent\Factories\Factory;

class AddressFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Address::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $output = ['street_line1' => $this->faker->streetAddress()];

        // 50/50 to add a secondary address
        if(rand(0,1) === 1) $output['street_line2'] = $this->faker->secondaryAddress();

        // Separated this from the original array declaration for OCD reasons
        $output['city'] = $this->faker->city();

        // Heads create address in the US, tails create it in Canada
        if(rand(0,1) === 1) {
            $output['state'] = $this->faker->state();
            $output['postal_code'] = $this->faker->postcode();
            $output['country'] = 'United States';
        } else {
            $faker_en_CA = \Faker\Factory::create('en_CA');
            $output['province'] = $faker_en_CA->province();
            $output['postal_code'] = $faker_en_CA->postcode();
            $output['country'] = 'Canada';
        }

        return $output;
    }
}
