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
            'status' => $this->status,
            'started_at' => $this->started_at?->toISOString(),
            'ended_at' => $this->ended_at?->toISOString(),
            'options' => PollOptionResource::collection($this->whenLoaded('options')),
            'total_votes' => $this->when($this->relationLoaded('votes'), fn () => $this->votes->count()),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }
}
