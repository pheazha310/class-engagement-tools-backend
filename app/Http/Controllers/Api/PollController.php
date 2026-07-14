<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePollRequest;
use App\Http\Requests\UpdatePollRequest;
use App\Http\Resources\PollResource;
use App\Http\Resources\PollResultResource;
use App\Models\Poll;
use App\Services\PollService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\URL;

class PollController extends Controller
{
    public function __construct(
        private readonly PollService $pollService,
    ) {}

    public function index(Request $request): ResourceCollection
    {
        $polls = $this->pollService->getTeacherPolls(
            $request->user(),
            (int) $request->input('per_page', 10),
        );

        return PollResource::collection($polls);
    }

    public function show(Poll $poll): PollResource
    {
        $poll->load('options');

        return new PollResource($poll);
    }

    public function joinByCode(Request $request): JsonResponse
    {
        $request->validate(['room_code' => ['required', 'string', 'size:6']]);

        $poll = $this->pollService->findByRoomCode($request->input('room_code'));

        if (! $poll || ! $poll->isActive()) {
            return response()->json(['message' => 'Invalid or inactive room code.'], 404);
        }

        $hasVoted = auth()->check()
            ? $poll->votes()->where('student_id', auth()->id())->exists()
            : false;

        return response()->json([
            'poll' => new PollResource($poll),
            'hasVoted' => $hasVoted,
        ]);
    }

    public function store(StorePollRequest $request): PollResource
    {
        $poll = $this->pollService->create(
            $request->validated(),
            $request->user(),
        );

        return new PollResource($poll);
    }

    public function update(UpdatePollRequest $request, Poll $poll): PollResource
    {
        $poll = $this->pollService->update($poll, $request->validated());

        return new PollResource($poll);
    }

    public function destroy(Request $request, Poll $poll): JsonResponse
    {
        if ($poll->teacher_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $this->pollService->delete($poll);

        return response()->json(['message' => 'Poll deleted successfully.']);
    }

    public function start(Request $request, Poll $poll): JsonResponse
    {
        if ($poll->teacher_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        if (! $poll->isDraft()) {
            return response()->json(['message' => 'Only draft polls can be started.'], 422);
        }

        $activePoll = $this->pollService->getActivePoll();
        if ($activePoll && $activePoll->id !== $poll->id) {
            return response()->json(['message' => 'Only one poll can be active at a time.'], 422);
        }

        $poll = $this->pollService->start($poll);

        return response()->json([
            'message' => 'Poll started successfully.',
            'poll' => new PollResource($poll),
        ]);
    }

    public function end(Request $request, Poll $poll): JsonResponse
    {
        if ($poll->teacher_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        if (! $poll->isActive()) {
            return response()->json(['message' => 'Only active polls can be ended.'], 422);
        }

        $poll = $this->pollService->end($poll);

        return response()->json([
            'message' => 'Poll ended successfully.',
            'poll' => new PollResource($poll),
        ]);
    }

    public function active(): JsonResponse
    {
        $poll = $this->pollService->getActivePoll();

        if (! $poll) {
            return response()->json(['message' => 'No active poll available.'], 404);
        }

        $poll->load('options');

        return response()->json([
            'poll' => new PollResource($poll),
            'hasVoted' => auth()->user()?->isStudent()
                ? $poll->votes()->where('student_id', auth()->id())->exists()
                : false,
        ]);
    }

    public function results(Poll $poll): PollResultResource
    {
        $results = $this->pollService->getResults($poll);

        return new PollResultResource($results);
    }

    public function qrCode(Poll $poll, Request $request): JsonResponse
    {
        if ($poll->teacher_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $joinUrl = URL::to('/live-voting').'?code='.$poll->room_code;

        return response()->json([
            'room_code' => $poll->room_code,
            'join_url' => $joinUrl,
            'poll_id' => $poll->id,
        ]);
    }
}
