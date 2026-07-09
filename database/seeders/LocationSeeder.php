<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\District;
use App\Models\Province;
use App\Models\School;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
    public function run(): void
    {
        $country = Country::firstOrCreate(
            ['code' => 'KH'],
            ['name' => 'Cambodia'],
        );

        $provinces = [
            'Banteay Meanchey',
            'Battambang',
            'Kampong Cham',
            'Kampong Chhnang',
            'Kampong Speu',
            'Kampong Thom',
            'Kampot',
            'Kandal',
            'Koh Kong',
            'Kratie',
            'Mondulkiri',
            'Phnom Penh',
            'Preah Vihear',
            'Prey Veng',
            'Pursat',
            'Ratanakiri',
            'Siem Reap',
            'Preah Sihanouk',
            'Stung Treng',
            'Svay Rieng',
            'Takeo',
            'Oddar Meanchey',
            'Kep',
            'Pailin',
            'Tboung Khmum',
        ];

        foreach ($provinces as $index => $provinceName) {
            $province = Province::firstOrCreate([
                'country_id' => $country->id,
                'name' => $provinceName,
            ]);

            $districtCount = rand(3, 6);

            for ($d = 1; $d <= $districtCount; $d++) {
                $district = District::firstOrCreate([
                    'province_id' => $province->id,
                    'name' => "{$provinceName} District {$d}",
                ]);

                School::firstOrCreate(
                    ['district_id' => $district->id, 'name' => "{$provinceName} High School {$d}"],
                    [
                        'address' => "Main Street, {$provinceName} District {$d}",
                        'latitude' => 11.565 + ($index * 0.05) + ($d * 0.01),
                        'longitude' => 104.912 + ($index * 0.03) + ($d * 0.01),
                    ],
                );

                School::firstOrCreate(
                    ['district_id' => $district->id, 'name' => "{$provinceName} Secondary School {$d}"],
                    [
                        'address' => "Second Avenue, {$provinceName} District {$d}",
                        'latitude' => 11.575 + ($index * 0.05) + ($d * 0.01),
                        'longitude' => 104.922 + ($index * 0.03) + ($d * 0.01),
                    ],
                );
            }
        }
    }
}
