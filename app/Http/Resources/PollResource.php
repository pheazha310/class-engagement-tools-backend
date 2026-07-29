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
            'title' => $this->title,
            'description' => $this->description,
            'question' => $this->question,
            'poll_type' => $this->poll_type,
            'status' => $this->status,
            'duration_minutes' => $this->duration_minutes,
            'allow_multiple_votes' => $this->allow_multiple_votes,
            'anonymous' => $this->anonymous,
            'show_results' => $this->show_results,
            'teacher_id' => $this->created_by,
            'share_token' => $this->public_token,
            'public_token' => $this->public_token,
            'room_code' => $this->room_code,
            'join_url' => url("/vote/{$this->public_token}"),
            'created_by' => $this->created_by,
            'started_at' => $this->started_at?->toISOString(),
            'ended_at' => $this->ended_at?->toISOString(),
            'options' => PollOptionResource::collection($this->whenLoaded('options')),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }
}
