<?php

namespace App\Http\Resources;

use App\Models\QuizSubmission;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin QuizSubmission */
class QuizSubmissionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'quiz_id' => $this->quiz_id,
            'student_name' => $this->student_name,
            'score' => $this->score,
            'percentage' => (float) $this->percentage,
            'time_taken' => $this->time_taken,
            'submitted_at' => $this->submitted_at?->format('Y-m-d\TH:i:s\Z'),
            'status' => $this->status,
            'class_name' => $this->class_name ?? '',
        ];
    }
}
