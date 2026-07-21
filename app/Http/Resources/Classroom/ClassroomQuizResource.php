<?php

namespace App\Http\Resources\Classroom;

use App\Models\Quiz;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Quiz */
class ClassroomQuizResource extends JsonResource
{
    /** Whether to include the full questions list */
    private bool $withQuestions = false;

    public function withQuestions(bool $withQuestions = true): static
    {
        $this->withQuestions = $withQuestions;

        return $this;
    }

    public function toArray(Request $request): array
    {
        $data = [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description ?? '',
            'subject' => $this->subject ?? '',
            'class_name' => $this->class_name ?? '',
            'duration' => (int) $this->duration,
            'passing_score' => (int) $this->passing_score,
            'shuffle_questions' => (bool) $this->shuffle_questions,
            'status' => $this->status ?? 'published',
            'questions_count' => $this->when($this->questions_count !== null, $this->questions_count),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];

        if ($this->withQuestions) {
            $data['questions'] = ClassroomQuestionResource::collection(
                $this->relationLoaded('questions') ? $this->questions : $this->whenLoaded('questions')
            );
        }

        return $data;
    }
}
