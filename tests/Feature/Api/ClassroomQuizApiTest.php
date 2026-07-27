<?php

use App\Models\Question;
use App\Models\Quiz;
use App\Models\User;

beforeEach(function () {
    $this->teacher = User::factory()->create(['role' => 'teacher']);
    $this->token = $this->teacher->createToken('test-token')->plainTextToken;

    // Create a seeded quiz for testing
    $this->quiz = Quiz::factory()->create([
        'id' => 'test-quiz-1',
        'title' => 'Test Quiz',
        'description' => 'A test quiz',
        'subject' => 'Testing',
        'class_name' => 'Grade 10A',
        'duration' => 10,
        'passing_score' => 50,
        'shuffle_questions' => false,
        'status' => 'published',
        'due_date' => now()->addDays(7),
        'teacher_id' => $this->teacher->id,
    ]);

    // Create questions
    $this->questions = [];
    foreach (range(1, 3) as $i) {
        $question = Question::create([
            'id' => "test-q-{$i}",
            'quiz_id' => $this->quiz->id,
            'question_text' => "Question {$i}",
            'question_type' => 'multiple_choice',
            'points' => 10,
            'choices' => [
                ['id' => 'a', 'choice_text' => 'Option A', 'is_correct' => $i === 1],
                ['id' => 'b', 'choice_text' => 'Option B', 'is_correct' => $i === 2],
                ['id' => 'c', 'choice_text' => 'Option C', 'is_correct' => $i === 3],
                ['id' => 'd', 'choice_text' => 'Option D', 'is_correct' => false],
            ],
            'correct_answer' => match ($i) {
                1 => 'a',
                2 => 'b',
                3 => 'c',
            },
            'order' => $i,
        ]);
        $this->questions[] = $question;
    }

    // Create a question with multiple_answer type
    Question::create([
        'id' => 'test-q-ma',
        'quiz_id' => $this->quiz->id,
        'question_text' => 'Select all that apply',
        'question_type' => 'multiple_answer',
        'points' => 10,
        'choices' => [
            ['id' => 'a', 'choice_text' => 'Correct 1', 'is_correct' => true],
            ['id' => 'b', 'choice_text' => 'Correct 2', 'is_correct' => true],
            ['id' => 'c', 'choice_text' => 'Wrong 1', 'is_correct' => false],
        ],
        'correct_answer' => 'a,b',
        'order' => 4,
    ]);

    Question::create([
        'id' => 'test-q-tf',
        'quiz_id' => $this->quiz->id,
        'question_text' => 'The sky is blue',
        'question_type' => 'true_false',
        'points' => 10,
        'choices' => [
            ['id' => 'true', 'choice_text' => 'True', 'is_correct' => true],
            ['id' => 'false', 'choice_text' => 'False', 'is_correct' => false],
        ],
        'correct_answer' => 'true',
        'order' => 5,
    ]);

    Question::create([
        'id' => 'test-q-sa',
        'quiz_id' => $this->quiz->id,
        'question_text' => 'What is 2+2?',
        'question_type' => 'short_answer',
        'points' => 10,
        'choices' => [],
        'correct_answer' => '4',
        'order' => 6,
    ]);
});

it('can list quizzes with questions_count', function () {
    $response = $this->withToken($this->token)->getJson('/api/v1/classroom/quizzes');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id', 'title', 'description', 'subject',
                    'class_name', 'duration', 'passing_score',
                    'shuffle_questions', 'status', 'questions_count',
                    'created_at', 'updated_at',
                ],
            ],
        ]);

    $quiz = collect($response->json('data'))->firstWhere('id', $this->quiz->id);
    expect($quiz)->not->toBeNull()
        ->and($quiz['title'])->toBe('Test Quiz')
        ->and($quiz['questions_count'])->toBe(6);
});

it('can get a single quiz with embedded questions', function () {
    $response = $this->withToken($this->token)->getJson("/api/v1/classroom/quizzes/{$this->quiz->id}");

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                'id', 'title', 'description', 'subject',
                'class_name', 'duration', 'passing_score',
                'shuffle_questions', 'status',
                'questions' => [
                    '*' => [
                        'id', 'question_text', 'question_type',
                        'points', 'choices', 'correct_answer', 'order',
                    ],
                ],
                'created_at', 'updated_at',
            ],
        ]);

    expect($response->json('data.questions'))->toHaveCount(6);
});

