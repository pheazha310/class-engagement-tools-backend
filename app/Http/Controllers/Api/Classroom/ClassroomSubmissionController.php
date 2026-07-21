<?php

namespace App\Http\Controllers\Api\Classroom;

use App\Http\Controllers\Controller;
use App\Http\Resources\Classroom\ClassroomSubmissionResource;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\QuizSubmission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ClassroomSubmissionController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'quizId' => 'required|string|exists:quizzes,id',
            'studentName' => 'required|string|max:255',
            'class_name' => 'nullable|string|max:255',
            'answers' => 'required|array',
            'answers.*.questionId' => 'required|string|exists:questions,id',
            'answers.*.selectedChoiceId' => 'nullable|string',
            'timeTaken' => 'required|integer|min:0',
        ]);

        $quiz = Quiz::with('questions')->findOrFail($validated['quizId']);
        $questions = $quiz->questions->keyBy('id');

        $totalPoints = $questions->sum('points');
        $passingScore = $quiz->passing_score ?? 50;

        if ($totalPoints === 0) {
            return response()->json([
                'message' => 'Quiz has no questions with points assigned.',
                'errors' => ['quiz' => ['This quiz has no questions or no points configured.']],
            ], 422);
        }

        $score = 0;
        $results = [];

        foreach ($validated['answers'] as $answerData) {
            $question = $questions->get($answerData['questionId']);
            if (! $question) {
                continue;
            }

            $givenAnswer = $answerData['selectedChoiceId'] ?? '';
            $isCorrect = $this->checkAnswer($question, $givenAnswer);

            if ($isCorrect) {
                $score += $question->points;
            }

            $results[] = [
                'questionId' => $question->id,
                'selectedChoiceId' => $givenAnswer,
                'isCorrect' => $isCorrect,
                'points' => $question->points,
                'earnedPoints' => $isCorrect ? $question->points : 0,
            ];
        }

        $percentage = $totalPoints > 0 ? round(($score / $totalPoints) * 100, 2) : 0;
        $status = $percentage >= $passingScore ? 'pass' : 'fail';

        $submission = QuizSubmission::create([
            'quiz_id' => $quiz->id,
            'student_name' => $validated['studentName'],
            'class_name' => $validated['class_name'] ?? '',
            'answers' => $results,
            'score' => $score,
            'total_points' => $totalPoints,
            'percentage' => $percentage,
            'passing_score' => $passingScore,
            'time_taken' => $validated['timeTaken'],
            'submitted_at' => now(),
            'status' => $status,
        ]);

        return response()->json(['data' => new ClassroomSubmissionResource($submission)], 201);
    }

    public function index(Request $request): ResourceCollection
    {
        $validated = $request->validate([
            'quizId' => 'required|string|exists:quizzes,id',
            'studentName' => 'required|string|max:255',
        ]);

        $submissions = QuizSubmission::where('quiz_id', $validated['quizId'])
            ->where('student_name', $validated['studentName'])
            ->orderByDesc('submitted_at')
            ->get();

        return ClassroomSubmissionResource::collection($submissions);
    }

    public function check(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'quizId' => 'required|string|exists:quizzes,id',
            'studentName' => 'required|string|max:255',
        ]);

        $hasSubmitted = QuizSubmission::where('quiz_id', $validated['quizId'])
            ->where('student_name', $validated['studentName'])
            ->exists();

        return response()->json(['hasSubmitted' => $hasSubmitted]);
    }

    private function checkAnswer(Question $question, string $givenAnswer): bool
    {
        return match ($question->question_type) {
            'multiple_choice' => $this->checkMultipleChoice($question, $givenAnswer),
            'multiple_answer' => $this->checkMultipleAnswer($question, $givenAnswer),
            'true_false' => $this->checkTrueFalse($question, $givenAnswer),
            'short_answer' => $this->checkShortAnswer($question, $givenAnswer),
            default => false,
        };
    }

    private function checkMultipleChoice(Question $question, string $givenAnswer): bool
    {
        if (empty($givenAnswer)) {
            return false;
        }

        $correctAnswer = trim($question->correct_answer ?? '');
        if (! empty($correctAnswer)) {
            return strtolower(trim($givenAnswer)) === strtolower($correctAnswer);
        }

        // Fallback: check against choices with is_correct flag
        $choices = $question->choices ?? [];
        foreach ($choices as $choice) {
            if (($choice['is_correct'] ?? false) && strtolower(trim($choice['id'] ?? '')) === strtolower(trim($givenAnswer))) {
                return true;
            }
        }

        return false;
    }

    private function checkMultipleAnswer(Question $question, string $givenAnswer): bool
    {
        if (empty($givenAnswer)) {
            return false;
        }

        // Given answer is comma-separated (e.g., "a,b,c")
        $givenAnswers = array_map('trim', explode(',', $givenAnswer));
        $correctIds = [];

        $choices = $question->choices ?? [];
        foreach ($choices as $choice) {
            if ($choice['is_correct'] ?? false) {
                $correctIds[] = trim($choice['id'] ?? '');
            }
        }

        sort($givenAnswers);
        sort($correctIds);

        return $givenAnswers === $correctIds;
    }

    private function checkTrueFalse(Question $question, string $givenAnswer): bool
    {
        if (empty($givenAnswer)) {
            return false;
        }

        $correctAnswer = trim($question->correct_answer ?? '');
        if (! empty($correctAnswer)) {
            return strtolower(trim($givenAnswer)) === strtolower($correctAnswer);
        }

        // Fallback: check choices
        $choices = $question->choices ?? [];
        foreach ($choices as $choice) {
            if (($choice['is_correct'] ?? false) && strtolower(trim($choice['id'] ?? '')) === strtolower(trim($givenAnswer))) {
                return true;
            }
        }

        return false;
    }

    private function checkShortAnswer(Question $question, string $givenAnswer): bool
    {
        if (empty($givenAnswer)) {
            return false;
        }

        $correctAnswer = trim($question->correct_answer ?? '');
        if (empty($correctAnswer)) {
            return false;
        }

        return strcasecmp(trim($givenAnswer), $correctAnswer) === 0;
    }
}
