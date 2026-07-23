<?php

use App\Models\Quiz;
use App\Models\User;

beforeEach(function () {
    $this->teacher = User::factory()->create(['role' => 'teacher']);
    $this->token = $this->teacher->createToken('test-token')->plainTextToken;
});

it('can create a quiz', function () {
    $response = $this->withToken($this->token)->postJson('/api/quizzes', [
        'title' => 'Mathematics Final Exam',
        'description' => 'Covers chapters 5-8',
        'subject' => 'Mathematics',
        'class_name' => 'Grade 10A',
        'duration' => 60,
        'passing_score' => 50,
        'due_date' => '2026-07-20T23:59',
        'shuffle_questions' => false,
        'status' => 'draft',
    ]);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'data' => [
                'id', 'title', 'description', 'subject',
                'class_name', 'duration', 'passing_score', 'due_date',
                'shuffle_questions', 'status', 'created_at', 'updated_at',
            ],
        ]);

    expect($response->json('data.title'))->toBe('Mathematics Final Exam')
        ->and($response->json('data.status'))->toBe('draft');
});

it('allows creating a quiz without authentication', function () {
    $response = $this->postJson('/api/quizzes', [
        'title' => 'Test Quiz',
        'subject' => 'Math',
        'class_name' => 'Grade 10A',
        'duration' => 30,
        'due_date' => '2026-07-20T23:59',
    ]);

    $response->assertStatus(201);
});

it('validates required fields when creating a quiz', function () {
    $response = $this->withToken($this->token)->postJson('/api/quizzes', []);

    $response->assertStatus(422)
        ->assertJsonStructure(['message', 'errors']);
});

it('can list all quizzes', function () {
    Quiz::factory()->count(3)->create(['teacher_id' => $this->teacher->id]);
    Quiz::factory()->create(); // different teacher

    $response = $this->getJson('/api/quizzes');

    $response->assertStatus(200)
        ->assertJsonStructure(['data']);
    expect(count($response->json('data')))->toBe(4);
});

it('can search quizzes by title, subject, or class name', function () {
    Quiz::factory()->create([
        'teacher_id' => $this->teacher->id,
        'title' => 'Algebra Basics',
        'subject' => 'Mathematics',
    ]);
    Quiz::factory()->create([
        'teacher_id' => $this->teacher->id,
        'title' => 'World History',
        'subject' => 'History',
    ]);

    $response = $this->getJson('/api/quizzes?search=Algebra');
    expect(count($response->json('data')))->toBe(1)
        ->and($response->json('data.0.title'))->toBe('Algebra Basics');

    $response = $this->getJson('/api/quizzes?search=History');
    expect(count($response->json('data')))->toBe(1);
});

it('can filter quizzes by status', function () {
    Quiz::factory()->create([
        'teacher_id' => $this->teacher->id,
        'status' => 'draft',
    ]);
    Quiz::factory()->published()->create([
        'teacher_id' => $this->teacher->id,
    ]);

    $response = $this->getJson('/api/quizzes?status=draft');
    expect(count($response->json('data')))->toBe(1)
        ->and($response->json('data.0.status'))->toBe('draft');
});

it('can filter quizzes by subject', function () {
    Quiz::factory()->create([
        'teacher_id' => $this->teacher->id,
        'subject' => 'Mathematics',
    ]);
    Quiz::factory()->create([
        'teacher_id' => $this->teacher->id,
        'subject' => 'English',
    ]);

    $response = $this->getJson('/api/quizzes?subject=Mathematics');
    expect(count($response->json('data')))->toBe(1);
});

it('can show a single quiz', function () {
    $quiz = Quiz::factory()->create(['teacher_id' => $this->teacher->id]);

    $response = $this->getJson("/api/quizzes/{$quiz->id}");

    $response->assertStatus(200)
        ->assertJsonStructure(['data' => ['id', 'title']]);
    expect($response->json('data.id'))->toBe($quiz->id);
});

it('returns quizzes from all teachers when showing a quiz', function () {
    $otherTeacher = User::factory()->create(['role' => 'teacher']);
    $quiz = Quiz::factory()->create(['teacher_id' => $otherTeacher->id]);

    $response = $this->withToken($this->token)->getJson("/api/quizzes/{$quiz->id}");

    $response->assertStatus(200);
    expect($response->json('data.id'))->toBe($quiz->id);
});

it('can update a quiz', function () {
    $quiz = Quiz::factory()->create([
        'teacher_id' => $this->teacher->id,
        'title' => 'Original Title',
    ]);

    $response = $this->withToken($this->token)->putJson("/api/quizzes/{$quiz->id}", [
        'title' => 'Updated Title',
    ]);

    $response->assertStatus(200);
    expect($response->json('data.title'))->toBe('Updated Title');
});

it('can delete a quiz', function () {
    $quiz = Quiz::factory()->create(['teacher_id' => $this->teacher->id]);

    $response = $this->withToken($this->token)->deleteJson("/api/quizzes/{$quiz->id}");

    $response->assertStatus(200)
        ->assertJson(['message' => 'Quiz deleted successfully.']);
    expect(Quiz::find($quiz->id))->toBeNull();
});

it('can duplicate a quiz', function () {
    $quiz = Quiz::factory()->create([
        'teacher_id' => $this->teacher->id,
        'title' => 'Original Quiz',
        'status' => 'published',
    ]);

    $response = $this->withToken($this->token)->postJson("/api/quizzes/{$quiz->id}/duplicate");

    $response->assertStatus(201)
        ->assertJsonStructure(['data' => ['id', 'title', 'status']]);
    expect($response->json('data.title'))->toBe('Original Quiz (Copy)')
        ->and($response->json('data.status'))->toBe('draft');
});

it('orders quizzes by newest first', function () {
    Quiz::factory()->create(['teacher_id' => $this->teacher->id]);
    $this->travel(1)->second();
    $second = Quiz::factory()->create(['teacher_id' => $this->teacher->id]);

    $response = $this->getJson('/api/quizzes');

    expect($response->json('data.0.id'))->toBe($second->id);
});
