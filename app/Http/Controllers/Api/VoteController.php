<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\VoteRequest;
use App\Http\Resources\PollResultResource;
use App\Models\Poll;
use App\Services\VoteService;

class VoteController extends Controller
{
    public function __construct(
        private readonly VoteService $voteService,
    ) {}

    public function vote(VoteRequest $request, Poll $poll): PollResultResource
    {
        $user = $request->user();
        $optionId = $request->input('option_id') ? (int) $request->input('option_id') : null;

        $results = $this->voteService->vote(
            $poll,
            $optionId,
            $user,
            $request->input('points') ? (int) $request->input('points') : null,
            $request->input('text_response'),
            $request->input('voter_token'),
        );

        return new PollResultResource($results);
    }
}
