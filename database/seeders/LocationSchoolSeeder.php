<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\Province;
use App\Models\School;
use Illuminate\Database\Seeder;

class LocationSchoolSeeder extends Seeder
{
    public function run(): void
    {
        $country = Country::firstOrCreate(
            ['code' => 'KH'],
            ['name' => 'Cambodia'],
        );

        $sampleSchools = [
            'Banteay Meanchey' => ['Phnom Svay High School', 'Serei Saophoan Secondary School'],
            'Battambang' => ['Battambang High School', 'Wat Kandal Secondary School'],
            'Kampong Cham' => ['Kampong Cham High School', 'Koh Sotin Secondary School'],
            'Kampong Chhnang' => ['Kampong Chhnang High School', 'Kampong Leaeng Secondary School'],
            'Kampong Speu' => ['Kampong Speu High School', 'Chbar Mon Secondary School'],
            'Kampong Thom' => ['Kampong Thom High School', 'Steung Sen Secondary School'],
            'Kampot' => ['Kampot High School', 'Tuek Chhou Secondary School'],
            'Kandal' => ['Kandal High School', 'Ta Khmau Secondary School'],
            'Koh Kong' => ['Koh Kong High School', 'Botum Sakor Secondary School'],
            'Kratie' => ['Kratie High School', 'Chhloung Secondary School'],
            'Mondulkiri' => ['Mondulkiri High School', 'Kaev Seima Secondary School'],
            'Phnom Penh' => ['Phnom Penh International School', 'Wat Phnom High School'],
            'Preah Vihear' => ['Preah Vihear High School', 'Choam Ksan Secondary School'],
            'Prey Veng' => ['Prey Veng High School', 'Kampong Trabaek Secondary School'],
            'Pursat' => ['Pursat High School', 'Bakan Secondary School'],
            'Ratanakiri' => ['Ratanakiri High School', 'Lumphat Secondary School'],
            'Siem Reap' => ['Siem Reap High School', 'Angkor Secondary School'],
            'Preah Sihanouk' => ['Sihanoukville High School', 'Stueng Hav Secondary School'],
            'Stung Treng' => ['Stung Treng High School', 'Sesan Secondary School'],
            'Svay Rieng' => ['Svay Rieng High School', 'Chantrea Secondary School'],
            'Takeo' => ['Takeo High School', 'Doun Kaev Secondary School'],
            'Oddar Meanchey' => ['Oddar Meanchey High School', 'Anlong Veng Secondary School'],
            'Kep' => ['Kep High School', 'Damnak Chang\'aeur Secondary School'],
            'Pailin' => ['Pailin High School', 'Sala Krau Secondary School'],
            'Tboung Khmum' => ['Tboung Khmum High School', 'Ponhea Kraek Secondary School'],
        ];

        foreach ($sampleSchools as $provinceName => $schoolNames) {
            $province = Province::firstOrCreate(
                ['name' => $provinceName, 'country_id' => $country->id],
                ['country_id' => $country->id],
            );

            foreach ($schoolNames as $schoolName) {
                School::create([
                    'school_name' => $schoolName,
                    'country_id' => $country->id,
                    'province_id' => $province->id,
                ]);
            }
        }
    }
}