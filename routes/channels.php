<?php

use App\Models\Poll;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('poll.{pollId}', function (mixed $user, int $pollId) {
    $poll = Poll::find($pollId);

    if (! $poll) {
        return false;
    }

    return $poll->teacher_id === $user->id || $user->isStudent();
});
