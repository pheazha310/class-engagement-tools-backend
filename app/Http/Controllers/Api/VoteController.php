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
        $results = $this->voteService->vote(
            $poll,
            (int) $request->input('option_id'),
            $request->user(),
        );

        return new PollResultResource($results);
    }
}
