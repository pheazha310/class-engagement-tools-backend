<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PollResource;
use App\Models\Poll;
use App\Models\PollOption;
use App\Models\Vote;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PollController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection|JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $polls = Poll::byCreator($user->id)
            ->with('options')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return PollResource::collection($polls);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        if (! $user->isTeacher()) {
            return response()->json(['message' => 'Only teachers can create polls.'], 403);
        }

        $validated = $request->validate([
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'question' => ['required', 'string', 'max:1000'],
            'poll_type' => ['nullable', 'string', 'in:multiple_choice,single_choice,yes_no,true_false,rating,open_text'],
            'options' => ['nullable', 'array', 'min:2', 'max:20'],
            'options.*' => ['required', 'string', 'max:255'],
            'duration_minutes' => ['nullable', 'integer', 'min:1', 'max:1440'],
            'allow_multiple_votes' => ['boolean'],
            'anonymous' => ['boolean'],
            'show_results' => ['boolean'],
            // Frontend compatibility fields
            'is_multiple_choice' => ['boolean'],
            'is_anonymous' => ['boolean'],
            'is_quiz' => ['boolean'],
            'is_open_text' => ['boolean'],
            'max_points' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        // Determine poll_type from frontend format if not directly provided
        $pollType = $validated['poll_type'] ?? null;
        if (! $pollType) {
            if (! empty($validated['is_open_text'])) {
                $pollType = 'open_text';
            } elseif (! empty($validated['is_multiple_choice'])) {
                $pollType = 'multiple_choice';
            } else {
                $pollType = 'single_choice';
            }
        }

        // Use question as title if title not provided
        $title = $validated['title'] ?? $validated['question'];

        // If options is empty but is_open_text, allow it
        $options = $validated['options'] ?? [];
        if (empty($options) && $pollType === 'open_text') {
            $options = [];
        }

        $poll = Poll::create([
            'title' => $title,
            'description' => $validated['description'] ?? null,
            'question' => $validated['question'],
            'poll_type' => $pollType,
            'duration_minutes' => $validated['duration_minutes'] ?? null,
            'allow_multiple_votes' => $validated['allow_multiple_votes'] ?? false,
            'anonymous' => $validated['anonymous'] ?? ($validated['is_anonymous'] ?? true),
            'show_results' => $validated['show_results'] ?? true,
            'created_by' => $user->id,
        ]);

        foreach ($options as $order => $optionText) {
            PollOption::create([
                'poll_id' => $poll->id,
                'option_text' => $optionText,
                'display_order' => $order,
            ]);
        }

        $poll->load('options');

        return response()->json([
            'message' => 'Poll created successfully.',
            'data' => new PollResource($poll),
            'poll' => new PollResource($poll),
        ], 201);
    }

    public function show(Request $request, Poll $poll): JsonResponse
    {
        // Allow public access to active polls (for voting)
        if ($poll->created_by !== $request->user()?->id) {
            if (! $poll->isActive()) {
                return response()->json(['message' => 'Poll not found.'], 404);
            }
        }

        $poll->load('options');

        return response()->json([
            'data' => new PollResource($poll),
            'poll' => new PollResource($poll),
        ]);
    }

    public function update(Request $request, Poll $poll): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'question' => ['sometimes', 'required', 'string', 'max:1000'],
            'poll_type' => ['nullable', 'string', 'in:multiple_choice,single_choice,yes_no,true_false,rating,open_text'],
            'options' => ['sometimes', 'array', 'min:2', 'max:20'],
            'options.*' => ['required', 'string', 'max:255'],
            'duration_minutes' => ['nullable', 'integer', 'min:1', 'max:1440'],
            'allow_multiple_votes' => ['boolean'],
            'anonymous' => ['boolean'],
            'show_results' => ['boolean'],
        ]);

        $poll->update([
            'title' => $validated['title'] ?? $poll->title,
            'description' => array_key_exists('description', $validated) ? $validated['description'] : $poll->description,
            'question' => $validated['question'] ?? $poll->question,
            'poll_type' => $validated['poll_type'] ?? $poll->poll_type,
            'duration_minutes' => array_key_exists('duration_minutes', $validated) ? $validated['duration_minutes'] : $poll->duration_minutes,
            'allow_multiple_votes' => $validated['allow_multiple_votes'] ?? $poll->allow_multiple_votes,
            'anonymous' => $validated['anonymous'] ?? $poll->anonymous,
            'show_results' => $validated['show_results'] ?? $poll->show_results,
        ]);

        if (isset($validated['options'])) {
            $poll->options()->delete();
            foreach ($validated['options'] as $order => $optionText) {
                PollOption::create([
                    'poll_id' => $poll->id,
                    'option_text' => $optionText,
                    'display_order' => $order,
                ]);
            }
        }

        $poll->load('options');

        return response()->json([
            'message' => 'Poll updated successfully.',
            'poll' => new PollResource($poll),
        ]);
    }

    public function destroy(Request $request, Poll $poll): JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        if ($poll->created_by !== $user->id) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        if (! $poll->isDraft()) {
            return response()->json(['message' => 'Only draft polls can be deleted.'], 422);
        }

        $poll->delete();

        return response()->json(['message' => 'Poll deleted successfully.']);
    }

    public function start(Request $request, Poll $poll): JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        if ($poll->created_by !== $user->id) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        if (! $poll->isDraft()) {
            return response()->json(['message' => 'Only draft polls can be started.'], 422);
        }

        $poll->update([
            'status' => 'active',
            'started_at' => now(),
        ]);

        $poll->load('options');

        return response()->json([
            'message' => 'Poll started successfully.',
            'poll' => new PollResource($poll),
        ]);
    }

    public function end(Request $request, Poll $poll): JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        if ($poll->created_by !== $user->id) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        if (! $poll->isActive()) {
            return response()->json(['message' => 'Only active polls can be ended.'], 422);
        }

        $poll->update([
            'status' => 'closed',
            'ended_at' => now(),
        ]);

        $poll->load('options');

        return response()->json([
            'message' => 'Poll ended successfully.',
            'poll' => new PollResource($poll),
        ]);
    }

    public function activePolls(Request $request): JsonResponse
    {
        $polls = Poll::active()
            ->select(['id', 'question', 'title', 'poll_type', 'public_token', 'duration_minutes', 'started_at', 'anonymous', 'allow_multiple_votes', 'created_at'])
            ->withCount('options')
            ->orderBy('created_at', 'desc')
            ->get();

        // If a single poll exists and voter_token is provided, return it in { poll, hasVoted } format
        $voterToken = $request->query('voter_token');
        if ($polls->count() === 1 && $voterToken) {
            $poll = $polls->first();
            $poll->load('options');
            $hasVoted = Vote::where('poll_id', $poll->id)
                ->where(function ($q) use ($voterToken) {
                    $q->where('guest_token', $voterToken)
                        ->orWhere('user_id', $voterToken);
                })
                ->exists();

            return response()->json([
                'poll' => new PollResource($poll),
                'hasVoted' => $hasVoted,
            ]);
        }

        return response()->json([
            'polls' => $polls,
        ]);
    }

    public function dashboardStats(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            return response()->json(['message' => 'Unauthorized.'], 401);
        }

        $totalPolls = Poll::byCreator($user->id)->count();
        $activePolls = Poll::byCreator($user->id)->active()->count();
        $closedPolls = Poll::byCreator($user->id)->closed()->count();
        $totalVotes = Vote::whereIn('poll_id', Poll::byCreator($user->id)->pluck('id'))->count();

        return response()->json([
            'data' => [
                'total_polls' => $totalPolls,
                'active_polls' => $activePolls,
                'closed_polls' => $closedPolls,
                'total_votes' => $totalVotes,
            ],
        ]);
    }

    public function showByToken(string $token): JsonResponse
    {
        $poll = Poll::byPublicToken($token)->with('options')->first();

        if (! $poll) {
            return response()->json(['message' => 'Poll not found.'], 404);
        }

        if (! $poll->isActive()) {
            return response()->json(['message' => 'This poll is not currently active.'], 404);
        }

        return response()->json([
            'poll' => new PollResource($poll),
        ]);
    }

    public function publicResults(string $token): JsonResponse
    {
        $poll = Poll::byPublicToken($token)->with('options.votes')->first();

        if (! $poll) {
            return response()->json(['message' => 'Poll not found.'], 404);
        }

        return response()->json([
            'results' => new PollResultResource($poll),
        ]);
    }

    public function results(Request $request, Poll $poll): JsonResponse
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

        return response()->json([
            'data' => [
                'question' => $poll->question,
                'status' => $poll->status,
                'totalVotes' => $totalVotes,
                'totalPoints' => null,
                'results' => $results->toArray(),
            ],
        ]);
    }

    public function qrCode(Request $request, Poll $poll): JsonResponse
    {
        return response()->json([
            'room_code' => $poll->public_token ? substr($poll->public_token, 0, 6) : null,
            'join_url' => url('/vote/'.$poll->public_token),
            'poll_id' => $poll->id,
        ]);
    }
}
