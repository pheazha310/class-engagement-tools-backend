<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePollRequest;
use App\Http\Requests\UpdatePollRequest;
use App\Http\Resources\PollResource;
use App\Http\Resources\PollResultResource;
use App\Models\Poll;
use App\Models\PollOption;
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

    public function store(StorePollRequest $request): JsonResponse
    {
        $data = $request->validated();

        $poll = Poll::create([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'question' => $data['question'],
            'poll_type' => $data['poll_type'],
            'duration_minutes' => $data['duration_minutes'] ?? null,
            'allow_multiple_votes' => $data['allow_multiple_votes'] ?? false,
            'anonymous' => $data['anonymous'] ?? true,
            'show_results' => $data['show_results'] ?? true,
            'created_by' => $request->user()->id,
        ]);

        foreach ($data['options'] as $order => $optionText) {
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

    public function update(UpdatePollRequest $request, Poll $poll): JsonResponse
    {
        $data = $request->validated();

        $poll->update([
            'title' => $data['title'] ?? $poll->title,
            'description' => array_key_exists('description', $data) ? $data['description'] : $poll->description,
            'question' => $data['question'] ?? $poll->question,
            'poll_type' => $data['poll_type'] ?? $poll->poll_type,
            'duration_minutes' => array_key_exists('duration_minutes', $data) ? $data['duration_minutes'] : $poll->duration_minutes,
            'allow_multiple_votes' => $data['allow_multiple_votes'] ?? $poll->allow_multiple_votes,
            'anonymous' => $data['anonymous'] ?? $poll->anonymous,
            'show_results' => $data['show_results'] ?? $poll->show_results,
        ]);

        if (isset($data['options'])) {
            $poll->options()->delete();
            foreach ($data['options'] as $order => $optionText) {
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
