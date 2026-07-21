<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\QuestionResource;
use App\Models\Question;
use App\Models\Quiz;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class QuizQuestionController extends Controller
{
    public function index(Quiz $quiz): ResourceCollection
    {
        $questions = $quiz->questions()->orderBy('order')->get();

        return QuestionResource::collection($questions);
    }

    public function store(Request $request, Quiz $quiz): JsonResponse
    {
        $validated = $request->validate([
            'question_text' => 'required|string',
            'question_type' => 'required|in:multiple_choice,true_false,short_answer',
            'points' => 'required|integer|min:1',
            'choices' => 'nullable|array',
            'choices.*.choice_text' => 'required_with:choices|string',
            'choices.*.is_correct' => 'boolean',
            'correct_answer' => 'nullable|string',
        ]);

        $maxOrder = $quiz->questions()->max('order') ?? 0;

        $question = $quiz->questions()->create([
            'question_text' => $validated['question_text'],
            'question_type' => $validated['question_type'],
            'points' => $validated['points'],
            'choices' => $validated['choices'] ?? [],
            'correct_answer' => $validated['correct_answer'] ?? '',
            'order' => $maxOrder + 1,
        ]);

        return response()->json(['data' => new QuestionResource($question)], 201);
    }

    public function show(Question $question): JsonResponse
    {
        return response()->json(['data' => new QuestionResource($question)]);
    }

    public function update(Request $request, Question $question): JsonResponse
    {
        $validated = $request->validate([
            'question_text' => 'sometimes|required|string',
            'question_type' => 'sometimes|required|in:multiple_choice,true_false,short_answer',
            'points' => 'sometimes|required|integer|min:1',
            'choices' => 'nullable|array',
            'choices.*.choice_text' => 'required_with:choices|string',
            'choices.*.is_correct' => 'boolean',
            'correct_answer' => 'nullable|string',
        ]);

        $question->update($validated);

        return response()->json(['data' => new QuestionResource($question->fresh())]);
    }

    public function destroy(Question $question): JsonResponse
    {
        $question->delete();

        return response()->json(['message' => 'Question deleted successfully.']);
    }
}
