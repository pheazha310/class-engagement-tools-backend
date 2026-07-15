<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GameAnswerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'game_session_id' => $this->game_session_id,
            'question_id' => $this->question_id,
            'submitted_answer' => $this->submitted_answer,
            'is_correct' => $this->is_correct,
            'points_awarded' => $this->points_awarded,
            'user_id' => $this->user_id,
            'participant_name' => $this->participant_name,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
