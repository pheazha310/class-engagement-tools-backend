<?php

namespace App\Http\Resources;

use App\Models\SoundPlayHistory;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin SoundPlayHistory */
class SoundPlayHistoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'sound_id' => $this->sound_id,
            'sound_name' => $this->sound_name,
            'audio_url' => $this->audio_url,
            'sound_category' => $this->sound_category,
            'icon' => $this->icon,
            'duration_seconds' => $this->duration_seconds,
            'played_by' => $this->when($this->relationLoaded('player'), fn () => $this->player
                ? ['id' => $this->player->id, 'name' => $this->player->name]
                : null
            ),
            'played_at' => $this->played_at->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
