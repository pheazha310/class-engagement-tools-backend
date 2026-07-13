<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePollRequest;
use App\Http\Requests\UpdatePollRequest;
use App\Http\Requests\UpdatePollStatusRequest;
use App\Http\Resources\PollResource;
use App\Http\Resources\PollResultResource;
use App\Models\Poll;
use App\Services\PollService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

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

    public function show(Request $request, Poll $poll): PollResource|JsonResponse
    {
        $user = $request->user();

        if ($user?->isStudent()) {
            if (! $poll->isActive() || $poll->school_id === null || $user->schoolId() !== $poll->school_id) {
                return response()->json(['message' => 'This poll is not available for your school.'], 403);
            }
        } elseif ($user?->isTeacher()) {
            if ($poll->teacher_id !== $user->id) {
                return response()->json(['message' => 'Unauthorized.'], 403);
            }
        }

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

    public function active(Request $request): JsonResponse
    {
        $user = $request->user() ?? auth()->user();

        if ($user && $user->isStudent() && $user->schoolId()) {
            $polls = $this->pollService->getActivePollsBySchool($user);

            return response()->json([
                'polls' => PollResource::collection($polls),
            ]);
        }

        if ($user && $user->isTeacher() && $user->schoolId()) {
            $polls = $this->pollService->getActivePollsBySchool($user);

            return response()->json([
                'polls' => PollResource::collection($polls),
            ]);
        }

        $poll = $this->pollService->getActivePoll();

        if (! $poll) {
            return response()->json(['message' => 'No active poll available.'], 404);
        }

        $poll->load('options');

        return response()->json([
            'poll' => new PollResource($poll),
            'hasVoted' => $user?->isStudent()
                ? $poll->votes()->where('student_id', $user->id)->exists()
                : false,
        ]);
    }

    public function schoolActive(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user->schoolId()) {
            return response()->json(['message' => 'You are not assigned to a school.'], 403);
        }

        $polls = $this->pollService->getActivePollsBySchool($user);

        return response()->json([
            'polls' => PollResource::collection($polls),
        ]);
    }

    public function results(Poll $poll): PollResultResource
    {
        $results = $this->pollService->getResults($poll);

        return new PollResultResource($results);
    }

    public function status(UpdatePollStatusRequest $request, Poll $poll): JsonResponse
    {
        $requestedStatus = $request->validated()['status'];

        if ($requestedStatus === 'active') {
            $poll = $this->pollService->start($poll);
        } else {
            $poll = $this->pollService->end($poll);
        }

        return response()->json([
            'message' => "Poll {$requestedStatus} successfully.",
            'poll' => new PollResource($poll),
        ]);
    }
}
