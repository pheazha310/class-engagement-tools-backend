<?php

use App\Models\Poll;
use App\Models\PollOption;
use App\Models\User;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    $this->teacher = User::factory()->create(['role' => 'teacher']);
    $this->student = User::factory()->create(['role' => 'student']);
});

it('validates option belongs to poll', function () {
    $poll1 = Poll::factory()->active()->create(['teacher_id' => $this->teacher->id]);
    $poll2 = Poll::factory()->active()->create(['teacher_id' => $this->teacher->id]);
    $optionFromPoll2 = PollOption::factory()->create(['poll_id' => $poll2->id]);

    actingAs($this->student)
        ->postJson("/api/polls/{$poll1->id}/vote", [
            'option_id' => $optionFromPoll2->id,
        ])
        ->assertUnprocessable();
});

it('requires authentication for voting', function () {
    $poll = Poll::factory()->active()->create(['teacher_id' => $this->teacher->id]);
    $option = PollOption::factory()->create(['poll_id' => $poll->id]);

    $this->postJson("/api/polls/{$poll->id}/vote", [
        'option_id' => $option->id,
    ])->assertUnauthorized();
});

it('students can view active poll', function () {
    $poll = Poll::factory()->active()->create(['teacher_id' => $this->teacher->id]);
    PollOption::factory(3)->create(['poll_id' => $poll->id]);

    actingAs($this->student)
        ->getJson('/api/polls/active')
        ->assertOk()
        ->assertJsonPath('poll.question', $poll->question);
});

it('returns 404 when no active poll', function () {
    actingAs($this->student)
        ->getJson('/api/polls/active')
        ->assertNotFound();
});
