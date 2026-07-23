<?php

use App\Models\Poll;
use App\Models\PollOption;
use App\Models\User;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    $this->teacher = User::factory()->create(['role' => 'teacher']);
});

it('returns 404 for inactive public poll', function () {
    $poll = Poll::factory()->create(['created_by' => $this->teacher->id]);
    PollOption::factory(2)->create(['poll_id' => $poll->id]);

    $this->getJson("/api/polls/public/{$poll->public_token}")->assertNotFound();
});

it('returns 404 for invalid public token', function () {
    $this->getJson('/api/polls/public/invalid-token')->assertNotFound();
});

it('public can view active poll by token', function () {
    $poll = Poll::factory()->active()->create(['created_by' => $this->teacher->id]);
    PollOption::factory(3)->create(['poll_id' => $poll->id]);

    $this->getJson("/api/polls/public/{$poll->public_token}")
        ->assertOk()
        ->assertJsonPath('poll.question', $poll->question);
});

it('public can vote and cannot vote twice with same guest token', function () {
    $poll = Poll::factory()->active()->create(['created_by' => $this->teacher->id]);
    $option = PollOption::factory()->create(['poll_id' => $poll->id]);
    $guestToken = 'guest-token-'.uniqid();

    $this->postJson("/api/polls/public/{$poll->public_token}/vote", [
        'option_id' => $option->id,
        'guest_token' => $guestToken,
    ])->assertStatus(201);

    $this->postJson("/api/polls/public/{$poll->public_token}/vote", [
        'option_id' => $option->id,
        'guest_token' => $guestToken,
    ])->assertUnprocessable();
});

it('active polls endpoint returns list', function () {
    $poll = Poll::factory()->active()->create(['created_by' => $this->teacher->id]);
    PollOption::factory(2)->create(['poll_id' => $poll->id]);

    actingAs($this->teacher)
        ->getJson('/api/polls/active')
        ->assertOk()
        ->assertJsonCount(1, 'polls');
});
