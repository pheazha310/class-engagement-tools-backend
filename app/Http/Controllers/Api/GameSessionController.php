<?php

namespace App\Http\Controllers\Api;

use App\Events\ScoreUpdated;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreGameSessionRequest;
use App\Http\Requests\ValidateGameAnswerRequest;
use App\Http\Resources\GameAnswerResource;
use App\Http\Resources\GameSessionResource;
use App\Models\GameAnswer;
use App\Models\GameSession;
use App\Services\GameQuestionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class GameSessionController extends Controller
{
    public function index(): JsonResponse
    {
        $user = Auth::user();

        $sessions = GameSession::query()
            ->when($user, fn ($query) => $query->where('teacher_id', $user->id))
            ->when(! $user, fn ($query) => $query->whereNull('teacher_id'))
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'data' => GameSessionResource::collection($sessions),
        ], Response::HTTP_OK);
    }

    public function store(StoreGameSessionRequest $request): JsonResponse
    {
        $data = $request->validated();

        $user = Auth::user();

        if ($user) {
            $gameSession = $user->gameSessions()->create($data);
        } else {
            $gameSession = GameSession::create($data);
        }

        return response()->json([
            'game_session' => new GameSessionResource($gameSession),
            'game_id' => $gameSession->id,
        ], Response::HTTP_CREATED);
    }

    public function showByJoinCode(string $joinCode): JsonResponse
    {
        $session = GameSession::where('join_code', $joinCode)
            ->where('status', 'active')
            ->firstOrFail();

        return response()->json([
            'game_session' => new GameSessionResource($session),
        ], Response::HTTP_OK);
    }

    public function generateQuestions(Request $request): JsonResponse
    {
        $data = $request->validate([
            'game_type' => ['required', 'string', 'max:50'],
            'settings' => ['nullable', 'array'],
        ]);

        $questions = app(GameQuestionService::class)->generate(
            $data['game_type'],
            $data['settings'] ?? []
        );

        return response()->json([
            'game_type' => $data['game_type'],
            'questions' => $questions,
        ], Response::HTTP_OK);
    }

    public function validateAnswer(ValidateGameAnswerRequest $request, GameSession $gameSession): JsonResponse
    {
        if (! $gameSession->isActive()) {
            return response()->json([
                'message' => 'Game session is not active.',
            ], Response::HTTP_NOT_FOUND);
        }

        $data = $request->validated();

        $submitted = Str::of($data['submitted_answer'])->trim()->toString();
        $expected = Str::of($data['correct_answer'])->trim()->toString();

        $isCorrect = Str::lower($submitted) === Str::lower($expected);
        $pointsAwarded = $isCorrect ? 10 : 0;

        $user = Auth::user();

        $answer = GameAnswer::create([
            'game_session_id' => $gameSession->id,
            'question_id' => $data['question_id'] ?? null,
            'submitted_answer' => $data['submitted_answer'],
            'is_correct' => $isCorrect,
            'points_awarded' => $pointsAwarded,
            'user_id' => $user?->id,
            'participant_name' => $data['participant_name'] ?? ($user?->name ?? null),
        ]);

        $participantId = $user?->id ?? $data['participant_name'] ?? null;
        $participantName = $data['participant_name'] ?? ($user?->name ?? 'Anonymous');
        $totalScore = GameAnswer::where('game_session_id', $gameSession->id)
            ->when($user, fn ($query) => $query->where('user_id', $user->id))
            ->when(! $user && ($data['participant_name'] ?? false), fn ($query) => $query->where('participant_name', $data['participant_name']))
            ->sum('points_awarded');

        broadcast(new ScoreUpdated(
            gameSessionId: $gameSession->id,
            participantId: $participantId,
            participantName: $participantName,
            score: $totalScore,
            pointsAwarded: $pointsAwarded,
            isCorrect: $isCorrect,
            questionId: $data['question_id'] ?? null,
        ))->toOthers();

        return response()->json([
            'is_correct' => $isCorrect,
            'points_awarded' => $pointsAwarded,
            'total_score' => $totalScore,
            'score' => $totalScore,
            'game_answer' => new GameAnswerResource($answer),
        ], Response::HTTP_OK);
    }
}
