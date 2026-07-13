<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreGameSessionRequest;
use App\Http\Resources\GameSessionResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class GameSessionController extends Controller
{
    public function store(StoreGameSessionRequest $request): JsonResponse
    {
        $data = $request->validated();

        $user = Auth::user();

        if ($user) {
            $gameSession = $user->gameSessions()->create($data);
        } else {
            $gameSession = \App\Models\GameSession::create($data);
        }

        return response()->json([
            'game_session' => new GameSessionResource($gameSession),
            'game_id' => $gameSession->id,
        ], Response::HTTP_CREATED);
    }
}
