<?php

namespace App\Repositories\Contracts;

use App\Models\GameHistory;
use App\Models\GameSession;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface GameHistoryRepositoryInterface
{
    public function findAllPaginated(int $perPage = 10): LengthAwarePaginator;

    public function findById(int $id): ?GameHistory;

    public function findByTeacher(int $teacherId, int $perPage = 10): LengthAwarePaginator;

    public function findByGameSession(GameSession $gameSession): ?GameHistory;

    public function create(array $data): GameHistory;

    public function createFromSession(GameSession $session): GameHistory;

    public function findEndedByTeacher(int $teacherId): Collection;

    public function findEndedGuestSessions(): Collection;
}
