<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\VoteRequest;
use App\Models\Poll;
use App\Models\PollOption;
use App\Models\Vote;
use Illuminate\Http\JsonResponse;

class VoteController extends Controller
{
    public function vote(VoteRequest $request, string $token): JsonResponse
    {
        $poll = Poll::byPublicToken($token)->first();

        if (! $poll) {
            return response()->json(['message' => 'Poll not found.'], 404);
        }

        if (! $poll->isActive()) {
            return response()->json(['message' => 'This poll is not currently active.'], 422);
        }

        $option = PollOption::findOrFail($request->validated()['option_id']);

        if ($option->poll_id !== $poll->id) {
            return response()->json(['message' => 'Invalid option for this poll.'], 422);
        }

        $user = $request->user();
        $guestToken = $request->validated()['guest_token'] ?? null;

        if ($user) {
            if (! $user->isStudent()) {
                return response()->json(['message' => 'Only students can vote.'], 403);
            }

            $existingVote = Vote::where('poll_id', $poll->id)
                ->where('user_id', $user->id)
                ->first();

            if ($existingVote && ! $poll->allow_multiple_votes) {
                return response()->json(['message' => 'You have already voted on this poll.'], 422);
            }
        } elseif ($guestToken) {
            $existingVote = Vote::where('poll_id', $poll->id)
                ->where('guest_token', $guestToken)
                ->first();

            if ($existingVote && ! $poll->allow_multiple_votes) {
                return response()->json(['message' => 'You have already voted on this poll.'], 422);
            }
        } else {
            return response()->json(['message' => 'Authentication or guest token is required to vote.'], 422);
        }

        Vote::create([
            'poll_id' => $poll->id,
            'poll_option_id' => $option->id,
            'user_id' => $user?->id,
            'guest_token' => $guestToken,
        ]);

        return response()->json([
            'message' => 'Vote recorded successfully.',
        ], 201);
    }
}
