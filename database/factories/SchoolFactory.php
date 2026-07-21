<?php

namespace Database\Factories;

use App\Models\Country;
use App\Models\Province;
use App\Models\School;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<School>
 */
class SchoolFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $country = Country::firstOrCreate(
            ['code' => 'KH'],
            ['name' => 'Cambodia'],
        );

        $province = Province::inRandomOrder()->first()
            ?? Province::firstOrCreate(
                ['country_id' => $country->id, 'name' => 'Phnom Penh'],
            );

        return [
            'school_name' => fake()->company().' High School',
            'country_id' => $country->id,
            'province_id' => $province->id,
        ];
    }
}
