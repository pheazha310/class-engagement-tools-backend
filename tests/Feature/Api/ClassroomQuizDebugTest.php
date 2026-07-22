<?php

use App\Models\Question;
use App\Models\Quiz;

beforeEach(function () {
    $this->quiz = Quiz::factory()->create([
        'id' => 'test-quiz-1',
        'title' => 'Debug Quiz',
        'subject' => 'Debug',
        'class_name' => 'Grade 10A',
        'duration' => 10,
        'passing_score' => 50,
        'shuffle_questions' => false,
        'status' => 'published',
        'due_date' => now()->addDays(7),
    ]);

    Question::create([
        'id' => 'test-q-1',
        'quiz_id' => $this->quiz->id,
        'question_text' => 'Debug Question',
        'question_type' => 'multiple_choice',
        'points' => 10,
        'choices' => [
            ['id' => 'a', 'choice_text' => 'A', 'is_correct' => true],
            ['id' => 'b', 'choice_text' => 'B', 'is_correct' => false],
        ],
        'correct_answer' => 'a',
        'order' => 1,
    ]);
});

it('can find the quiz by ID directly', function () {
    $found = Quiz::find('test-quiz-1');
    expect($found)->not->toBeNull()
        ->and($found->id)->toBe('test-quiz-1')
        ->and($found->title)->toBe('Debug Quiz');
});

it('can find the quiz by route', function () {
    $response = $this->getJson("/api/v1/classroom/quizzes/{$this->quiz->id}");
    dump('URL called: '."/api/v1/classroom/quizzes/{$this->quiz->id}");
    dump('Quiz ID in test: '.$this->quiz->id);
    $response->assertStatus(200);
});

it('can submit with correct question ID', function () {
    $response = $this->postJson('/api/v1/classroom/submissions', [
        'quizId' => $this->quiz->id,
        'studentName' => 'Debug Student',
        'class_name' => 'Grade 10A',
        'answers' => [
            ['questionId' => 'test-q-1', 'selectedChoiceId' => 'a'],
        ],
        'timeTaken' => 30,
    ]);

    $response->assertStatus(201);
});

it('lists quizzes includes the debug quiz', function () {
    $response = $this->getJson('/api/v1/classroom/quizzes');
    $ids = collect($response->json('data'))->pluck('id');
    dump('Quiz IDs in list: '.$ids->implode(', '));
    expect($ids)->toContain('test-quiz-1');
});
