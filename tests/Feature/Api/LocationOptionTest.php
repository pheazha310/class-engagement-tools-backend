<?php

use App\Models\Country;
use App\Models\Province;
use App\Models\School;

test('guest can add a missing country province and school option', function () {
    $this->postJson('/api/location-options', [
        'country_name' => 'Thailand',
        'country_code' => 'TH',
        'province_name' => 'Bangkok',
        'school_name' => 'Bangkok Learning School',
    ])
        ->assertCreated()
        ->assertJsonPath('country.name', 'Thailand')
        ->assertJsonPath('country.code', 'TH')
        ->assertJsonPath('province.name', 'Bangkok')
        ->assertJsonPath('school.name', 'Bangkok Learning School');

    $country = Country::where('code', 'TH')->firstOrFail();

    expect(Province::where('country_id', $country->id)->where('name', 'Bangkok')->exists())->toBeTrue()
        ->and(School::where('province_id', $country->provinces()->first()->id)
            ->where('school_name', 'Bangkok Learning School')
            ->exists())->toBeTrue();
});

test('adding the same location option reuses existing records', function () {
    $country = Country::firstOrCreate(['code' => 'KH'], ['name' => 'Cambodia']);
    $province = Province::firstOrCreate(['country_id' => $country->id, 'name' => 'Phnom Penh']);
    School::firstOrCreate([
        'country_id' => $country->id,
        'province_id' => $province->id,
        'school_name' => 'Wat Phnom High School',
    ]);

    $this->postJson('/api/location-options', [
        'country_name' => 'cambodia',
        'country_code' => 'KH',
        'province_name' => 'phnom penh',
        'school_name' => 'wat phnom high school',
    ])->assertCreated();

    expect(Country::count())->toBe(1)
        ->and(Province::count())->toBe(1)
        ->and(School::count())->toBe(1);
});
