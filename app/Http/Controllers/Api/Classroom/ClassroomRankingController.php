<?php

namespace App\Http\Controllers\Api\Classroom;

use App\Http\Controllers\Controller;
use App\Http\Resources\Classroom\ClassroomRankingResource;
use App\Models\Quiz;
use App\Models\QuizSubmission;
use Illuminate\Http\JsonResponse;

class ClassroomRankingController extends Controller
{
    public function index(string $quizId): JsonResponse
    {
        $quiz = Quiz::findOrFail($quizId);

        // Get all submissions for this quiz
        $submissions = QuizSubmission::where('quiz_id', $quiz->id)
            ->orderByDesc('score')
            ->orderBy('time_taken')
            ->get();

        // Group by student_name and take the best submission per student
        $bestSubmissions = $submissions->groupBy('student_name')->map(function ($studentSubmissions) {
            // Best submission = highest score, then fastest time
            return $studentSubmissions->sort(function ($a, $b) {
                // Higher score first
                if ($a->score !== $b->score) {
                    return $b->score - $a->score;
                }

                // If same score, lower time first
                return $a->time_taken - $b->time_taken;
            })->first();
        });

        // Sort best submissions by score descending, then time ascending
        $sorted = $bestSubmissions->sort(function ($a, $b) {
            if ($a->score !== $b->score) {
                return $b->score - $a->score;
            }

            return $a->time_taken - $b->time_taken;
        })->values();

        // Assign ranks
        $ranked = $sorted->map(function ($submission, $index) {
            return new ClassroomRankingResource($submission, $index + 1);
        });

        return response()->json([
            'quizId' => $quiz->id,
            'rankings' => ClassroomRankingResource::collection($ranked),
        ]);
    }
}
