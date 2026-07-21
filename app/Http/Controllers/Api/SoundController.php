<?php

namespace App\Http\Controllers\Api;

use App\Events\SoundPlayed;
use App\Http\Controllers\Controller;
use App\Http\Resources\SoundPlayHistoryResource;
use App\Http\Resources\SoundResource;
use App\Models\Sound;
use App\Models\SoundPlayHistory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class SoundController extends Controller
{
    public function index(): ResourceCollection
    {
        $sounds = Sound::query()
            ->orderBy('category')
            ->orderBy('name')
            ->get();

        return SoundResource::collection($sounds);
    }

    public function play(Request $request, Sound $sound): JsonResponse
    {
        $event = SoundPlayed::fromModel($sound);

        broadcast($event);

        // Record play history
        SoundPlayHistory::create([
            'sound_id' => $sound->id,
            'sound_name' => $sound->name,
            'audio_url' => $sound->audio_url,
            'sound_category' => $sound->category,
            'icon' => $sound->icon,
            'duration_seconds' => $sound->duration_seconds,
            'played_by' => $request->user()?->id,
            'played_at' => now(),
        ]);

        return response()->json([
            'message' => 'Sound played successfully.',
            'data' => [
                'soundId' => $sound->id,
                'soundName' => $sound->name,
                'audioUrl' => $sound->audio_url,
                'playedAt' => now()->toISOString(),
            ],
        ]);
    }

    public function history(Request $request): ResourceCollection
    {
        $histories = SoundPlayHistory::query()
            ->with('player')
            ->orderBy('played_at', 'desc')
            ->paginate($request->input('per_page', 50));

        return SoundPlayHistoryResource::collection($histories);
    }
}
