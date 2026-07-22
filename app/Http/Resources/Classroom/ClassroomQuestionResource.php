<?php

namespace App\Http\Resources\Classroom;

use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Question */
class ClassroomQuestionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'question_text' => $this->question_text,
            'question_type' => $this->question_type,
            'points' => (int) $this->points,
            'choices' => $this->choices ?? [],
            'correct_answer' => $this->correct_answer ?? '',
            'order' => (int) $this->order,
        ];
    }
}
