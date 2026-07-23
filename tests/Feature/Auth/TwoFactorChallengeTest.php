<?php

use App\Models\User;
use Laravel\Fortify\Features;

beforeEach(function () {
    $this->skipUnlessFortifyHas(Features::twoFactorAuthentication());
});

test('two factor challenge redirects to login when not authenticated', function () {
    $response = $this->get('/two-factor-challenge');

    $response->assertRedirect('/login');
});

test('two factor challenge can be rendered', function () {
    $user = User::factory()->withTwoFactor()->create();

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->get('/two-factor-challenge')
        ->assertOk();
});
