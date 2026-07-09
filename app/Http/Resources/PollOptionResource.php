<?php

namespace App\Http\Resources;

use App\Models\PollOption;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin PollOption */
class PollOptionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'option_text' => $this->option_text,
            'created_at' => $this->created_at->toISOString(),
        ];
    }
}
