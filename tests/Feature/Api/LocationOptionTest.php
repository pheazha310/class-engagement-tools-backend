<?php

use App\Models\Country;
use App\Models\Location;
use App\Models\Province;

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
        ->and(Location::where('country', 'Thailand')
            ->where('province', 'Bangkok')
            ->where('school_name', 'Bangkok Learning School')
            ->exists())->toBeTrue();
});

test('adding the same location option reuses existing records', function () {
    $country = Country::create(['name' => 'Cambodia', 'code' => 'KH']);
    Province::create(['country_id' => $country->id, 'name' => 'Phnom Penh']);
    Location::create([
        'country' => 'Cambodia',
        'province' => 'Phnom Penh',
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
        ->and(Location::count())->toBe(1);
});
