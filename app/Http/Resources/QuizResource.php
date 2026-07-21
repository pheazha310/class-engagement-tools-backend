<?php

namespace App\Http\Resources;

use App\Models\Quiz;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Quiz */
class QuizResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'teacher_id' => $this->teacher_id,
            'title' => $this->title,
            'description' => $this->description,
            'subject' => $this->subject,
            'class_name' => $this->class_name,
            'duration' => $this->duration,
            'passing_score' => $this->passing_score,
            'due_date' => $this->due_date->format('Y-m-d\TH:i'),
            'shuffle_questions' => $this->shuffle_questions,
            'status' => $this->status,
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }
}
