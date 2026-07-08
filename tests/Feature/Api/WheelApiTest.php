<?php

use App\Http\Middleware\VerifyCsrfToken;
use App\Models\User;
use App\Models\Wheel;

beforeEach(function () {
    $this->withoutMiddleware(VerifyCsrfToken::class);
});

test('teacher can create a new wheel', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->postJson('/api/wheels', [
            'name' => 'Morning Circle',
            'description' => 'Ice breaker wheel',
            'color' => '#FF5733',
        ]);

    $response->assertCreated()
        ->assertJson([
            'name' => 'Morning Circle',
            'description' => 'Ice breaker wheel',
            'color' => '#FF5733',
        ])
        ->assertJsonStructure([
            'id',
            'name',
            'description',
            'color',
            'participants' => [],
        ]);

    expect(Wheel::count())->toBe(1);
});

test('wheel creation validates required fields', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->postJson('/api/wheels', []);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['name']);

    $response = $this
        ->actingAs($user)
        ->postJson('/api/wheels', ['name' => str_repeat('a', 256)]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['name']);
});

test('teacher can update wheel information', function () {
    $user = User::factory()->create();
    $wheel = Wheel::factory()->for($user)->create(['name' => 'Old Name']);

    $response = $this
        ->actingAs($user)
        ->putJson("/api/wheels/{$wheel->id}", [
            'name' => 'New Name',
            'description' => 'Updated description',
        ]);

    $response->assertOk()
        ->assertJson([
            'name' => 'New Name',
            'description' => 'Updated description',
        ]);

    $wheel->refresh();
    expect($wheel->name)->toBe('New Name');
});

test('system returns wheel details with participant list', function () {
    $user = User::factory()->create();
    $wheel = Wheel::factory()->for($user)->create();
    $wheel->participants()->createMany([
        ['name' => 'Alice'],
        ['name' => 'Bob'],
    ]);

    $response = $this
        ->actingAs($user)
        ->getJson("/api/wheels/{$wheel->id}");

    $response->assertOk()
        ->assertJson([
            'id' => $wheel->id,
            'name' => $wheel->name,
            'participants' => [
                ['name' => 'Alice'],
                ['name' => 'Bob'],
            ],
        ]);
});

test('unauthorized user cannot access another users wheel', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $wheel = Wheel::factory()->for($owner)->create();

    $this->actingAs($other)
        ->getJson("/api/wheels/{$wheel->id}")
        ->assertStatus(403);

    $this->actingAs($other)
        ->putJson("/api/wheels/{$wheel->id}", ['name' => 'Hacked'])
        ->assertStatus(403);

    $this->actingAs($other)
        ->deleteJson("/api/wheels/{$wheel->id}")
        ->assertStatus(403);
});

test('unauthenticated user cannot access wheels', function () {
    $this->getJson('/api/wheels')->assertStatus(401);
    $this->postJson('/api/wheels', [])->assertStatus(401);
});

test('teacher can list their own wheels', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();

    $user->wheels()->createMany([
        ['name' => 'Wheel 1', 'description' => 'First'],
        ['name' => 'Wheel 2', 'description' => 'Second'],
    ]);
    $other->wheels()->create(['name' => 'Other Wheel']);

    $response = $this
        ->actingAs($user)
        ->getJson('/api/wheels');

    $response->assertOk()
        ->assertJsonCount(2)
        ->assertJson([
            ['name' => 'Wheel 1'],
            ['name' => 'Wheel 2'],
        ]);
});

test('teacher can add participant to wheel', function () {
    $user = User::factory()->create();
    $wheel = Wheel::factory()->for($user)->create();

    $response = $this
        ->actingAs($user)
        ->postJson("/api/wheels/{$wheel->id}/participants", [
            'name' => 'Charlie',
        ]);

    $response->assertCreated()
        ->assertJson([
            'name' => 'Charlie',
        ]);

    expect($wheel->participants->count())->toBe(1);
});

test('participant creation validates required fields', function () {
    $user = User::factory()->create();
    $wheel = Wheel::factory()->for($user)->create();

    $response = $this
        ->actingAs($user)
        ->postJson("/api/wheels/{$wheel->id}/participants", []);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['name']);
});

test('unauthorized user cannot add participant to another users wheel', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $wheel = Wheel::factory()->for($owner)->create();

    $this->actingAs($other)
        ->postJson("/api/wheels/{$wheel->id}/participants", ['name' => 'Eve'])
        ->assertStatus(403);
});

test('teacher can remove participant from wheel', function () {
    $user = User::factory()->create();
    $wheel = Wheel::factory()->for($user)->create();
    $participant = $wheel->participants()->create(['name' => 'Dave']);

    $response = $this
        ->actingAs($user)
        ->deleteJson("/api/wheels/{$wheel->id}/participants/{$participant->id}");

    $response->assertNoContent();
    expect($wheel->participants()->count())->toBe(0);
});

test('teacher can delete their wheel', function () {
    $user = User::factory()->create();
    $wheel = Wheel::factory()->for($user)->create();

    $response = $this
        ->actingAs($user)
        ->deleteJson("/api/wheels/{$wheel->id}");

    $response->assertNoContent();
    expect(Wheel::find($wheel->id))->toBeNull();
});
