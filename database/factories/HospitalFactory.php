<?php

namespace Database\Factories;

use App\Models\Hospital;
use Illuminate\Database\Eloquent\Factories\Factory;

use App\Models\Address;

class HospitalFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Hospital::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->company(),
            'ccn' => $this->faker->unique()->numberBetween(50000,500000),
            'cms_id' => $this->faker->unique()->numberBetween(100,10000) + 200000000,
            'address_id' => Address::factory()
        ];
    }
}
