<?php

use App\Models\GameSession;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\postJson;
use function PHPUnit\Framework\assertNotEmpty;

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

it('student can create a game session', function () {
    actingAs($this->student)
        ->postJson('/api/game-sessions', [
            'game_type' => 'poll',
            'settings' => ['time_limit' => 120],
        ])
        ->assertCreated()
        ->assertJsonFragment(['game_type' => 'poll']);

    assertDatabaseHas('game_sessions', [
        'game_type' => 'poll',
        'teacher_id' => $this->student->id,
        'status' => 'active',
    ]);
});

it('allows unauthenticated guest to create a game session', function () {
    postJson('/api/game-sessions', [
        'game_type' => 'poll',
        'settings' => ['time_limit' => 120],
    ])
        ->assertCreated()
        ->assertJsonFragment(['game_type' => 'poll']);

    assertDatabaseHas('game_sessions', [
        'teacher_id' => null,
        'status' => 'active',
    ]);
});

it('generates a unique join code on creation', function () {
    actingAs($this->teacher)
        ->postJson('/api/game-sessions', [
            'game_type' => 'poll',
        ]);

    assertDatabaseHas('game_sessions', [
        'teacher_id' => $this->teacher->id,
        'game_type' => 'poll',
    ]);

    $session = GameSession::where('teacher_id', $this->teacher->id)->first();
    assertNotEmpty($session->join_code);
});

it('returns the join code in the API response', function () {
    $response = actingAs($this->teacher)
        ->postJson('/api/game-sessions', [
            'game_type' => 'poll',
        ]);

    $response->assertCreated();
    expect($response->json('game_session.join_code'))->not->toBeNull();
});

it('generates unique join codes across multiple sessions', function () {
    $codes = [];

    foreach (range(1, 5) as $i) {
        $response = actingAs($this->teacher)
            ->postJson('/api/game-sessions', [
                'game_type' => 'poll',
            ]);

        $code = $response->json('game_session.join_code');
        assertNotEmpty($code);
        expect($codes)->not->toContain($code);
        $codes[] = $code;
    }

    expect(array_unique($codes))->toHaveCount(5);
});

it('accepts a custom join code when provided', function () {
    $customCode = 'CUSTOM1';

    actingAs($this->teacher)
        ->postJson('/api/game-sessions', [
            'game_type' => 'poll',
            'join_code' => $customCode,
        ])
        ->assertCreated()
        ->assertJsonFragment(['join_code' => $customCode]);

    assertDatabaseHas('game_sessions', [
        'join_code' => $customCode,
    ]);
});

it('allows a student to join an active game with a valid code', function () {
    $session = GameSession::factory()->create([
        'game_type' => 'poll',
        'status' => 'active',
    ]);

    actingAs($this->student)
        ->getJson("/api/game-sessions/join/{$session->join_code}")
        ->assertOk()
        ->assertJsonFragment([
            'join_code' => $session->join_code,
            'game_type' => 'poll',
            'status' => 'active',
        ]);
});

it('allows an unauthenticated user to join an active game with a valid code', function () {
    $session = GameSession::factory()->create([
        'game_type' => 'poll',
        'status' => 'active',
    ]);

    $this->getJson("/api/game-sessions/join/{$session->join_code}")
        ->assertOk()
        ->assertJsonFragment([
            'join_code' => $session->join_code,
            'game_type' => 'poll',
            'status' => 'active',
        ]);
});

it('returns not found when joining with an invalid code', function () {
    $this->getJson('/api/game-sessions/join/INVALID')
        ->assertNotFound();
});

it('returns not found when joining an ended game session', function () {
    $session = GameSession::factory()->create([
        'game_type' => 'poll',
        'status' => 'ended',
    ]);

    $this->getJson("/api/game-sessions/join/{$session->join_code}")
        ->assertNotFound();
});
