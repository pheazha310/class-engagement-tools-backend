<?php

use App\Models\GameSession;
use App\Models\Poll;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('poll.{pollId}', function (mixed $user, string $pollId) {
    $poll = Poll::find($pollId);

    if (! $poll) {
        return false;
    }

    return $poll->created_by === $user->id || $user->isStudent();
});

Broadcast::channel('game-session.{gameSessionId}', function (mixed $user, string $gameSessionId) {
    $session = GameSession::find($gameSessionId);

    if (! $session) {
        return false;
    }

    if ($user) {
        return $session->teacher_id === $user->id || $user->isStudent();
    }

    return true;
});
