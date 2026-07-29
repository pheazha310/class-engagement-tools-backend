<?php

use App\Models\Poll;
use App\Models\PollOption;
use App\Models\User;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    $this->teacher = User::factory()->create(['role' => 'teacher']);
    $this->student = User::factory()->create(['role' => 'student']);
});

// ─── Room Code Auto-Generation ───

it('generates a room code when a poll is created', function () {
    $poll = Poll::factory()->create(['teacher_id' => $this->teacher->id]);

    expect($poll->room_code)->not->toBeNull();
});

it('generates a 6-character uppercase room code', function () {
    $poll = Poll::factory()->create(['teacher_id' => $this->teacher->id]);

    expect(strlen($poll->room_code))->toBe(6);
    expect($poll->room_code)->toMatch('/^[A-Z0-9]{6}$/');
});

it('generates unique room codes for different polls', function () {
    $poll1 = Poll::factory()->create(['teacher_id' => $this->teacher->id]);
    $poll2 = Poll::factory()->create(['teacher_id' => $this->teacher->id]);

    expect($poll1->room_code)->not->toBe($poll2->room_code);
});

it('preserves a manually set room code', function () {
    $poll = Poll::factory()->create([
        'teacher_id' => $this->teacher->id,
        'room_code' => 'A1B2C3',
    ]);

    expect($poll->room_code)->toBe('A1B2C3');
});

it('generates room code via API creation as well', function () {
    actingAs($this->teacher)
        ->postJson('/api/polls', [
            'question' => 'Test room code generation?',
            'poll_type' => 'yes_no',
        ])
        ->assertCreated()
        ->assertJsonStructure(['poll' => ['room_code', 'join_url']]);

    $poll = Poll::first();

    expect($poll->room_code)->not->toBeNull();
    expect(strlen($poll->room_code))->toBe(6);
    expect($poll->room_code)->toMatch('/^[A-Z0-9]{6}$/');
});

it('includes room_code and join_url in the poll resource response', function () {
    $poll = Poll::factory()->active()->create(['teacher_id' => $this->teacher->id]);

    actingAs($this->teacher)
        ->getJson("/api/polls/{$poll->id}")
        ->assertOk()
        ->assertJsonStructure([
            'poll' => ['room_code', 'join_url'],
        ]);
});

// ─── Join API Endpoint (showByRoomCode) ───

it('looks up an active poll by room code', function () {
    $poll = Poll::factory()->active()->create(['teacher_id' => $this->teacher->id]);
    PollOption::factory(2)->create(['poll_id' => $poll->id]);

    $this->getJson("/api/polls/join/{$poll->room_code}")
        ->assertOk()
        ->assertJsonPath('poll.id', $poll->id)
        ->assertJsonPath('poll.room_code', $poll->room_code)
        ->assertJsonStructure(['poll' => ['id', 'room_code', 'join_url', 'question', 'options']]);
});

it('is case-insensitive for room code lookup', function () {
    $poll = Poll::factory()->active()->create(['teacher_id' => $this->teacher->id]);
    $lowerCode = strtolower($poll->room_code);

    $this->getJson("/api/polls/join/{$lowerCode}")
        ->assertOk()
        ->assertJsonPath('poll.id', $poll->id);
});

it('returns 404 for a non-existent room code', function () {
    $this->getJson('/api/polls/join/NONEXIST')
        ->assertNotFound();
});

it('returns 404 for a draft poll looked up by room code', function () {
    $poll = Poll::factory()->create(['teacher_id' => $this->teacher->id, 'status' => 'draft']);

    $this->getJson("/api/polls/join/{$poll->room_code}")
        ->assertNotFound();
});

it('returns 404 for a closed poll looked up by room code', function () {
    $poll = Poll::factory()->closed()->create(['teacher_id' => $this->teacher->id]);

    $this->getJson("/api/polls/join/{$poll->room_code}")
        ->assertNotFound();
});

it('does not require authentication for room code lookup', function () {
    $poll = Poll::factory()->active()->create(['teacher_id' => $this->teacher->id]);

    $this->getJson("/api/polls/join/{$poll->room_code}")
        ->assertOk();
});
