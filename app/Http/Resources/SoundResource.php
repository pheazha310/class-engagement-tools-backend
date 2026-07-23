<?php

namespace App\Http\Resources;

use App\Models\Sound;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Sound */
class SoundResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'audio_url' => $this->audio_url,
            'category' => $this->category,
            'icon' => $this->icon,
            'duration_seconds' => $this->duration_seconds,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
