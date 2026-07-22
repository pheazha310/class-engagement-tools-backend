<?php

namespace App\Http\Resources\Classroom;

use App\Models\QuizSubmission;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin QuizSubmission */
class ClassroomSubmissionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'quizId' => $this->quiz_id,
            'studentName' => $this->student_name,
            'class_name' => $this->class_name ?? '',
            'answers' => $this->answers ?? [],
            'score' => (int) $this->score,
            'totalPoints' => (int) $this->total_points,
            'percentage' => (float) $this->percentage,
            'timeTaken' => (int) $this->time_taken,
            'submittedAt' => $this->submitted_at?->toISOString(),
            'status' => $this->status,
            'passingScore' => (int) ($this->passing_score ?: 50),
        ];
    }
}
