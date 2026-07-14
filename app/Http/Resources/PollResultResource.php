<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PollResultResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var array $data */
        $data = $this->resource;

        return [
            'question' => $data['question'] ?? '',
            'status' => $data['status'] ?? '',
            'totalVotes' => $data['totalVotes'] ?? 0,
            'totalPoints' => $data['totalPoints'] ?? null,
            'is_anonymous' => $data['is_anonymous'] ?? false,
            'is_quiz' => $data['is_quiz'] ?? false,
            'is_open_text' => $data['is_open_text'] ?? false,
            'max_points' => $data['max_points'] ?? null,
            'has_weights' => $data['has_weights'] ?? false,
            'results' => $data['results'] ?? [],
            'quiz_summary' => $data['quiz_summary'] ?? null,
        ];
    }
}
