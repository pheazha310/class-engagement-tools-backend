<?php

namespace App\Http\Controllers\Api\Classroom;

use App\Http\Controllers\Controller;
use App\Http\Resources\Classroom\ClassroomQuizResource;
use App\Models\Question;
use App\Models\Quiz;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Str;

class ClassroomQuizController extends Controller
{
    public function index(Request $request): ResourceCollection
    {
        $query = Quiz::withCount('questions');

        if ($request->has('search')) {
            $search = $request->input('search');
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

        $quizzes = $query->latest()->get();

        $resource = ClassroomQuizResource::collection($quizzes);

        return $resource;
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'subject' => 'required|string|max:255',
            'class_name' => 'required|string|max:255',
            'duration' => 'required|integer|min:1|max:480',
            'passing_score' => 'integer|min:0|max:100',
            'shuffle_questions' => 'boolean',
            'status' => 'string|in:draft,published',
            'questions' => 'sometimes|array',
            'questions.*.id' => 'nullable|string',
            'questions.*.question_text' => 'required_with:questions|string',
            'questions.*.question_type' => 'required_with:questions|in:multiple_choice,multiple_answer,true_false,short_answer',
            'questions.*.points' => 'required_with:questions|integer|min:1',
            'questions.*.choices' => 'nullable|array',
            'questions.*.choices.*.id' => 'required_with:questions.*.choices|string',
            'questions.*.choices.*.choice_text' => 'required_with:questions.*.choices|string',
            'questions.*.choices.*.is_correct' => 'boolean',
            'questions.*.correct_answer' => 'nullable|string',
            'questions.*.order' => 'integer|min:0',
        ]);

        $quiz = Quiz::create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'subject' => $validated['subject'],
            'class_name' => $validated['class_name'],
            'duration' => $validated['duration'],
            'passing_score' => $validated['passing_score'] ?? 50,
            'due_date' => now()->addDays(7),
            'shuffle_questions' => $validated['shuffle_questions'] ?? false,
            'status' => $validated['status'] ?? 'published',
        ]);

        if (! empty($validated['questions'])) {
            $questions = collect($validated['questions'])->map(function ($q) {
                return new Question([
                    'id' => $q['id'] ?? (string) Str::uuid(),
                    'question_text' => $q['question_text'],
                    'question_type' => $q['question_type'],
                    'points' => $q['points'],
                    'choices' => $q['choices'] ?? [],
                    'correct_answer' => $q['correct_answer'] ?? '',
                    'order' => $q['order'] ?? 0,
                ]);
            });

            $quiz->questions()->saveMany($questions);
            $quiz->load('questions');
        }

        $quiz->loadCount('questions');

        $resource = (new ClassroomQuizResource($quiz))->withQuestions(true);

        return response()->json(['data' => $resource], 201);
    }

    public function show(string $quiz): JsonResponse
    {
        $quizModel = Quiz::findOrFail($quiz);

        $quizModel->load(['questions' => fn ($q) => $q->orderBy('order')]);
        $quizModel->loadCount('questions');

        $resource = (new ClassroomQuizResource($quizModel))->withQuestions(true);

        return response()->json(['data' => $resource]);
    }

    public function update(Request $request, string $quiz): JsonResponse
    {
        $quizModel = Quiz::findOrFail($quiz);

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'subject' => 'sometimes|required|string|max:255',
            'class_name' => 'sometimes|required|string|max:255',
            'duration' => 'sometimes|required|integer|min:1|max:480',
            'passing_score' => 'integer|min:0|max:100',
            'shuffle_questions' => 'boolean',
            'status' => 'string|in:draft,published',
            'questions' => 'sometimes|array',
            'questions.*.id' => 'nullable|string',
            'questions.*.question_text' => 'required_with:questions|string',
            'questions.*.question_type' => 'required_with:questions|in:multiple_choice,multiple_answer,true_false,short_answer',
            'questions.*.points' => 'required_with:questions|integer|min:1',
            'questions.*.choices' => 'nullable|array',
            'questions.*.choices.*.id' => 'required_with:questions.*.choices|string',
            'questions.*.choices.*.choice_text' => 'required_with:questions.*.choices|string',
            'questions.*.choices.*.is_correct' => 'boolean',
            'questions.*.correct_answer' => 'nullable|string',
            'questions.*.order' => 'integer|min:0',
        ]);

        $quizModel->update(collect($validated)->except('questions')->toArray());

        // Sync questions if provided
        if ($request->has('questions')) {
            $existingQuestionIds = $quiz->questions()->pluck('id')->toArray();
            $incomingQuestionIds = collect($validated['questions'])->pluck('id')->filter()->toArray();

            // Delete questions not in the incoming list
            $toDelete = array_diff($existingQuestionIds, $incomingQuestionIds);
            if (! empty($toDelete)) {
                $quiz->questions()->whereIn('id', $toDelete)->delete();
            }

            foreach ($validated['questions'] as $q) {
                $questionData = [
                    'question_text' => $q['question_text'],
                    'question_type' => $q['question_type'],
                    'points' => $q['points'],
                    'choices' => $q['choices'] ?? [],
                    'correct_answer' => $q['correct_answer'] ?? '',
                    'order' => $q['order'] ?? 0,
                ];

                if (! empty($q['id']) && in_array($q['id'], $existingQuestionIds)) {
                    // Update existing question
                    $quiz->questions()->where('id', $q['id'])->update($questionData);
                } else {
                    // Create new question
                    $quiz->questions()->create(array_merge(
                        ['id' => $q['id'] ?? (string) Str::uuid()],
                        $questionData
                    ));
                }
            }

            $quizModel->load('questions');
        }

        $quizModel->loadCount('questions');

        $resource = (new ClassroomQuizResource($quizModel))->withQuestions(true);

        return response()->json(['data' => $resource]);
    }

    public function destroy(string $quiz): JsonResponse
    {
        $quizModel = Quiz::findOrFail($quiz);
        $quizModel->delete();

        return response()->json(null, 204);
    }
}
