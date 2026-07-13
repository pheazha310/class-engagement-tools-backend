<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\Province;
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

        foreach ($provinces as $provinceName) {
            Province::firstOrCreate([
                'country_id' => $country->id,
                'name' => $provinceName,
            ]);
        }
    }
}
