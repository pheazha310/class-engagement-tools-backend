<?php

use App\Models\User;

test('confirm password screen requires authentication', function () {
    $response = $this->get('/user/confirm-password');

    $response->assertRedirect();
});

test('password confirmation requires authentication', function () {
    $response = $this->get('/user/confirm-password');

    $response->assertRedirect();
});
