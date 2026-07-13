<?php

use App\Models\Poll;
use App\Models\User;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    $this->teacher = User::factory()->create(['role' => 'teacher']);
    $this->student = User::factory()->create(['role' => 'student']);
});

it('teacher can open a draft poll via PATCH status', function () {
    $poll = Poll::factory()->create(['teacher_id' => $this->teacher->id]);

    actingAs($this->teacher)
        ->patchJson("/api/polls/{$poll->id}/status", [
            'status' => 'active',
        ])
        ->assertOk()
        ->assertJsonPath('poll.status', 'active');

    expect($poll->fresh()->status)->toBe('active');
    expect($poll->fresh()->started_at)->not->toBeNull();
});

it('teacher can close an active poll via PATCH status', function () {
    $poll = Poll::factory()->active()->create(['teacher_id' => $this->teacher->id]);

    actingAs($this->teacher)
        ->patchJson("/api/polls/{$poll->id}/status", [
            'status' => 'closed',
        ])
        ->assertOk()
        ->assertJsonPath('poll.status', 'ended');

    expect($poll->fresh()->status)->toBe('ended');
});

it('student cannot update poll status', function () {
    $poll = Poll::factory()->create(['teacher_id' => $this->teacher->id]);

    actingAs($this->student)
        ->patchJson("/api/polls/{$poll->id}/status", [
            'status' => 'active',
        ])
        ->assertForbidden();
});

it('cannot open an already active poll', function () {
    $poll = Poll::factory()->active()->create(['teacher_id' => $this->teacher->id]);

    actingAs($this->teacher)
        ->patchJson("/api/polls/{$poll->id}/status", [
            'status' => 'active',
        ])
        ->assertUnprocessable();
});

it('cannot close a draft poll without opening it', function () {
    $poll = Poll::factory()->create(['teacher_id' => $this->teacher->id]);

    actingAs($this->teacher)
        ->patchJson("/api/polls/{$poll->id}/status", [
            'status' => 'closed',
        ])
        ->assertUnprocessable();
});

it('validates status is required', function () {
    $poll = Poll::factory()->create(['teacher_id' => $this->teacher->id]);

    actingAs($this->teacher)
        ->patchJson("/api/polls/{$poll->id}/status", [])
        ->assertUnprocessable();
});

it('validates status must be active or closed', function () {
    $poll = Poll::factory()->create(['teacher_id' => $this->teacher->id]);

    actingAs($this->teacher)
        ->patchJson("/api/polls/{$poll->id}/status", [
            'status' => 'invalid',
        ])
        ->assertUnprocessable();
});

it('other teacher cannot update someone elses poll status', function () {
    $otherTeacher = User::factory()->create(['role' => 'teacher']);
    $poll = Poll::factory()->create(['teacher_id' => $this->teacher->id]);

    actingAs($otherTeacher)
        ->patchJson("/api/polls/{$poll->id}/status", [
            'status' => 'active',
        ])
        ->assertForbidden();
});
