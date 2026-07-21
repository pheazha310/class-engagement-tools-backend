<?php

namespace App\Http\Resources;

use App\Models\QuizSubmission;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin QuizSubmission */
class QuizSubmitResource extends JsonResource
{
    /**
     * @param  array{total_points: int, correct_count: int, incorrect_count: int, total_questions: int}  $summary
     */
    public function __construct(mixed $resource, private array $summary = [])
    {
        parent::__construct($resource);
    }

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'quiz_id' => $this->quiz_id,
            'student_name' => $this->student_name,
            'score' => $this->score,
            'total_points' => $this->summary['total_points'] ?? 0,
            'percentage' => (float) $this->percentage,
            'time_taken' => $this->time_taken,
            'submitted_at' => $this->submitted_at?->format('Y-m-d\TH:i:s\Z'),
            'status' => $this->status,
            'class_name' => $this->class_name ?? '',
            'summary' => [
                'correct_count' => $this->summary['correct_count'] ?? 0,
                'incorrect_count' => $this->summary['incorrect_count'] ?? 0,
                'total_questions' => $this->summary['total_questions'] ?? 0,
            ],
        ];
    }
}
