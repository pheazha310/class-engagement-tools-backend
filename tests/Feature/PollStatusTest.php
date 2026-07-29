<?php

use App\Models\Poll;
use App\Models\User;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    $this->teacher = User::factory()->create(['role' => 'teacher']);
    $this->student = User::factory()->create(['role' => 'student']);
});

it('teacher can start a draft poll', function () {
    $poll = Poll::factory()->create(['created_by' => $this->teacher->id]);

    actingAs($this->teacher)
        ->postJson("/api/polls/{$poll->id}/start")
        ->assertOk()
        ->assertJsonPath('poll.status', 'active');

    expect($poll->fresh()->status)->toBe('active');
    expect($poll->fresh()->started_at)->not->toBeNull();
});

it('teacher can close an active poll', function () {
    $poll = Poll::factory()->active()->create(['created_by' => $this->teacher->id]);

    actingAs($this->teacher)
        ->postJson("/api/polls/{$poll->id}/end")
        ->assertOk()
        ->assertJsonPath('poll.status', 'closed');

    expect($poll->fresh()->status)->toBe('closed');
});

it('student cannot start a poll', function () {
    $poll = Poll::factory()->create(['created_by' => $this->teacher->id]);

    actingAs($this->student)
        ->postJson("/api/polls/{$poll->id}/start")
        ->assertForbidden();
});

it('cannot start an already active poll', function () {
    $poll = Poll::factory()->active()->create(['created_by' => $this->teacher->id]);

    actingAs($this->teacher)
        ->postJson("/api/polls/{$poll->id}/start")
        ->assertUnprocessable();
});

it('cannot close a draft poll', function () {
    $poll = Poll::factory()->create(['created_by' => $this->teacher->id]);

    actingAs($this->teacher)
        ->postJson("/api/polls/{$poll->id}/end")
        ->assertUnprocessable();
});

it('auto-closes expired polls via scheduler', function () {
    $poll = Poll::factory()->active()->create([
        'created_by' => $this->teacher->id,
        'started_at' => now()->subMinutes(15),
        'duration_minutes' => 10,
    ]);

    $this->artisan('app:close-expired-polls')->assertSuccessful();

    expect($poll->fresh()->status)->toBe('closed');
    expect($poll->fresh()->ended_at)->not->toBeNull();
});

it('does not close polls that have not expired', function () {
    $poll = Poll::factory()->active()->create([
        'created_by' => $this->teacher->id,
        'started_at' => now(),
        'duration_minutes' => 10,
    ]);

    $this->artisan('app:close-expired-polls')->assertSuccessful();

    expect($poll->fresh()->status)->toBe('active');
});

it('dashboard stats returns correct counts', function () {
    Poll::factory()->create(['created_by' => $this->teacher->id]);
    Poll::factory()->active()->create(['created_by' => $this->teacher->id]);
    Poll::factory()->closed()->create(['created_by' => $this->teacher->id]);

    actingAs($this->teacher)
        ->getJson('/api/polls/dashboard/stats')
        ->assertOk()
        ->assertJsonPath('data.total_polls', 3)
        ->assertJsonPath('data.active_polls', 1)
        ->assertJsonPath('data.closed_polls', 1);
});

it('returns dashboard stats for authenticated teacher', function () {
    Poll::factory()->create(['created_by' => $this->teacher->id]);

    actingAs($this->teacher)
        ->getJson('/api/polls/stats')
        ->assertOk()
        ->assertJsonPath('data.total_polls', 1);
});

it('unauthorized user cannot access dashboard stats', function () {
    $this->getJson('/api/polls/stats')->assertUnauthorized();
});
