<?php

use App\Models\Poll;
use App\Models\User;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    $this->teacher = User::factory()->create(['role' => 'teacher']);
    $this->student = User::factory()->create(['role' => 'student']);
});

it('teacher can open a draft poll', function () {
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

it('student cannot update poll status', function () {
    $poll = Poll::factory()->create(['created_by' => $this->teacher->id]);

    actingAs($this->student)
        ->postJson("/api/polls/{$poll->id}/start")
        ->assertForbidden();
});

it('cannot open an already active poll', function () {
    $poll = Poll::factory()->active()->create(['created_by' => $this->teacher->id]);

    actingAs($this->teacher)
        ->postJson("/api/polls/{$poll->id}/start")
        ->assertUnprocessable();
});

it('cannot close a draft poll without opening it', function () {
    $poll = Poll::factory()->create(['created_by' => $this->teacher->id]);

    actingAs($this->teacher)
        ->postJson("/api/polls/{$poll->id}/end")
        ->assertUnprocessable();
});

it('validates ownership on poll status change', function () {
    $otherTeacher = User::factory()->create(['role' => 'teacher']);
    $poll = Poll::factory()->create(['created_by' => $this->teacher->id]);

    actingAs($otherTeacher)
        ->postJson("/api/polls/{$poll->id}/start")
        ->assertForbidden();
});
