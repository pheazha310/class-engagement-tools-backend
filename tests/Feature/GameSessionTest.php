<?php

use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;

beforeEach(function () {
    $this->teacher = User::factory()->create(['role' => 'teacher']);
    $this->student = User::factory()->create(['role' => 'student']);
});

it('teacher can create a game session', function () {
    actingAs($this->teacher)
        ->postJson('/api/game-sessions', [
            'game_type' => 'poll',
            'settings' => ['time_limit' => 120],
        ])
        ->assertCreated()
        ->assertJsonFragment(['game_type' => 'poll']);

    assertDatabaseHas('game_sessions', [
        'game_type' => 'poll',
        'teacher_id' => $this->teacher->id,
        'status' => 'active',
    ]);
});

it('returns unique game id on creation', function () {
    $response = actingAs($this->teacher)
        ->postJson('/api/game-sessions', [
            'game_type' => 'wheel',
        ]);

    $response->assertCreated();
    expect($response->json('game_id'))->not->toBeNull();

    assertDatabaseCount('game_sessions', 1);
});

it('requires game_type', function () {
    actingAs($this->teacher)
        ->postJson('/api/game-sessions', [])
        ->assertUnprocessable();
});

it('student cannot create a game session', function () {
    actingAs($this->student)
        ->postJson('/api/game-sessions', [
            'game_type' => 'poll',
        ])
        ->assertForbidden();
});
