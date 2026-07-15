<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\QuizSubmissionResource;
use App\Models\Quiz;
use App\Models\QuizSubmission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class QuizRankingController extends Controller
{
    public function index(Request $request, Quiz $quiz): ResourceCollection|JsonResponse
    {
        $query = QuizSubmission::where('quiz_id', $quiz->id);

        // Filter by search (student name)
        if ($search = $request->input('search')) {
            $query->where('student_name', 'like', "%{$search}%");
        }

        // Filter by class name
        if ($class_name = $request->input('class_name')) {
            $query->where('class_name', $class_name);
        }

        // Filter by status (pass/fail)
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        // Sorting
        $sort_by = $request->input('sort_by', 'highest_score');
        $query = match ($sort_by) {
            'lowest_score' => $query->orderBy('percentage'),
            default => $query->orderByDesc('percentage'),
        };

        $submissions = $query->get();

        return QuizSubmissionResource::collection($submissions);
    }
}
