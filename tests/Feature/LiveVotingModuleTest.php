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
            'question' => 'Is Laravel great?',
            'poll_type' => 'yes_no',
            'options' => ['Yes', 'No'],
        ])
        ->assertCreated()
        ->assertJsonPath('data.poll_type', 'yes_no');

    $poll = Poll::first();

    expect($poll->options)->toHaveCount(2);
    expect($poll->options->pluck('option_text')->all())->toBe(['Yes', 'No']);
});

it('creates a rating poll with 1-5 options', function () {
    actingAs($this->teacher)
        ->postJson('/api/polls', [
            'question' => 'Rate the lesson',
            'poll_type' => 'rating',
            'options' => [],
        ])
        ->assertCreated()
        ->assertJsonPath('data.poll_type', 'rating');

    $poll = Poll::first();

    expect($poll->options->pluck('option_text')->all())->toBe(['1', '2', '3', '4', '5']);
});

it('generates a share token on create', function () {
    $poll = Poll::factory()->create(['teacher_id' => $this->teacher->id]);

    expect($poll->share_token)->not->toBeNull();
    expect(strlen($poll->share_token))->toBe(32);
});

it('exposes a public poll by share token', function () {
    $poll = Poll::factory()->active()->create(['teacher_id' => $this->teacher->id]);
    PollOption::factory(2)->create(['poll_id' => $poll->id]);

    $this->getJson("/api/polls/share/{$poll->share_token}")
        ->assertOk()
        ->assertJsonPath('data.id', $poll->id)
        ->assertJsonPath('data.share_token', $poll->share_token);
});

it('returns 404 for an invalid share token', function () {
    $this->getJson('/api/polls/share/does-not-exist')
        ->assertNotFound();
});

it('guest can vote via share token and cannot vote twice', function () {
    $poll = Poll::factory()->active()->create(['teacher_id' => $this->teacher->id]);
    $option = PollOption::factory()->create(['poll_id' => $poll->id]);
    $token = 'guest-token-'.uniqid();

    $this->postJson("/api/polls/{$poll->id}/vote", [
        'option_id' => $option->id,
        'voter_token' => $token,
    ])->assertOk();

    assertDatabaseCount('votes', 1);

    $this->postJson("/api/polls/{$poll->id}/vote", [
        'option_id' => $option->id,
        'voter_token' => $token,
    ])->assertUnprocessable();
});

it('auto-closes an expired poll', function () {
    $poll = Poll::factory()->active()->create([
        'teacher_id' => $this->teacher->id,
        'started_at' => now()->subMinutes(15),
        'duration_minutes' => 10,
    ]);

    $this->artisan('app:close-expired-polls')->assertSuccessful();

    expect($poll->fresh()->status)->toBe('ended');
    expect($poll->fresh()->ended_at)->not->toBeNull();
});

it('does not close polls whose timer has not expired', function () {
    $poll = Poll::factory()->active()->create([
        'teacher_id' => $this->teacher->id,
        'started_at' => now(),
        'duration_minutes' => 10,
    ]);

    $this->artisan('app:close-expired-polls')->assertSuccessful();

    expect($poll->fresh()->status)->toBe('active');
});

it('requires points when voting on a rating poll', function () {
    $poll = Poll::factory()->active()->create([
        'teacher_id' => $this->teacher->id,
        'poll_type' => 'rating',
    ]);
    PollOption::factory(5)->create(['poll_id' => $poll->id]);

    actingAs($this->student)
        ->postJson("/api/polls/{$poll->id}/vote", [])
        ->assertUnprocessable();

    actingAs($this->student)
        ->postJson("/api/polls/{$poll->id}/vote", [
            'points' => 4,
            'option_id' => $poll->options->skip(3)->first()->id,
        ])
        ->assertOk();
});
