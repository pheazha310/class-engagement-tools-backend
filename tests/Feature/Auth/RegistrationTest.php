<?php

use App\Actions\Fortify\CreateNewUser;
use Database\Seeders\RolePermissionSeeder;
use Laravel\Fortify\Features;

beforeEach(function () {
    $this->skipUnlessFortifyHas(Features::registration());
    $this->seed(RolePermissionSeeder::class);
});

test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response->assertOk();
});

test('new users can register', function () {
    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect();
});

test('new users can save school, country, and province during registration', function () {
    $action = app(CreateNewUser::class);

    $user = $action->create([
        'name' => 'Profile User',
        'email' => 'profile@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'role' => 'student',
        'country_name' => 'South Africa',
        'province_name' => 'Western Cape',
        'school_name' => 'Brighton High School',
    ]);

    $user->refresh();

    $this->assertNotNull($user->profile);
    $this->assertNotNull($user->profile->country);
    $this->assertSame('South Africa', $user->profile->country->name);
    $this->assertNotNull($user->profile->province);
    $this->assertSame('Western Cape', $user->profile->province->name);
    $this->assertNotNull($user->profile->school);
    $this->assertSame('Brighton High School', $user->profile->school->school_name);
});
