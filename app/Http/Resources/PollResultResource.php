<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PollResultResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var array{question: string, status: string, totalVotes: int, results: array} $data */
        $data = $this->resource;

        return [
            'question' => $data['question'],
            'status' => $data['status'],
            'totalVotes' => $data['totalVotes'],
            'results' => $data['results'],
        ];
    }
}
