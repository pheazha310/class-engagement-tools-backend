<?php

use function Pest\Laravel\postJson;

it('generates math questions for math-challenge', function () {
    $response = postJson('/api/game-sessions/generate-questions', [
        'game_type' => 'math-challenge',
        'settings' => [
            'difficulty' => 'easy',
            'numQuestions' => 3,
            'operations' => ['+'],
        ],
    ])->assertOk();

    $data = $response->json('questions');

    expect($data)->toHaveCount(3);
    expect($data[0]['type'])->toBe('math');
    expect($data[0]['question'])->toMatch('/^\d+ \+ \d+ = \?$/');
    expect($data[0]['answer'])->toBe((string) ((int) $data[0]['answer']));
    expect($data[0]['difficulty'])->toBe('easy');
});

it('generates medium difficulty math questions', function () {
    $response = postJson('/api/game-sessions/generate-questions', [
        'game_type' => 'math-challenge',
        'settings' => [
            'difficulty' => 'medium',
            'numQuestions' => 5,
            'operations' => ['+', '-'],
        ],
    ])->assertOk();

    $data = $response->json('questions');

    expect($data)->toHaveCount(5);
    expect($data[0]['type'])->toBe('math');
    expect($data[0]['difficulty'])->toBe('medium');
});

it('generates hard difficulty math questions', function () {
    $response = postJson('/api/game-sessions/generate-questions', [
        'game_type' => 'math-challenge',
        'settings' => [
            'difficulty' => 'hard',
            'numQuestions' => 2,
            'operations' => ['×'],
        ],
    ])->assertOk();

    $data = $response->json('questions');

    expect($data)->toHaveCount(2);
    expect($data[0]['type'])->toBe('math');
    expect($data[0]['difficulty'])->toBe('hard');
});

it('generates vocabulary questions for vocabulary-race', function () {
    $response = postJson('/api/game-sessions/generate-questions', [
        'game_type' => 'vocabulary-race',
        'settings' => [
            'wordList' => ["apple\n", "banana\n", 'cherry', 'date'],
            'rounds' => 2,
            'timeLimit' => 60,
        ],
    ])->assertOk();

    $data = $response->json('questions');

    expect($data)->toHaveCount(8);
    expect($data[0]['type'])->toBe('vocabulary');
    expect($data[0]['word'])->not->toBeEmpty();
    expect($data[0]['answer'])->toBe($data[0]['word']);
});

it('generates quiz questions for quiz-battle', function () {
    $response = postJson('/api/game-sessions/generate-questions', [
        'game_type' => 'quiz-battle',
        'settings' => [
            'numQuestions' => 3,
            'categories' => ['Science'],
            'difficulty' => 'easy',
        ],
    ])->assertOk();

    $data = $response->json('questions');

    expect($data)->toHaveCount(3);
    expect($data[0]['type'])->toBe('quiz');
    expect($data[0]['category'])->toBe('Science');
    expect($data[0]['question'])->not->toBeEmpty();
    expect($data[0]['options'])->toHaveCount(4);
    expect($data[0]['answer'])->not->toBeEmpty();
    expect($data[0]['difficulty'])->toBe('easy');
});

it('generates all categories when none specified for quiz-battle', function () {
    $response = postJson('/api/game-sessions/generate-questions', [
        'game_type' => 'quiz-battle',
        'settings' => [
            'numQuestions' => 5,
            'categories' => [],
            'difficulty' => 'medium',
        ],
    ])->assertOk();

    $data = $response->json('questions');

    expect($data)->toHaveCount(5);
});

it('generates memory game cards', function () {
    $response = postJson('/api/game-sessions/generate-questions', [
        'game_type' => 'memory-game',
        'settings' => [
            'gridSize' => 4,
            'theme' => 'animals',
            'timeLimit' => 120,
            'pairs' => 4,
        ],
    ])->assertOk();

    $data = $response->json('questions');

    expect($data['theme'])->toBe('animals');
    expect($data['pairs'])->toBe(4);
    expect($data['cards'])->toHaveCount(8);
});

it('returns empty questions for unsupported game type', function () {
    $response = postJson('/api/game-sessions/generate-questions', [
        'game_type' => 'unknown-game',
        'settings' => [],
    ])->assertOk();

    expect($response->json('questions'))->toBeEmpty();
});

it('requires game_type when generating questions', function () {
    postJson('/api/game-sessions/generate-questions', [])
        ->assertUnprocessable();
});
