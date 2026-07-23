<?php

use App\Models\Poll;
use App\Models\PollOption;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseCount;

beforeEach(function () {
    $this->teacher = User::factory()->create(['role' => 'teacher']);
});

it('teacher can create a poll', function () {
    actingAs($this->teacher)
        ->postJson('/api/polls', [
            'title' => 'Best PHP framework?',
            'question' => 'Best PHP framework?',
            'options' => ['Laravel', 'Symfony', 'CakePHP'],
        ])
        ->assertCreated()
        ->assertJsonPath('poll.question', 'Best PHP framework?');

    assertDatabaseCount('polls', 1);
    assertDatabaseCount('poll_options', 3);
});

it('validates minimum 2 options', function () {
    actingAs($this->teacher)
        ->postJson('/api/polls', [
            'title' => 'Test?',
            'question' => 'Test?',
            'options' => ['Only one'],
        ])
        ->assertUnprocessable();
});

it('validates maximum 10 options', function () {
    actingAs($this->teacher)
        ->postJson('/api/polls', [
            'title' => 'Test?',
            'question' => 'Test?',
            'options' => range(1, 11),
        ])
        ->assertUnprocessable();
});

it('student cannot create a poll', function () {
    $student = User::factory()->create(['role' => 'student']);

    actingAs($student)
        ->postJson('/api/polls', [
            'title' => 'Test?',
            'question' => 'Test?',
            'options' => ['A', 'B'],
        ])
        ->assertForbidden();
});

it('teacher can view their polls', function () {
    Poll::factory(3)->create(['created_by' => $this->teacher->id]);

    actingAs($this->teacher)
        ->getJson('/api/polls')
        ->assertOk()
        ->assertJsonCount(3, 'data');
});

it('teacher can update a draft poll', function () {
    $poll = Poll::factory()->create(['created_by' => $this->teacher->id]);

    actingAs($this->teacher)
        ->putJson("/api/polls/{$poll->id}", [
            'title' => 'Updated title',
            'question' => 'Updated question?',
            'options' => ['Yes', 'No'],
        ])
        ->assertOk()
        ->assertJsonPath('poll.title', 'Updated title');

    expect($poll->fresh()->title)->toBe('Updated title');
});

it('teacher can delete a draft poll', function () {
    $poll = Poll::factory()->create(['created_by' => $this->teacher->id]);

    actingAs($this->teacher)
        ->deleteJson("/api/polls/{$poll->id}")
        ->assertOk();

    assertDatabaseCount('polls', 0);
});

it('teacher can start a draft poll', function () {
    $poll = Poll::factory()->create(['created_by' => $this->teacher->id]);

    actingAs($this->teacher)
        ->postJson("/api/polls/{$poll->id}/start")
        ->assertOk();

    expect($poll->fresh()->status)->toBe('active');
});

it('teacher can end an active poll', function () {
    $poll = Poll::factory()->active()->create(['created_by' => $this->teacher->id]);

    actingAs($this->teacher)
        ->postJson("/api/polls/{$poll->id}/end")
        ->assertOk();

    expect($poll->fresh()->status)->toBe('closed');
});

it('cannot start an already active poll', function () {
    $poll = Poll::factory()->active()->create(['created_by' => $this->teacher->id]);

    actingAs($this->teacher)
        ->postJson("/api/polls/{$poll->id}/start")
        ->assertUnprocessable();
});

it('cannot end a draft poll', function () {
    $poll = Poll::factory()->create(['created_by' => $this->teacher->id]);

    actingAs($this->teacher)
        ->postJson("/api/polls/{$poll->id}/end")
        ->assertUnprocessable();
});

it('other teacher cannot manage someone elses poll', function () {
    $otherTeacher = User::factory()->create(['role' => 'teacher']);
    $poll = Poll::factory()->create(['created_by' => $this->teacher->id]);

    actingAs($otherTeacher)
        ->postJson("/api/polls/{$poll->id}/start")
        ->assertForbidden();
});
