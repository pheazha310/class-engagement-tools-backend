<?php

namespace App\Http\Resources;

use App\Models\Poll;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Poll */
class PollResultResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $options = $this->options->map(function ($option) {
            return [
                'id' => $option->id,
                'option_text' => $option->option_text,
                'display_order' => $option->display_order,
                'votes' => $option->votes->count(),
            ];
        });

        $totalVotes = $this->votes()->count();

        return [
            'question' => $this->question,
            'status' => $this->status,
            'total_votes' => $totalVotes,
            'anonymous' => $this->anonymous,
            'options' => $options,
        ];
    }
}