it('can submit a quiz and get score', function () {
    $response = $this->withToken($this->token)->postJson('/api/v1/classroom/submissions', [
        'quizId' => $this->quiz->id,
        'studentName' => 'John Doe',
        'class_name' => 'Grade 10A',
        'answers' => [
            ['questionId' => 'test-q-1', 'selectedChoiceId' => 'a'],  // correct
            ['questionId' => 'test-q-2', 'selectedChoiceId' => 'b'],  // correct
            ['questionId' => 'test-q-3', 'selectedChoiceId' => 'd'],  // wrong
            ['questionId' => 'test-q-ma', 'selectedChoiceId' => 'a,b'], // correct (both correct choices)
            ['questionId' => 'test-q-tf', 'selectedChoiceId' => 'true'], // correct
            ['questionId' => 'test-q-sa', 'selectedChoiceId' => '4'],  // correct
        ],
        'timeTaken' => 120,
    ]);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'data' => [
                'id', 'quizId', 'studentName', 'class_name',
                'answers', 'score', 'totalPoints', 'percentage',
                'timeTaken', 'submittedAt', 'status', 'passingScore',
            ],
        ]);

    expect($response->json('data.studentName'))->toBe('John Doe')
        ->and($response->json('data.status'))->toBe('pass')
        ->and($response->json('data.score'))->toBe(50) // 5 correct * 10 points
        ->and($response->json('data.totalPoints'))->toBe(60)
        ->and($response->json('data.percentage'))->toBe(83.33);
});

it('returns fail status when score is below passing', function () {
    $response = $this->withToken($this->token)->postJson('/api/v1/classroom/submissions', [
        'quizId' => $this->quiz->id,
        'studentName' => 'Jane Doe',
        'class_name' => 'Grade 10A',
        'answers' => [
            ['questionId' => 'test-q-1', 'selectedChoiceId' => 'b'],  // wrong
            ['questionId' => 'test-q-2', 'selectedChoiceId' => 'c'],  // wrong
            ['questionId' => 'test-q-3', 'selectedChoiceId' => 'd'],  // wrong
            ['questionId' => 'test-q-ma', 'selectedChoiceId' => 'a'], // wrong (missing b)
            ['questionId' => 'test-q-tf', 'selectedChoiceId' => 'false'], // wrong
            ['questionId' => 'test-q-sa', 'selectedChoiceId' => '5'],  // wrong
        ],
        'timeTaken' => 120,
    ]);

    $response->assertStatus(201);
    expect($response->json('data.status'))->toBe('fail')
        ->and($response->json('data.score'))->toBe(0);
});

it('can check if a student has submitted', function () {
    // Submit first
    $this->withToken($this->token)->postJson('/api/v1/classroom/submissions', [
        'quizId' => $this->quiz->id,
        'studentName' => 'Mark',
        'class_name' => 'Grade 10A',
        'answers' => [
            ['questionId' => 'test-q-1', 'selectedChoiceId' => 'a'],
            ['questionId' => 'test-q-2', 'selectedChoiceId' => 'b'],
            ['questionId' => 'test-q-3', 'selectedChoiceId' => 'c'],
            ['questionId' => 'test-q-ma', 'selectedChoiceId' => 'a,b'],
            ['questionId' => 'test-q-tf', 'selectedChoiceId' => 'true'],
            ['questionId' => 'test-q-sa', 'selectedChoiceId' => '4'],
        ],
        'timeTaken' => 60,
    ]);

    // Check submitted student
    $response = $this->withToken($this->token)->getJson('/api/v1/classroom/submissions/check?quizId='.$this->quiz->id.'&studentName=Mark');
    $response->assertStatus(200)
        ->assertJson(['hasSubmitted' => true]);

    // Check non-submitted student
    $response = $this->withToken($this->token)->getJson('/api/v1/classroom/submissions/check?quizId='.$this->quiz->id.'&studentName=Unknown');
    $response->assertStatus(200)
        ->assertJson(['hasSubmitted' => false]);
});

it('can get student submissions', function () {
    $this->withToken($this->token)->postJson('/api/v1/classroom/submissions', [
        'quizId' => $this->quiz->id,
        'studentName' => 'Alice',
        'class_name' => 'Grade 10A',
        'answers' => [
            ['questionId' => 'test-q-1', 'selectedChoiceId' => 'a'],
            ['questionId' => 'test-q-2', 'selectedChoiceId' => 'b'],
            ['questionId' => 'test-q-3', 'selectedChoiceId' => 'c'],
            ['questionId' => 'test-q-ma', 'selectedChoiceId' => 'a,b'],
            ['questionId' => 'test-q-tf', 'selectedChoiceId' => 'true'],
            ['questionId' => 'test-q-sa', 'selectedChoiceId' => '4'],
        ],
        'timeTaken' => 90,
    ]);

    $response = $this->withToken($this->token)->getJson('/api/v1/classroom/submissions?quizId='.$this->quiz->id.'&studentName=Alice');
    $response->assertStatus(200)
        ->assertJsonStructure(['data' => ['*' => ['id', 'quizId', 'studentName', 'score', 'percentage', 'status']]]);
    expect($response->json('data'))->toHaveCount(1);
});

