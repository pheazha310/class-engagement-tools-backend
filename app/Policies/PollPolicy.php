<?php

namespace App\Policies;

use App\Models\Poll;
use App\Models\User;

class PollPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isTeacher();
    }

    public function view(User $user, Poll $poll): bool
    {
        return $user->isTeacher() && $poll->teacher_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->isTeacher();
    }

    public function update(User $user, Poll $poll): bool
    {
        return $user->isTeacher()
            && $poll->teacher_id === $user->id
            && $poll->isDraft();
    }

    public function delete(User $user, Poll $poll): bool
    {
        return $user->isTeacher()
            && $poll->teacher_id === $user->id
            && $poll->isDraft();
    }

    public function start(User $user, Poll $poll): bool
    {
        return $user->isTeacher() && $poll->teacher_id === $user->id;
    }

    public function end(User $user, Poll $poll): bool
    {
        return $user->isTeacher() && $poll->teacher_id === $user->id;
    }

    public function vote(User $user, Poll $poll): bool
    {
        return $user->isStudent() && $poll->isActive();
    }

    public function viewResults(User $user, Poll $poll): bool
    {
        return true;
    }
}
