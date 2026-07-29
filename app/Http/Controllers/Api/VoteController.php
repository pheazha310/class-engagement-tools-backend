<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\VoteRequest;
use App\Models\Poll;
use App\Models\PollOption;
use App\Models\Vote;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
        $guestToken = $request->validated()['guest_token'];

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

    /**
     * Vote on a poll by poll ID (with voter_token for guest users).
     * Used by the frontend's old poll system (PollForm, VotePage).
     */
    public function voteByPollId(Request $request, Poll $poll): JsonResponse
    {
        if (! $poll->isActive()) {
            return response()->json(['message' => 'This poll is not currently active, or has ended.'], 422);
        }

        $validated = $request->validate([
            'option_id' => ['nullable', 'exists:poll_options,id'],
            'voter_token' => ['required', 'string'],
            'points' => ['nullable', 'integer', 'min:1', 'max:100'],
            'text_response' => ['nullable', 'string', 'max:1000'],
        ]);

        $optionId = $validated['option_id'] ?? null;
        $voterToken = $validated['voter_token'];
        $textResponse = $validated['text_response'] ?? null;
        $points = $validated['points'] ?? null;

        // If not open text, must have option_id
        if (! $poll->isOpenText() && ! $optionId) {
            return response()->json(['message' => 'An option must be selected.'], 422);
        }

        // If option provided, verify it belongs to this poll
        if ($optionId) {
            $option = PollOption::find($optionId);
            if (! $option || $option->poll_id !== $poll->id) {
                return response()->json(['message' => 'Invalid option for this poll.'], 422);
            }
        }

        // Check for existing vote
        $user = $request->user();
        if ($user && ! $user->isTeacher()) {
            // Authenticated non-teacher user (student) voting
            $existingVote = Vote::where('poll_id', $poll->id)
                ->where('user_id', $user->id)
                ->first();

            if ($existingVote) {
                return response()->json(['message' => 'You have already voted on this poll.'], 422);
            }
        } else {
            // Guest voting with voter_token
            $existingVote = Vote::where('poll_id', $poll->id)
                ->where('guest_token', $voterToken)
                ->first();

            if ($existingVote) {
                return response()->json(['message' => 'You have already voted on this poll.'], 422);
            }
        }

        Vote::create([
            'poll_id' => $poll->id,
            'poll_option_id' => $optionId,
            'user_id' => $user?->id,
            'guest_token' => $voterToken,
            'points' => $points,
            'text_response' => $textResponse,
        ]);

        // Return updated results
        $results = $this->buildPollResults($poll);

        return response()->json([
            'message' => 'Vote recorded successfully.',
            'data' => $results,
        ], 201);
    }

    /**
     * Check if a voter has already voted on a poll.
     */
    public function checkVote(Request $request, Poll $poll): JsonResponse
    {
        $voterToken = $request->query('voter_token');
        $user = $request->user();

        $hasVoted = false;
        if ($user && ! $user->isTeacher()) {
            $hasVoted = Vote::where('poll_id', $poll->id)
                ->where('user_id', $user->id)
                ->exists();
        } elseif ($voterToken) {
            $hasVoted = Vote::where('poll_id', $poll->id)
                ->where('guest_token', $voterToken)
                ->exists();
        }

        return response()->json(['hasVoted' => $hasVoted]);
    }

    /**
     * Build results data for a poll.
     */
    private function buildPollResults(Poll $poll): array
    {
        $poll->load('options.votes');
        $totalVotes = $poll->votes()->count();

        $results = $poll->options->map(function ($option) use ($totalVotes) {
            $votes = $option->votes->count();

            return [
                'id' => $option->id,
                'option' => $option->option_text,
                'votes' => $votes,
                'percentage' => $totalVotes > 0 ? round(($votes / $totalVotes) * 100, 1) : 0,
            ];
        });

        return [
            'question' => $poll->question,
            'status' => $poll->status,
            'totalVotes' => $totalVotes,
            'results' => $results->toArray(),
        ];
    }
}
