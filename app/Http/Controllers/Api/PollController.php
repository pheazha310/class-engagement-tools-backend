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
    public function index(Request $request): AnonymousResourceCollection
    {
        $polls = Poll::byCreator($request->user()->id)
            ->with('options')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return PollResource::collection($polls);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user || ! $user->isTeacher()) {
            return response()->json(['message' => 'Only teachers can create polls.'], 403);
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'question' => ['required', 'string', 'max:1000'],
            'poll_type' => ['nullable', 'string', 'in:multiple_choice,single_choice,yes_no,true_false,rating,open_text'],
            'options' => ['required', 'array', 'min:2', 'max:20'],
            'options.*' => ['required', 'string', 'max:255'],
            'duration_minutes' => ['nullable', 'integer', 'min:1', 'max:1440'],
            'allow_multiple_votes' => ['boolean'],
            'anonymous' => ['boolean'],
            'show_results' => ['boolean'],
        ]);

        $poll = Poll::create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'question' => $validated['question'],
            'poll_type' => $validated['poll_type'] ?? 'multiple_choice',
            'duration_minutes' => $validated['duration_minutes'] ?? null,
            'allow_multiple_votes' => $validated['allow_multiple_votes'] ?? false,
            'anonymous' => $validated['anonymous'] ?? true,
            'show_results' => $validated['show_results'] ?? true,
            'created_by' => $request->user()->id,
        ]);

        foreach ($validated['options'] as $order => $optionText) {
            PollOption::create([
                'poll_id' => $poll->id,
                'option_text' => $optionText,
                'display_order' => $order,
            ]);
        }

        $poll->load('options');

        return response()->json([
            'message' => 'Poll created successfully.',
            'poll' => new PollResource($poll),
        ], 201);
    }

    public function show(Request $request, Poll $poll): JsonResponse
    {
        if ($poll->created_by !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $poll->load('options');

        return response()->json([
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
        if ($poll->created_by !== $request->user()->id) {
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
        if ($poll->created_by !== $request->user()->id) {
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
        if ($poll->created_by !== $request->user()->id) {
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

    public function activePolls(): JsonResponse
    {
        $polls = Poll::active()
            ->select(['id', 'question', 'title', 'poll_type', 'public_token', 'duration_minutes', 'started_at', 'anonymous', 'allow_multiple_votes', 'created_at'])
            ->withCount('options')
            ->orderBy('created_at', 'desc')
            ->get();

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
}
