<?php

use App\Models\Poll;
use App\Models\PollOption;
use App\Models\User;
use App\Models\Vote;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;

beforeEach(function () {
    $this->teacher = User::factory()->create(['role' => 'teacher']);
    $this->student = User::factory()->create(['role' => 'student']);
});

it('teacher can create a poll', function () {
    actingAs($this->teacher)
        ->postJson('/api/polls', [
            'question' => 'Best PHP framework?',
            'options' => ['Laravel', 'Symfony', 'CakePHP'],
        ])
        ->assertCreated()
        ->assertJsonFragment(['question' => 'Best PHP framework?']);

    assertDatabaseCount('polls', 1);
    assertDatabaseCount('poll_options', 3);
});

it('validates minimum 2 options', function () {
    actingAs($this->teacher)
        ->postJson('/api/polls', [
            'question' => 'Test?',
            'options' => ['Only one'],
        ])
        ->assertUnprocessable();
});

it('validates maximum 10 options', function () {
    actingAs($this->teacher)
        ->postJson('/api/polls', [
            'question' => 'Test?',
            'options' => range(1, 11),
        ])
        ->assertUnprocessable();
});

it('student cannot create a poll', function () {
    actingAs($this->student)
        ->postJson('/api/polls', [
            'question' => 'Test?',
            'options' => ['A', 'B'],
        ])
        ->assertForbidden();
});

it('teacher can view their polls', function () {
    Poll::factory(3)->create(['teacher_id' => $this->teacher->id]);

    actingAs($this->teacher)
        ->getJson('/api/polls')
        ->assertOk()
        ->assertJsonCount(3, 'data');
});

it('teacher can start a poll', function () {
    $poll = Poll::factory()->create(['teacher_id' => $this->teacher->id]);

    actingAs($this->teacher)
        ->postJson("/api/polls/{$poll->id}/start")
        ->assertOk();

    expect($poll->fresh()->status)->toBe('active');
    expect($poll->fresh()->started_at)->not->toBeNull();
});

it('teacher can end an active poll', function () {
    $poll = Poll::factory()->active()->create(['teacher_id' => $this->teacher->id]);

    actingAs($this->teacher)
        ->postJson("/api/polls/{$poll->id}/end")
        ->assertOk();

    expect($poll->fresh()->status)->toBe('ended');
});

it('student can vote on active poll', function () {
    $poll = Poll::factory()->active()->create(['teacher_id' => $this->teacher->id]);
    $option = PollOption::factory()->create(['poll_id' => $poll->id]);

    actingAs($this->student)
        ->postJson("/api/polls/{$poll->id}/vote", [
            'option_id' => $option->id,
        ])
        ->assertOk();

    assertDatabaseHas('votes', [
        'poll_id' => $poll->id,
        'option_id' => $option->id,
        'student_id' => $this->student->id,
    ]);
});

it('student cannot vote twice', function () {
    $poll = Poll::factory()->active()->create(['teacher_id' => $this->teacher->id]);
    $option = PollOption::factory()->create(['poll_id' => $poll->id]);
    Vote::factory()->create([
        'poll_id' => $poll->id,
        'option_id' => $option->id,
        'student_id' => $this->student->id,
    ]);

    actingAs($this->student)
        ->postJson("/api/polls/{$poll->id}/vote", [
            'option_id' => $option->id,
        ])
        ->assertUnprocessable();
});

it('student cannot vote on draft poll', function () {
    $poll = Poll::factory()->create(['teacher_id' => $this->teacher->id]);
    $option = PollOption::factory()->create(['poll_id' => $poll->id]);

    actingAs($this->student)
        ->postJson("/api/polls/{$poll->id}/vote", [
            'option_id' => $option->id,
        ])
        ->assertUnprocessable();
});

it('shows live results', function () {
    $poll = Poll::factory()->active()->create(['teacher_id' => $this->teacher->id]);
    $options = PollOption::factory(2)->create(['poll_id' => $poll->id]);

    Vote::factory()->create(['poll_id' => $poll->id, 'option_id' => $options[0]->id, 'student_id' => User::factory()]);
    Vote::factory()->create(['poll_id' => $poll->id, 'option_id' => $options[0]->id, 'student_id' => User::factory()]);
    Vote::factory()->create(['poll_id' => $poll->id, 'option_id' => $options[1]->id, 'student_id' => User::factory()]);

    actingAs($this->teacher)
        ->getJson("/api/polls/{$poll->id}/results")
        ->assertOk()
        ->assertJsonFragment(['totalVotes' => 3])
        ->assertJsonCount(2, 'data.results');
});

it('teacher can delete a draft poll', function () {
    $poll = Poll::factory()->create(['teacher_id' => $this->teacher->id]);

    actingAs($this->teacher)
        ->deleteJson("/api/polls/{$poll->id}")
        ->assertOk();

    assertDatabaseCount('polls', 0);
});

it('only one poll can be active at a time', function () {
    $activePoll = Poll::factory()->active()->create(['teacher_id' => $this->teacher->id]);
    $draftPoll = Poll::factory()->create(['teacher_id' => $this->teacher->id]);

    actingAs($this->teacher)
        ->postJson("/api/polls/{$draftPoll->id}/start")
        ->assertUnprocessable();
});
