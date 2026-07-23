<?php

namespace App\Repositories;

use App\Models\GameAnswer;
use App\Models\GameHistory;
use App\Models\GameSession;
use App\Repositories\Contracts\GameHistoryRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class GameHistoryRepository implements GameHistoryRepositoryInterface
{
    public function findAllPaginated(int $perPage = 10): LengthAwarePaginator
    {
        return GameHistory::with('gameSession', 'teacher')->latest()->paginate($perPage);
    }

    public function findById(int|string $id): ?GameHistory
    {
        return GameHistory::with('gameSession', 'teacher')->find($id);
    }

    public function findByTeacher(int $teacherId, int $perPage = 10): LengthAwarePaginator
    {
        return GameHistory::with('gameSession', 'teacher')
            ->where('teacher_id', $teacherId)
            ->latest()
            ->paginate($perPage);
    }

    public function findByGameSession(GameSession $gameSession): ?GameHistory
    {
        return GameHistory::where('game_session_id', $gameSession->id)->first();
    }

    public function create(array $data): GameHistory
    {
        return GameHistory::query()->create($data);
    }

    public function createFromSession(GameSession $session): GameHistory
    {
        $scores = GameAnswer::where('game_session_id', $session->id)
            ->selectRaw('COALESCE(participant_name, user_id) as participant_key')
            ->selectRaw('MAX(participant_name) as participant_name')
            ->selectRaw('SUM(points_awarded) as score')
            ->groupBy('participant_key')
            ->orderByDesc('score')
            ->get()
            ->map(fn ($row) => [
                'participant' => $row->participant_name,
                'score' => (int) $row->score,
            ])
            ->values()
            ->all();

        $participants = GameAnswer::where('game_session_id', $session->id)
            ->selectRaw('COALESCE(participant_name, user_id) as participant_key')
            ->selectRaw('MAX(participant_name) as participant_name')
            ->groupBy('participant_key')
            ->get()
            ->map(fn ($row) => $row->participant_name)
            ->values()
            ->all();

        $totalQuestions = GameAnswer::where('game_session_id', $session->id)
            ->count();

        return $this->create([
            'game_session_id' => $session->id,
            'teacher_id' => $session->teacher_id,
            'game_type' => $session->game_type,
            'settings' => $session->settings,
            'participants' => $participants,
            'scores' => $scores,
            'total_questions' => $totalQuestions,
            'started_at' => $session->started_at,
            'ended_at' => $session->ended_at ?? now(),
        ]);
    }

    public function findEndedByTeacher(int $teacherId): Collection
    {
        return GameHistory::with('gameSession', 'teacher')
            ->where('teacher_id', $teacherId)
            ->whereHas('gameSession', fn ($query) => $query->where('status', 'ended'))
            ->latest()
            ->get();
    }

    public function findEndedGuestSessions(): Collection
    {
        return GameHistory::with('gameSession', 'teacher')
            ->whereNull('teacher_id')
            ->whereHas('gameSession', fn ($query) => $query->where('status', 'ended'))
            ->latest()
            ->get();
    }
}
