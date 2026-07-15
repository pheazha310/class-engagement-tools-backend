<?php

use App\Models\GameSession;
use App\Models\User;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\postJson;

it('awards points for a correct answer', function () {
    $session = GameSession::factory()->create(['status' => 'active']);

    $response = postJson("/api/game-sessions/{$session->id}/validate-answer", [
        'question_id' => '1',
        'submitted_answer' => '42',
        'correct_answer' => '42',
        'participant_name' => 'Alice',
    ]);

    $response->assertOk()
        ->assertJson([
            'is_correct' => true,
            'points_awarded' => 10,
        ])
        ->assertJsonStructure([
            'game_answer' => [
                'id',
                'game_session_id',
                'question_id',
                'submitted_answer',
                'is_correct',
                'points_awarded',
                'participant_name',
                'created_at',
                'updated_at',
            ],
        ]);

    assertDatabaseHas('game_answers', [
        'game_session_id' => $session->id,
        'question_id' => '1',
        'submitted_answer' => '42',
        'is_correct' => true,
        'points_awarded' => 10,
        'participant_name' => 'Alice',
    ]);
});

it('awards zero points for an incorrect answer', function () {
    $session = GameSession::factory()->create(['status' => 'active']);

    $response = postJson("/api/game-sessions/{$session->id}/validate-answer", [
        'question_id' => '1',
        'submitted_answer' => 'wrong',
        'correct_answer' => 'correct',
    ]);

    $response->assertOk()
        ->assertJson([
            'is_correct' => false,
            'points_awarded' => 0,
        ]);

    assertDatabaseHas('game_answers', [
        'game_session_id' => $session->id,
        'is_correct' => false,
        'points_awarded' => 0,
    ]);
});

it('is case-insensitive when validating answers', function () {
    $session = GameSession::factory()->create(['status' => 'active']);

    $response = postJson("/api/game-sessions/{$session->id}/validate-answer", [
        'question_id' => '1',
        'submitted_answer' => '  Mars  ',
        'correct_answer' => 'mars',
    ]);

    $response->assertOk()
        ->assertJson([
            'is_correct' => true,
            'points_awarded' => 10,
        ]);
});

it('allows an authenticated user to submit an answer', function () {
    $user = User::factory()->create();
    $session = GameSession::factory()->create(['status' => 'active']);

    $response = $this->actingAs($user)
        ->postJson("/api/game-sessions/{$session->id}/validate-answer", [
            'question_id' => '1',
            'submitted_answer' => 'Paris',
            'correct_answer' => 'Paris',
        ]);

    $response->assertOk()
        ->assertJson([
            'is_correct' => true,
            'points_awarded' => 10,
        ]);

    assertDatabaseHas('game_answers', [
        'game_session_id' => $session->id,
        'user_id' => $user->id,
        'is_correct' => true,
    ]);
});

it('returns not found when validating against an ended game session', function () {
    $session = GameSession::factory()->create(['status' => 'ended']);

    postJson("/api/game-sessions/{$session->id}/validate-answer", [
        'question_id' => '1',
        'submitted_answer' => '42',
        'correct_answer' => '42',
    ])->assertNotFound();
});

it('returns not found when validating against a non-existent game session', function () {
    postJson('/api/game-sessions/99999/validate-answer', [
        'question_id' => '1',
        'submitted_answer' => '42',
        'correct_answer' => '42',
    ])->assertNotFound();
});

it('requires submitted_answer when validating an answer', function () {
    $session = GameSession::factory()->create(['status' => 'active']);

    postJson("/api/game-sessions/{$session->id}/validate-answer", [
        'question_id' => '1',
        'correct_answer' => '42',
    ])->assertUnprocessable();
});

it('stores the answer for an unauthenticated guest with a participant name', function () {
    $session = GameSession::factory()->create(['status' => 'active']);

    postJson("/api/game-sessions/{$session->id}/validate-answer", [
        'question_id' => '1',
        'submitted_answer' => 'Blue',
        'correct_answer' => 'Blue',
        'participant_name' => 'Guest Player',
    ]);

    assertDatabaseHas('game_answers', [
        'game_session_id' => $session->id,
        'participant_name' => 'Guest Player',
        'is_correct' => true,
    ]);
});
