<?php

namespace Database\Factories;

use App\Models\Setting;
use Illuminate\Database\Eloquent\Factories\Factory;

class SettingFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Setting::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $output = ['name' => $this->faker->word()];

        switch(rand(0,3)) {
            case 0:
                $output['type'] = 'integer';
                $output['value'] = rand(1,10000);
                break;
            case 1:
                $output['type'] = 'double';
                $output['value'] = rand(10,100000) / 10;
                break;
            case 2:
                $output['type'] = 'boolean';
                $output['value'] = (rand(0,1) === 1) ? true : false;
                break;
            case 3:
                $output['type'] = 'string';
                $output['value'] = $this->faker->word();
                break;
        }

        return $output;
    }
}
