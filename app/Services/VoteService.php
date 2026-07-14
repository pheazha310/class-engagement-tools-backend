<?php

namespace App\Services;

use App\Events\VoteUpdated;
use App\Models\Poll;
use App\Models\User;
use App\Repositories\Contracts\PollRepositoryInterface;
use App\Repositories\Contracts\VoteRepositoryInterface;

readonly class VoteService
{
    public function __construct(
        private VoteRepositoryInterface $voteRepository,
        private PollRepositoryInterface $pollRepository,
    ) {}

    public function vote(Poll $poll, ?int $optionId, User|string|null $voter, ?int $points = null, ?string $textResponse = null, ?string $voterToken = null): array
    {
        $data = [
            'poll_id' => $poll->id,
            'option_id' => $optionId,
            'points' => $points ?? ($poll->max_points ? 1 : 1),
            'text_response' => $textResponse,
        ];

        if ($voter instanceof User) {
            $data['student_id'] = $voter->id;
            $data['voter_token'] = 'user_'.$voter->id;
        } elseif (is_string($voterToken)) {
            $data['voter_token'] = $voterToken;
        }

        $this->voteRepository->create($data);

        $results = $this->pollRepository->getResults($poll);

        broadcast(new VoteUpdated(
            pollId: $poll->id,
            totalVotes: $results['totalVotes'],
            results: $results['results'],
        ))->toOthers();

        return $results;
    }

    public function hasVoted(Poll $poll, User|string|null $voter): bool
    {
        return $this->voteRepository->hasVoted($poll, $voter);
    }
}
