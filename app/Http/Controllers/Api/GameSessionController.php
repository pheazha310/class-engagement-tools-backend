<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreGameSessionRequest;
use App\Http\Resources\GameSessionResource;
use App\Models\GameSession;
use App\Services\GameQuestionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

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
}
