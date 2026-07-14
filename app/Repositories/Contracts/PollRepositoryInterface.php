<?php

namespace App\Repositories\Contracts;

use App\Models\Poll;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface PollRepositoryInterface
{
    public function findAllPaginated(int $perPage = 10): LengthAwarePaginator;

    public function findById(int $id): ?Poll;

    public function findActive(): ?Poll;

    public function findActiveBySchool(int $schoolId): ?Poll;

    public function findActivePollsBySchool(int $schoolId): iterable;

    public function findByRoomCode(string $roomCode): ?Poll;

    public function findByTeacher(int $teacherId, int $perPage = 10): LengthAwarePaginator;

    public function create(array $data): Poll;

    public function update(Poll $poll, array $data): Poll;

    public function delete(Poll $poll): bool;

    public function start(Poll $poll): Poll;

    public function end(Poll $poll): Poll;

    public function getResults(Poll $poll): array;
}
