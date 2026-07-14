<?php

namespace App\Repositories;

use App\Models\Poll;
use App\Models\User;
use App\Models\Vote;
use App\Repositories\Contracts\VoteRepositoryInterface;

class VoteRepository implements VoteRepositoryInterface
{
    public function hasVoted(Poll $poll, User|string|null $voter): bool
    {
        $query = Vote::query()->where('poll_id', $poll->id);

        if ($voter instanceof User) {
            $query->where('student_id', $voter->id);
        } elseif (is_string($voter)) {
            $query->where('voter_token', $voter);
        } else {
            return false;
        }

        return $query->exists();
    }

    public function create(array $data): Vote
    {
        return Vote::query()->create($data);
    }

    public function countByOption(Poll $poll): array
    {
        return Vote::query()
            ->where('poll_id', $poll->id)
            ->whereNotNull('option_id')
            ->groupBy('option_id')
            ->selectRaw('option_id, count(*) as count')
            ->pluck('count', 'option_id')
            ->toArray();
    }

    public function totalVotes(Poll $poll): int
    {
        return Vote::query()->where('poll_id', $poll->id)->count();
    }
}
