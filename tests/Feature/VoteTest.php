<?php

use App\Models\Poll;
use App\Models\PollOption;
use App\Models\School;
use App\Models\User;
use App\Models\UserProfile;

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

it('students only see active polls from their own school', function () {
    $school = School::create(['name' => 'Alpha School']);
    $otherSchool = School::create(['name' => 'Beta School']);
    UserProfile::create(['user_id' => $this->student->id, 'school_id' => $school->id]);

    $ownPoll = Poll::factory()->active()->create([
        'teacher_id' => $this->teacher->id,
        'school_id' => $school->id,
        'province_id' => null,
    ]);
    $foreignPoll = Poll::factory()->active()->create([
        'teacher_id' => $this->teacher->id,
        'school_id' => $otherSchool->id,
        'province_id' => null,
    ]);

    actingAs($this->student)
        ->getJson('/api/polls/active')
        ->assertOk()
        ->assertJsonCount(1, 'polls')
        ->assertJsonFragment(['id' => $ownPoll->id])
        ->assertJsonMissing(['id' => $foreignPoll->id]);
});

it('students cannot view polls from a different school', function () {
    $school = School::create(['name' => 'Alpha School']);
    $otherSchool = School::create(['name' => 'Beta School']);
    UserProfile::create(['user_id' => $this->student->id, 'school_id' => $school->id]);

    $foreignPoll = Poll::factory()->active()->create([
        'teacher_id' => $this->teacher->id,
        'school_id' => $otherSchool->id,
        'province_id' => null,
    ]);

    actingAs($this->student)
        ->getJson("/api/polls/{$foreignPoll->id}")
        ->assertForbidden();
});
