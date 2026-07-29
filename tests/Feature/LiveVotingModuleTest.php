<?php

use App\Models\Poll;
use App\Models\PollOption;
use App\Models\User;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    $this->teacher = User::factory()->create(['role' => 'teacher']);
    $this->student = User::factory()->create(['role' => 'student']);
});

it('creates a yes/no poll with default options', function () {
    actingAs($this->teacher)
        ->postJson('/api/polls', [
            'title' => 'Is Laravel great?',
            'question' => 'Is Laravel great?',
            'poll_type' => 'yes_no',
            'options' => ['Yes', 'No'],
        ])
        ->assertCreated()
        ->assertJsonPath('poll.poll_type', 'yes_no');

    $poll = Poll::first();

    expect($poll->options)->toHaveCount(2);
    expect($poll->options->pluck('option_text')->all())->toBe(['Yes', 'No']);
});

it('creates a rating poll with 1-5 options', function () {
    actingAs($this->teacher)
        ->postJson('/api/polls', [
            'title' => 'Rate the lesson',
            'question' => 'Rate the lesson',
            'poll_type' => 'rating',
            'options' => ['1', '2', '3', '4', '5'],
        ])
        ->assertCreated()
        ->assertJsonPath('poll.poll_type', 'rating');

    $poll = Poll::first();

    expect($poll->options->pluck('option_text')->all())->toBe(['1', '2', '3', '4', '5']);
});

it('generates a public token on create', function () {
    $poll = Poll::factory()->create(['created_by' => $this->teacher->id]);

    expect($poll->public_token)->not->toBeNull();
});

it('exposes a public poll by public token', function () {
    $poll = Poll::factory()->active()->create(['created_by' => $this->teacher->id]);
    PollOption::factory(2)->create(['poll_id' => $poll->id]);

    $this->getJson("/api/polls/public/{$poll->public_token}")
        ->assertOk()
        ->assertJsonPath('poll.id', $poll->id)
        ->assertJsonPath('poll.public_token', $poll->public_token);
});

it('returns 404 for an invalid public token', function () {
    $this->getJson('/api/polls/public/does-not-exist')
        ->assertNotFound();
});

it('guest can vote via public token and cannot vote twice', function () {
    $poll = Poll::factory()->active()->create(['created_by' => $this->teacher->id]);
    $option = PollOption::factory()->create(['poll_id' => $poll->id]);
    $token = 'guest-token-'.uniqid();

    $this->postJson("/api/polls/public/{$poll->public_token}/vote", [
        'option_id' => $option->id,
        'guest_token' => $token,
    ])->assertStatus(201);

    $this->postJson("/api/polls/public/{$poll->public_token}/vote", [
        'option_id' => $option->id,
        'guest_token' => $token,
    ])->assertUnprocessable();
});

it('dashboard stats returns counts', function () {
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

it('allows starting a draft poll', function () {
    $poll = Poll::factory()->create(['created_by' => $this->teacher->id]);

    actingAs($this->teacher)
        ->postJson("/api/polls/{$poll->id}/start")
        ->assertOk();

    expect($poll->fresh()->status)->toBe('active');
});

it('allows ending an active poll', function () {
    $poll = Poll::factory()->active()->create(['created_by' => $this->teacher->id]);

    actingAs($this->teacher)
        ->postJson("/api/polls/{$poll->id}/end")
        ->assertOk();

    expect($poll->fresh()->status)->toBe('closed');
});
