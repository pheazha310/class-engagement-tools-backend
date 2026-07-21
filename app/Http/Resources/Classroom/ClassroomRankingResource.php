<?php

namespace App\Http\Resources\Classroom;

use App\Models\QuizSubmission;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin QuizSubmission */
class ClassroomRankingResource extends JsonResource
{
    public function __construct(mixed $resource, private int $rank = 0)
    {
        parent::__construct($resource);
    }

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'quiz_id' => $this->quiz_id,
            'student_name' => $this->student_name,
            'score' => (int) $this->score,
            'percentage' => (float) $this->percentage,
            'time_taken' => (int) $this->time_taken,
            'submitted_at' => $this->submitted_at?->toISOString(),
            'status' => $this->status,
            'class_name' => $this->class_name ?? '',
            'rank' => $this->rank,
        ];
    }
}
