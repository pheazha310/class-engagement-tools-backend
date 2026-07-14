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

    public function vote(Poll $poll, ?int $optionId, User $student, ?int $points = null, ?string $textResponse = null): array
    {
        $this->voteRepository->create([
            'poll_id' => $poll->id,
            'option_id' => $optionId,
            'student_id' => $student->id,
            'points' => $points ?? ($poll->max_points ? 1 : 1),
            'text_response' => $textResponse,
        ]);

        $results = $this->pollRepository->getResults($poll);

        broadcast(new VoteUpdated(
            pollId: $poll->id,
            totalVotes: $results['totalVotes'],
            results: $results['results'],
        ))->toOthers();

        return $results;
    }

    public function hasVoted(Poll $poll, User $student): bool
    {
        return $this->voteRepository->hasVoted($poll, $student);
    }
}
