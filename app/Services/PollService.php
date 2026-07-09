<?php

namespace App\Services;

use App\Models\Poll;
use App\Models\User;
use App\Repositories\Contracts\PollRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

readonly class PollService
{
    public function __construct(
        private PollRepositoryInterface $pollRepository,
    ) {}

    public function getTeacherPolls(User $teacher, int $perPage = 10): LengthAwarePaginator
    {
        return $this->pollRepository->findByTeacher($teacher->id, $perPage);
    }

    public function findById(int $id): ?Poll
    {
        return $this->pollRepository->findById($id);
    }

    public function create(array $data, User $teacher): Poll
    {
        $poll = $this->pollRepository->create([
            'teacher_id' => $teacher->id,
            'question' => $data['question'],
            'status' => 'draft',
        ]);

        $poll->options()->createMany(
            collect($data['options'])->map(fn (string $text) => ['option_text' => $text])->toArray()
        );

        return $poll->load('options');
    }

    public function update(Poll $poll, array $data): Poll
    {
        $poll = $this->pollRepository->update($poll, [
            'question' => $data['question'] ?? $poll->question,
        ]);

        if (isset($data['options'])) {
            $poll->options()->delete();
            $poll->options()->createMany(
                collect($data['options'])->map(fn (string $text) => ['option_text' => $text])->toArray()
            );
        }

        return $poll->load('options');
    }

    public function delete(Poll $poll): bool
    {
        return $this->pollRepository->delete($poll);
    }

    public function start(Poll $poll): Poll
    {
        return $this->pollRepository->start($poll);
    }

    public function end(Poll $poll): Poll
    {
        return $this->pollRepository->end($poll);
    }

    public function getActivePoll(): ?Poll
    {
        return $this->pollRepository->findActive();
    }

    public function getResults(Poll $poll): array
    {
        return $this->pollRepository->getResults($poll);
    }
}