it('can get rankings with best submission per student', function () {
    // Submit multiple students
    $students = [
        ['name' => 'Alice', 'score' => 5, 'answers' => ['a', 'b', 'c', 'a,b', 'true', '4'], 'time' => 90],
        ['name' => 'Bob', 'score' => 3, 'answers' => ['a', 'c', 'd', 'a', 'true', '4'], 'time' => 60],
        ['name' => 'Charlie', 'score' => 5, 'answers' => ['a', 'b', 'c', 'a,b', 'true', '4'], 'time' => 120],
    ];

    foreach ($students as $student) {
        $this->withToken($this->token)->postJson('/api/v1/classroom/submissions', [
            'quizId' => $this->quiz->id,
            'studentName' => $student['name'],
            'class_name' => 'Grade 10A',
            'answers' => [
                ['questionId' => 'test-q-1', 'selectedChoiceId' => $student['answers'][0]],
                ['questionId' => 'test-q-2', 'selectedChoiceId' => $student['answers'][1]],
                ['questionId' => 'test-q-3', 'selectedChoiceId' => $student['answers'][2]],
                ['questionId' => 'test-q-ma', 'selectedChoiceId' => $student['answers'][3]],
                ['questionId' => 'test-q-tf', 'selectedChoiceId' => $student['answers'][4]],
                ['questionId' => 'test-q-sa', 'selectedChoiceId' => $student['answers'][5]],
            ],
            'timeTaken' => $student['time'],
        ]);
    }

    $response = $this->withToken($this->token)->getJson("/api/v1/classroom/rankings/{$this->quiz->id}");
    $response->assertStatus(200)
        ->assertJsonStructure([
            'quizId',
            'rankings' => [
                '*' => [
                    'id', 'quiz_id', 'student_name', 'score',
                    'percentage', 'time_taken', 'submitted_at',
                    'status', 'class_name', 'rank',
                ],
            ],
        ]);

    $rankings = $response->json('rankings');
    expect($rankings)->toHaveCount(3)
        ->and($rankings[0]['student_name'])->toBe('Alice')  // score 5, time 90
        ->and($rankings[0]['rank'])->toBe(1)
        ->and($rankings[1]['student_name'])->toBe('Charlie') // score 5, time 120
        ->and($rankings[1]['rank'])->toBe(2)
        ->and($rankings[2]['student_name'])->toBe('Bob')     // score 3
        ->and($rankings[2]['rank'])->toBe(3);
});

it('allows multiple submissions and best score counts for ranking', function () {
    // First submission - low score
    $this->withToken($this->token)->postJson('/api/v1/classroom/submissions', [
        'quizId' => $this->quiz->id,
        'studentName' => 'David',
        'class_name' => 'Grade 10A',
        'answers' => [
            ['questionId' => 'test-q-1', 'selectedChoiceId' => 'b'],  // wrong
            ['questionId' => 'test-q-2', 'selectedChoiceId' => 'c'],  // wrong
            ['questionId' => 'test-q-3', 'selectedChoiceId' => 'd'],  // wrong
            ['questionId' => 'test-q-ma', 'selectedChoiceId' => 'a'],
            ['questionId' => 'test-q-tf', 'selectedChoiceId' => 'false'],
            ['questionId' => 'test-q-sa', 'selectedChoiceId' => '5'],
        ],
        'timeTaken' => 30,
    ]);

    // Second submission - better score
    $this->withToken($this->token)->postJson('/api/v1/classroom/submissions', [
        'quizId' => $this->quiz->id,
        'studentName' => 'David',
        'class_name' => 'Grade 10A',
        'answers' => [
            ['questionId' => 'test-q-1', 'selectedChoiceId' => 'a'],  // correct
            ['questionId' => 'test-q-2', 'selectedChoiceId' => 'b'],  // correct
            ['questionId' => 'test-q-3', 'selectedChoiceId' => 'c'],  // correct
            ['questionId' => 'test-q-ma', 'selectedChoiceId' => 'a,b'], // correct
            ['questionId' => 'test-q-tf', 'selectedChoiceId' => 'true'], // correct
            ['questionId' => 'test-q-sa', 'selectedChoiceId' => '4'],  // correct
        ],
        'timeTaken' => 60,
    ]);

    // Check rankings - David should have score 60 (best, all 6 correct)
    $response = $this->withToken($this->token)->getJson("/api/v1/classroom/rankings/{$this->quiz->id}");
    $david = collect($response->json('rankings'))->firstWhere('student_name', 'David');
    expect($david)->not->toBeNull()
        ->and($david['score'])->toBe(60);
});

it('can search quizzes', function () {
    $response = $this->withToken($this->token)->getJson('/api/v1/classroom/quizzes?search=Test');
    $response->assertStatus(200);
    expect($response->json('data'))->not->toBeEmpty();
});

it('can filter quizzes by subject', function () {
    $response = $this->withToken($this->token)->getJson('/api/v1/classroom/quizzes?subject=Testing');
    $response->assertStatus(200);
    expect($response->json('data'))->not->toBeEmpty();
});
