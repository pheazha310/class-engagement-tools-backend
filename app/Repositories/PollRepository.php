<?php

namespace App\Repositories;

use App\Models\Poll;
use App\Repositories\Contracts\PollRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PollRepository implements PollRepositoryInterface
{
    public function findAllPaginated(int $perPage = 10): LengthAwarePaginator
    {
        return Poll::with('options')->latest()->paginate($perPage);
    }

    public function findById(int $id): ?Poll
    {
        return Poll::with('options')->find($id);
    }

    public function findActive(): ?Poll
    {
        return Poll::with('options')->active()->first();
    }

    public function findByTeacher(int $teacherId, int $perPage = 10): LengthAwarePaginator
    {
        return Poll::with('options')
            ->where('teacher_id', $teacherId)
            ->latest()
            ->paginate($perPage);
    }

    public function create(array $data): Poll
    {
        return Poll::query()->create($data);
    }

    public function update(Poll $poll, array $data): Poll
    {
        $poll->update($data);

        return $poll->fresh();
    }

    public function delete(Poll $poll): bool
    {
        return $poll->delete();
    }

    public function start(Poll $poll): Poll
    {
        return $this->update($poll, [
            'status' => 'active',
            'started_at' => now(),
        ]);
    }

    public function end(Poll $poll): Poll
    {
        return $this->update($poll, [
            'status' => 'ended',
            'ended_at' => now(),
        ]);
    }

    public function getResults(Poll $poll): array
    {
        $poll->load('options.votes');

        $totalVotes = $poll->votes()->count();

        $results = $poll->options->map(function ($option) use ($totalVotes) {
            $votes = $option->votes->count();

            return [
                'id' => $option->id,
                'option' => $option->option_text,
                'votes' => $votes,
                'percentage' => $totalVotes > 0 ? round(($votes / $totalVotes) * 100, 1) : 0,
            ];
        });

        return [
            'question' => $poll->question,
            'status' => $poll->status,
            'totalVotes' => $totalVotes,
            'results' => $results->toArray(),
        ];
    }
}
