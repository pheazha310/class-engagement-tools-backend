<?php

namespace App\Repositories\Contracts;

use App\Models\Poll;
use App\Models\User;
use App\Models\Vote;

interface VoteRepositoryInterface
{
    public function hasVoted(Poll $poll, User $student): bool;

    public function create(array $data): Vote;

    public function countByOption(Poll $poll): array;

    public function totalVotes(Poll $poll): int;
}
