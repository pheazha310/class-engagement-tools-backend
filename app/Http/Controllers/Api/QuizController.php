<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreQuizRequest;
use App\Http\Requests\UpdateQuizRequest;
use App\Http\Resources\QuizResource;
use App\Models\Quiz;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class QuizController extends Controller
{
    public function index(Request $request): ResourceCollection
    {
        $query = Quiz::query();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('subject', 'like', "%{$search}%")
                    ->orWhere('class_name', 'like', "%{$search}%");
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($subject = $request->input('subject')) {
            $query->where('subject', $subject);
        }

        if ($class_name = $request->input('class_name')) {
            $query->where('class_name', $class_name);
        }

        $quizzes = $query->latest()->get();

        return QuizResource::collection($quizzes);
    }

    public function store(StoreQuizRequest $request): JsonResponse
    {
        $quiz = Quiz::create([
            'teacher_id' => $request->user()?->id ?? $request->input('teacher_id'),
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'subject' => $request->input('subject'),
            'class_name' => $request->input('class_name'),
            'duration' => $request->input('duration'),
            'passing_score' => $request->input('passing_score', 50),
            'due_date' => $request->input('due_date'),
            'shuffle_questions' => $request->input('shuffle_questions', false),
            'status' => $request->input('status', 'draft'),
        ]);

        return response()->json(['data' => new QuizResource($quiz)], 201);
    }

    public function show(Quiz $quiz): JsonResponse
    {
        return response()->json(['data' => new QuizResource($quiz)]);
    }

    public function update(UpdateQuizRequest $request, Quiz $quiz): JsonResponse
    {
        $quiz->update($request->validated());

        return response()->json(['data' => new QuizResource($quiz->fresh())]);
    }

    public function destroy(Quiz $quiz): JsonResponse
    {
        $quiz->delete();

        return response()->json(['message' => 'Quiz deleted successfully.']);
    }

    public function duplicate(Request $request, Quiz $quiz): JsonResponse
    {
        $copy = Quiz::create([
            'teacher_id' => $quiz->teacher_id,
            'title' => $quiz->title.' (Copy)',
            'description' => $quiz->description,
            'subject' => $quiz->subject,
            'class_name' => $quiz->class_name,
            'duration' => $quiz->duration,
            'passing_score' => $quiz->passing_score,
            'due_date' => $quiz->due_date,
            'shuffle_questions' => $quiz->shuffle_questions,
            'status' => 'draft',
        ]);

        return response()->json(['data' => new QuizResource($copy)], 201);
    }
}
