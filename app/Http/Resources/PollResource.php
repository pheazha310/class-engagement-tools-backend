<?php

namespace App\Http\Resources;

use App\Models\Poll;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Poll */
class PollResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'teacher_id' => $this->teacher_id,
            'question' => $this->question,
            'room_code' => $this->room_code,
            'status' => $this->status,
            'is_multiple_choice' => $this->is_multiple_choice,
            'duration_minutes' => $this->duration_minutes,
            'started_at' => $this->started_at?->toISOString(),
            'ended_at' => $this->ended_at?->toISOString(),
            'options' => PollOptionResource::collection($this->whenLoaded('options')),
            'total_votes' => $this->when($this->relationLoaded('votes'), fn () => $this->votes->count()),
            'participant_count' => $this->when($this->relationLoaded('votes'), fn () => $this->participantCount()),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }
}
