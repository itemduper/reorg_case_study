<?php

namespace Database\Factories;

use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

use App\Models\Physician;
use App\Models\Hospital;

class PaymentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Payment::class;

    /**
     * Possible attribute values to randomly choose.
     *
     * @var array
     */
    protected static $attrs = [
        'change_type' => [
            'CHANGED',
            'ADD',
            'NEW',
            'UNCHANGED'
        ],
        'form_of_payment' => [
            'Cash or cash equivalent',
            'In-kind items and services',
            'Dividend, profit or other return on investment',
            'Stock, stock option, or any other ownership interest'
        ],
        'nature_of_payment' => [
            'Consulting Fee',
            'Travel and Lodging',
            'Royalty or License',
            'Education',
            'Grant',
            'Gift',
            'Food and Beverage',
            'Honoraria',
            'Charitable Contribution',
            'Compensation for services other than consulting, including serving as faculty or as a speaker at a venue other than a continuing education program',
            'Space rental or facility fees (teaching hospital only)',
            'Entertainment',
            'Current or prospective ownership or investment interest',
            'Compensation for serving as faculty or as a speaker for a non-accredited and noncertified continuing education program',
            'Compensation for serving as faculty or as a speaker for an accredited or certified continuing education program'
        ],
        'contextual_information' => [
            'Travel related expenses',
            'Airfare',
            'Lodging',
            'Business meal',
            'Loaned equipment'
        ],
        'product_indicator' => [
            'Covered',
            'None',
            'Non-Covered',
            'Combination'
        ]
    ];

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $output = [
            'record_id' => rand(1000,100000),
            'change_type' => array_rand(array_flip(self::$attrs['change_type']))
        ];

        // 50/50 on if this is a payment to a Physician or a Hospital
        if(rand(0,1) === 1) {
            $output['physician_id'] = Physician::factory();
            $output['covered_recipient_type'] = 'Covered Recipient Physician';
        } else {
            $output['hospital_id'] = Hospital::factory();
            $output['covered_recipient_type'] = 'Covered Recipient Teaching Hospital';
        }

        $output['submitting_manufacturer_name'] = $this->faker->company();
        $output['applicable_manufacturer_id'] = '1000000'.rand(10000,99999);
        $output['applicable_manufacturer_name'] = $this->faker->company();
        $output['applicable_manufacturer_state'] = $this->faker->state();
        $output['applicable_manufacturer_country'] = 'United States';
        $output['total_payment_usd'] = rand(100,10000000) / 10;
        $output['date_of_payment'] = $this->faker->date('2015-m-d 00:00:00');
        $output['number_of_payments'] = rand(1,100);
        $output['form_of_payment'] = array_rand(array_flip(self::$attrs['form_of_payment']));
        $output['nature_of_payment'] = array_rand(array_flip(self::$attrs['nature_of_payment']));
        
        // If Payment is Travel and Lodging, fill in City/State/Country
        if($output['nature_of_payment'] == 'Travel and Lodging') {
            $output['city_of_travel'] = $this->faker->city();
            $output['state_of_travel'] = $this->faker->state();
            $output['country_of_travel'] = 'United States';
        }

        // 50/50 on if Physician owned, then 50/50 on if third_party_payment_recipient
        $output['physician_ownership_indicator'] = (rand(0,1) === 1) ? 'Yes' : 'No';
        if($output['physician_ownership_indicator'] == 'No') {
            $output['third_party_payment_recipient_indicator'] = (rand(0,1) === 1) ? 'Yes' : 'No';
        } else {
            $output['third_party_payment_recipient_indicator'] = 'No';
        }

        // Fill in name of third party payment recipient if third_party_payment_recipient_indicator is 'Yes'
        if($output['third_party_payment_recipient_indicator'] == 'Yes') $output['name_of_third_party_receiving_payment'] = $this->faker->name();
        
        $output['charity_indicator'] = (rand(0,1) === 1) ? 'Yes' : 'No';

        // If third_party_payment_recipient_indicator is Yes, 50/50 on if third party is a covered recipient
        if($output['third_party_payment_recipient_indicator'] == 'Yes') {
            $output['third_party_equals_covered_recipient_indicator'] = (rand(0,1) === 1) ? 'Yes' : 'No';
        } else {
            $output['third_party_equals_covered_recipient_indicator'] = 'No';
        }

        $output['contextual_information'] = array_rand(array_flip(self::$attrs['contextual_information']));

        $output['delay_in_publication'] = (rand(0,1) === 1) ? 'Yes' : 'No';
        $output['dispute_status_for_publication'] = (rand(0,1) === 1) ? 'Yes' : 'No';

        // Random product_indicator and if it's Covered or Combination, fill in product
        $output['product_indicator'] = array_rand(array_flip(self::$attrs['product_indicator']));
        if(in_array($output['product_indicator'],['Covered','Combination'])) {      
            // 50/50 on if product is a drug or device
            if(rand(0,1) === 1) {
                $output['name_of_drug_or_biological1'] = $this->faker->company();
                if(rand(0,1) === 1) {
                    $output['name_of_drug_or_biological2'] = $this->faker->company();
                    if(rand(0,1) === 1) {
                        $output['name_of_drug_or_biological3'] = $this->faker->company();
                        if(rand(0,1) === 1) {
                            $output['name_of_drug_or_biological4'] = $this->faker->company();
                            if(rand(0,1) === 1) {
                                $output['name_of_drug_or_biological5'] = $this->faker->company();
                            }
                        }
                    }
                }

                $output['ndc_of_drug_or_biological1'] = rand(10000,99999).'-'.rand(100,999).'-'.rand(10,99);
                if(isset($output['name_of_drug_or_biological2'])) $output['ndc_of_drug_or_biological2'] = rand(10000,99999).'-'.rand(100,999).'-'.rand(10,99);
                if(isset($output['name_of_drug_or_biological3'])) $output['ndc_of_drug_or_biological3'] = rand(10000,99999).'-'.rand(100,999).'-'.rand(10,99);
                if(isset($output['name_of_drug_or_biological4'])) $output['ndc_of_drug_or_biological4'] = rand(10000,99999).'-'.rand(100,999).'-'.rand(10,99);
                if(isset($output['name_of_drug_or_biological5'])) $output['ndc_of_drug_or_biological5'] = rand(10000,99999).'-'.rand(100,999).'-'.rand(10,99);
            } else {
                $output['name_of_device_or_supply1'] = $this->faker->company();
                if(rand(0,1) === 1) {
                    $output['name_of_device_or_supply2'] = $this->faker->company();
                    if(rand(0,1) === 1) {
                        $output['name_of_device_or_supply3'] = $this->faker->company();
                        if(rand(0,1) === 1) {
                            $output['name_of_device_or_supply4'] = $this->faker->company();
                            if(rand(0,1) === 1) {
                                $output['name_of_device_or_supply5'] = $this->faker->company();
                            }
                        }
                    }
                }
            }
        }

        // Treating all payments as in the 2015 program year and publicized on 2021-06-30
        $output['program_year'] = '2015';
        $output['payment_publication_date'] = '2021-06-30 00:00:00';

        return $output;
    }
}
