<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GameHistoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'game_session_id' => $this->game_session_id,
            'teacher_id' => $this->teacher_id,
            'game_type' => $this->game_type,
            'settings' => $this->settings,
            'participants' => $this->participants,
            'scores' => $this->scores,
            'total_questions' => $this->total_questions,
            'started_at' => $this->started_at?->toISOString(),
            'ended_at' => $this->ended_at?->toISOString(),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }
}
