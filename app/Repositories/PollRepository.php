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

    public function findActiveBySchool(int $schoolId): ?Poll
    {
        return Poll::with('options')->active()->bySchool($schoolId)->first();
    }

    public function findActivePollsBySchool(int $schoolId): iterable
    {
        return Poll::with('options')->active()->bySchool($schoolId)->latest()->get();
    }

    public function findByRoomCode(string $roomCode): ?Poll
    {
        return Poll::with('options')->byRoomCode($roomCode)->first();
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
        $poll->loadMissing('options.votes.student');

        if ($poll->is_open_text) {
            return $this->getOpenTextResults($poll);
        }

        $totalVotes = $poll->votes()->count();
        $hasWeights = $poll->max_points !== null && $poll->max_points > 0;

        $totalPoints = $hasWeights ? $poll->votes()->sum('points') : $totalVotes;

        $results = $poll->options->map(function ($option) use ($totalPoints, $hasWeights) {
            if ($hasWeights) {
                $points = $option->votes->sum('points');
            } else {
                $points = $option->votes->count();
            }

            return [
                'id' => $option->id,
                'option' => $option->option_text,
                'votes' => $option->votes->count(),
                'points' => $hasWeights ? $points : null,
                'percentage' => $totalPoints > 0 ? round(($points / $totalPoints) * 100, 1) : 0,
                'is_correct' => $option->is_correct,
            ];
        });

        $data = [
            'question' => $poll->question,
            'status' => $poll->status,
            'totalVotes' => $totalVotes,
            'totalPoints' => $hasWeights ? $totalPoints : null,
            'is_anonymous' => $poll->is_anonymous,
            'is_quiz' => $poll->is_quiz,
            'is_open_text' => $poll->is_open_text,
            'max_points' => $poll->max_points,
            'has_weights' => $hasWeights,
            'results' => $results->toArray(),
        ];

        if ($poll->is_quiz) {
            $data['quiz_summary'] = $this->getQuizSummary($poll);
        }

        return $data;
    }

    private function getOpenTextResults(Poll $poll): array
    {
        $votes = $poll->votes()->with('student')->get();

        $responses = $votes->map(function ($vote) use ($poll) {
            $response = [
                'text' => $vote->text_response ?? '',
            ];

            if (! $poll->is_anonymous) {
                $response['student_name'] = $vote->student?->name ?? 'Unknown';
                $response['student_id'] = $vote->student_id;
            }

            return $response;
        });

        return [
            'question' => $poll->question,
            'status' => $poll->status,
            'totalVotes' => $votes->count(),
            'is_anonymous' => $poll->is_anonymous,
            'is_open_text' => true,
            'results' => $responses->toArray(),
        ];
    }

    private function getQuizSummary(Poll $poll): array
    {
        $poll->loadMissing('options');

        $correctOptionIds = $poll->options->where('is_correct', true)->pluck('id')->toArray();

        $totalVotes = $poll->votes()->count();
        $correctVotes = $poll->votes()->whereIn('option_id', $correctOptionIds)->count();

        $correctStudents = $poll->votes()
            ->with('student')
            ->whereIn('option_id', $correctOptionIds)
            ->get()
            ->map(fn ($vote) => [
                'student_id' => $vote->student_id,
                'student_name' => $vote->student?->name ?? 'Unknown',
            ]);

        return [
            'correct_option_ids' => $correctOptionIds,
            'total_votes' => $totalVotes,
            'correct_votes' => $correctVotes,
            'correct_percentage' => $totalVotes > 0 ? round(($correctVotes / $totalVotes) * 100, 1) : 0,
            'correct_students' => $correctStudents->toArray(),
        ];
    }
}
