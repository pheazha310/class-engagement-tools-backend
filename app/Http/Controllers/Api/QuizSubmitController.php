<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\QuizSubmitResource;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\QuizSubmission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QuizSubmitController extends Controller
{
    public function __invoke(Request $request, Quiz $quiz): JsonResponse
    {
        $validated = $request->validate([
            'student_name' => 'required|string|max:255',
            'answers' => 'required|array|min:1',
            'answers.*.question_id' => 'required|string|exists:questions,id',
            'answers.*.answer' => 'required|string',
            'time_taken' => 'required|integer|min:0',
            'class_name' => 'nullable|string|max:255',
        ]);

        // Load all questions for this quiz
        $questions = $quiz->questions()->get()->keyBy('id');
        $totalPoints = $questions->sum('points');
        $totalQuestions = $questions->count();

        if ($totalPoints === 0) {
            return response()->json([
                'message' => 'Quiz has no questions with points assigned.',
                'errors' => ['quiz' => ['This quiz has no questions or no points configured.']],
            ], 422);
        }

        $score = 0;
        $correctCount = 0;
        $incorrectCount = 0;
        $answeredIds = [];
        $results = [];

        foreach ($validated['answers'] as $answerData) {
            $question = $questions->get($answerData['question_id']);

            if (! $question) {
                continue;
            }

            $answeredIds[] = $question->id;
            $isCorrect = $this->checkAnswer($question, $answerData['answer']);

            $results[] = [
                'question_id' => $question->id,
                'question_text' => $question->question_text,
                'question_type' => $question->question_type,
                'given_answer' => $answerData['answer'],
                'correct_answer' => $question->correct_answer,
                'points' => $question->points,
                'earned_points' => $isCorrect ? $question->points : 0,
                'is_correct' => $isCorrect,
            ];

            if ($isCorrect) {
                $score += $question->points;
                $correctCount++;
            } else {
                $incorrectCount++;
            }
        }

        // Count unanswered questions as incorrect
        $unansweredCount = $totalQuestions - count($results);
        $incorrectCount += $unansweredCount;

        $percentage = $totalPoints > 0 ? round(($score / $totalPoints) * 100, 2) : 0;
        $passingScore = $quiz->passing_score ?? 50;
        $status = $percentage >= $passingScore ? 'pass' : 'fail';

        // Save submission
        $submission = QuizSubmission::create([
            'quiz_id' => $quiz->id,
            'student_name' => $validated['student_name'],
            'score' => $score,
            'percentage' => $percentage,
            'time_taken' => $validated['time_taken'],
            'submitted_at' => now(),
            'status' => $status,
            'class_name' => $validated['class_name'] ?? null,
        ]);

        return response()->json([
            'data' => new QuizSubmitResource($submission, [
                'total_points' => $totalPoints,
                'correct_count' => $correctCount,
                'incorrect_count' => $incorrectCount,
                'total_questions' => $totalQuestions,
            ]),
            'included' => [
                'results' => $results,
            ],
        ], 201);
    }

    private function checkAnswer(Question $question, string $givenAnswer): bool
    {
        $correctAnswer = trim($question->correct_answer ?? '');

        return match ($question->question_type) {
            'multiple_choice' => $this->checkMultipleChoice($question, $givenAnswer),
            'true_false' => strtolower(trim($givenAnswer)) === strtolower($correctAnswer),
            'short_answer' => $this->checkShortAnswer($correctAnswer, $givenAnswer),
            default => false,
        };
    }

    private function checkMultipleChoice(Question $question, string $givenAnswer): bool
    {
        $givenAnswer = trim($givenAnswer);

        // First, check against correct_answer field
        $correctAnswer = trim($question->correct_answer ?? '');
        if (! empty($correctAnswer)) {
            return strtolower($givenAnswer) === strtolower($correctAnswer);
        }

        // Fallback: check against choices array
        $choices = $question->choices ?? [];
        foreach ($choices as $choice) {
            if (($choice['is_correct'] ?? false) && strtolower(trim($choice['choice_text'] ?? '')) === strtolower($givenAnswer)) {
                return true;
            }
        }

        return false;
    }

    private function checkShortAnswer(string $correctAnswer, string $givenAnswer): bool
    {
        $correctAnswer = trim($correctAnswer);
        $givenAnswer = trim($givenAnswer);

        if (empty($correctAnswer)) {
            return false;
        }

        return strcasecmp($correctAnswer, $givenAnswer) === 0;
    }
}
