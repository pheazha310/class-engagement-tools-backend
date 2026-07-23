<?php

use App\Models\Poll;
use App\Models\PollOption;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseCount;

beforeEach(function () {
    $this->teacher = User::factory()->create(['role' => 'teacher']);
    $this->student = User::factory()->create(['role' => 'student']);
});

it('creates a yes/no poll with default options', function () {
    actingAs($this->teacher)
        ->postJson('/api/polls', [
            'title' => 'Laravel Poll',
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
            'title' => 'Rate the Lesson',
            'question' => 'Rate the lesson',
            'poll_type' => 'rating',
            'options' => [],
        ])
        ->assertCreated()
        ->assertJsonPath('poll.poll_type', 'rating');

    $poll = Poll::first();
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
    ])->assertCreated();

    assertDatabaseCount('votes', 1);

    $this->postJson("/api/polls/public/{$poll->public_token}/vote", [
        'option_id' => $option->id,
        'guest_token' => $token,
    ])->assertUnprocessable();
});

it('auto-closes an expired poll', function () {
    $poll = Poll::factory()->active()->create([
        'created_by' => $this->teacher->id,
        'started_at' => now()->subMinutes(15),
        'duration_minutes' => 10,
    ]);

    $this->artisan('app:close-expired-polls')->assertSuccessful();

    expect($poll->fresh()->status)->toBe('closed');
    expect($poll->fresh()->ended_at)->not->toBeNull();
});

it('does not close polls whose timer has not expired', function () {
    $poll = Poll::factory()->active()->create([
        'created_by' => $this->teacher->id,
        'started_at' => now(),
        'duration_minutes' => 10,
    ]);

    $this->artisan('app:close-expired-polls')->assertSuccessful();

    expect($poll->fresh()->status)->toBe('active');
});

it('requires option_id when voting on a rating poll', function () {
    $poll = Poll::factory()->active()->create([
        'created_by' => $this->teacher->id,
        'poll_type' => 'rating',
    ]);
    PollOption::factory(5)->create(['poll_id' => $poll->id]);

    actingAs($this->student)
        ->postJson("/api/polls/public/{$poll->public_token}/vote", [])
        ->assertUnprocessable();

    actingAs($this->student)
        ->postJson("/api/polls/public/{$poll->public_token}/vote", [
            'option_id' => $poll->options->skip(3)->first()->id,
        ])
        ->assertCreated();
});
