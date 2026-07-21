<?php

use App\Models\GameAnswer;
use App\Models\GameSession;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\postJson;

beforeEach(function () {
    $this->teacher = User::factory()->create(['role' => 'teacher']);
    $this->student = User::factory()->create(['role' => 'student']);
});

it('teacher can end a game session and game history is saved', function () {
    $session = GameSession::factory()->create([
        'teacher_id' => $this->teacher->id,
        'status' => 'active',
        'started_at' => now()->subHour(),
    ]);

    GameAnswer::factory()->create([
        'game_session_id' => $session->id,
        'participant_name' => 'Alice',
        'points_awarded' => 20,
    ]);

    GameAnswer::factory()->create([
        'game_session_id' => $session->id,
        'participant_name' => 'Bob',
        'points_awarded' => 10,
    ]);

    actingAs($this->teacher)
        ->postJson("/api/game-sessions/{$session->id}/end")
        ->assertOk()
        ->assertJsonFragment(['message' => 'Game session ended successfully.']);

    assertDatabaseHas('game_histories', [
        'game_session_id' => $session->id,
        'teacher_id' => $this->teacher->id,
        'game_type' => $session->game_type,
    ]);

    assertDatabaseHas('game_sessions', [
        'id' => $session->id,
        'status' => 'ended',
    ]);
});

it('saves participants and scores in game history', function () {
    $session = GameSession::factory()->create([
        'teacher_id' => $this->teacher->id,
        'status' => 'active',
        'started_at' => now()->subHour(),
    ]);

    GameAnswer::factory()->create([
        'game_session_id' => $session->id,
        'participant_name' => 'Alice',
        'points_awarded' => 20,
    ]);

    GameAnswer::factory()->create([
        'game_session_id' => $session->id,
        'participant_name' => 'Alice',
        'points_awarded' => 10,
    ]);

    GameAnswer::factory()->create([
        'game_session_id' => $session->id,
        'participant_name' => 'Bob',
        'points_awarded' => 5,
    ]);

    $response = actingAs($this->teacher)
        ->postJson("/api/game-sessions/{$session->id}/end");

    $response->assertOk();

    $history = $response->json('game_history');

    expect($history['scores'])->toHaveCount(2);
    expect($history['participants'])->toContain('Alice', 'Bob');
    expect($history['total_questions'])->toBe(3);
});

it('returns not found when ending an already ended game session', function () {
    $session = GameSession::factory()->create([
        'teacher_id' => $this->teacher->id,
        'status' => 'ended',
    ]);

    actingAs($this->teacher)
        ->postJson("/api/game-sessions/{$session->id}/end")
        ->assertNotFound();
});

it('allows a student to end a game session and saves history', function () {
    $session = GameSession::factory()->create([
        'teacher_id' => $this->teacher->id,
        'status' => 'active',
        'started_at' => now()->subHour(),
    ]);

    GameAnswer::factory()->create([
        'game_session_id' => $session->id,
        'participant_name' => 'Charlie',
        'points_awarded' => 15,
    ]);

    actingAs($this->student)
        ->postJson("/api/game-sessions/{$session->id}/end")
        ->assertOk()
        ->assertJsonFragment(['message' => 'Game session ended successfully.']);

    assertDatabaseHas('game_histories', [
        'game_session_id' => $session->id,
    ]);
});

it('allows guest to end a game session and saves history', function () {
    $session = GameSession::factory()->create([
        'teacher_id' => null,
        'status' => 'active',
        'started_at' => now()->subHour(),
    ]);

    GameAnswer::factory()->create([
        'game_session_id' => $session->id,
        'participant_name' => 'GuestPlayer',
        'points_awarded' => 30,
    ]);

    postJson("/api/game-sessions/{$session->id}/end")
        ->assertOk()
        ->assertJsonFragment(['message' => 'Game session ended successfully.']);

    assertDatabaseHas('game_histories', [
        'game_session_id' => $session->id,
        'teacher_id' => null,
    ]);
});

it('returns correct game history structure in API response', function () {
    $session = GameSession::factory()->create([
        'teacher_id' => $this->teacher->id,
        'status' => 'active',
        'started_at' => now()->subHour(),
    ]);

    GameAnswer::factory()->create([
        'game_session_id' => $session->id,
        'participant_name' => 'Alice',
        'points_awarded' => 20,
    ]);

    $response = actingAs($this->teacher)
        ->postJson("/api/game-sessions/{$session->id}/end");

    $response->assertOk()
        ->assertJsonStructure([
            'message',
            'game_history' => [
                'id',
                'game_session_id',
                'teacher_id',
                'game_type',
                'settings',
                'participants',
                'scores',
                'total_questions',
                'started_at',
                'ended_at',
                'created_at',
                'updated_at',
            ],
        ]);
});

it('returns not found when ending a non-existent game session', function () {
    actingAs($this->teacher)
        ->postJson('/api/game-sessions/99999/end')
        ->assertNotFound();
});
