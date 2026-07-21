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

    public function findByRoomCode(string $roomCode): ?Poll
    {
        return $this->pollRepository->findByRoomCode($roomCode);
    }

    public function findByShareToken(string $shareToken): ?Poll
    {
        return $this->pollRepository->findByShareToken($shareToken);
    }

    public function create(array $data, User $teacher): Poll
    {
        $pollType = $data['poll_type'] ?? Poll::POLL_TYPE_MULTIPLE_CHOICE;

        $poll = $this->pollRepository->create([
            'teacher_id' => $teacher->id,
            'school_id' => $teacher->schoolId(),
            'province_id' => $teacher->provinceId(),
            'question' => $data['question'],
            'poll_type' => $pollType,
            'status' => 'draft',
            'is_multiple_choice' => $data['is_multiple_choice'] ?? ($pollType === Poll::POLL_TYPE_MULTIPLE_CHOICE),
            'show_results' => $data['show_results'] ?? true,
            'duration_minutes' => $data['duration_minutes'] ?? null,
            'is_anonymous' => $data['is_anonymous'] ?? false,
            'is_quiz' => $data['is_quiz'] ?? false,
            'is_open_text' => $data['is_open_text'] ?? false,
            'max_points' => $data['max_points'] ?? null,
        ]);

        $poll->options()->createMany(
            collect($this->resolveOptions($pollType, $data['options'] ?? []))
                ->map(fn (string $text) => ['option_text' => $text])
                ->toArray()
        );

        return $poll->load('options');
    }

    /**
     * Resolve the option set for a poll type, defaulting Yes/No and Rating scales.
     *
     * @param  array<int, string>  $provided
     * @return array<int, string>
     */
    public function resolveOptions(string $pollType, array $provided): array
    {
        if ($pollType === Poll::POLL_TYPE_YES_NO) {
            return ['Yes', 'No'];
        }

        if ($pollType === Poll::POLL_TYPE_RATING) {
            return collect(range(1, 5))->map(fn (int $n) => (string) $n)->toArray();
        }

        return $provided;
    }

    public function update(Poll $poll, array $data): Poll
    {
        $pollType = $data['poll_type'] ?? $poll->poll_type;

        $poll = $this->pollRepository->update($poll, [
            'question' => $data['question'] ?? $poll->question,
            'poll_type' => $pollType,
            'is_multiple_choice' => $data['is_multiple_choice'] ?? $poll->is_multiple_choice,
            'show_results' => $data['show_results'] ?? $poll->show_results,
            'duration_minutes' => $data['duration_minutes'] ?? $poll->duration_minutes,
            'is_anonymous' => $data['is_anonymous'] ?? $poll->is_anonymous,
            'is_quiz' => $data['is_quiz'] ?? $poll->is_quiz,
            'is_open_text' => $data['is_open_text'] ?? $poll->is_open_text,
            'max_points' => $data['max_points'] ?? $poll->max_points,
        ]);

        if (isset($data['options'])) {
            $poll->options()->delete();
            $poll->options()->createMany(
                collect($this->resolveOptions($pollType, $data['options']))
                    ->map(fn (string $text) => ['option_text' => $text])
                    ->toArray()
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

    public function getActivePollBySchool(int $schoolId): ?Poll
    {
        return $this->pollRepository->findActiveBySchool($schoolId);
    }

    public function getActivePollsBySchool(User $user): iterable
    {
        $schoolId = $user->schoolId();

        if (! $schoolId) {
            return collect();
        }

        return $this->pollRepository->findActivePollsBySchool($schoolId);
    }

    public function getResults(Poll $poll): array
    {
        return $this->pollRepository->getResults($poll);
    }

    /**
     * Close every active poll whose timer has expired.
     *
     * @return int The number of polls that were closed.
     */
    public function closeExpiredPolls(): int
    {
        $expired = $this->pollRepository->findExpired();

        $closed = 0;

        foreach ($expired as $poll) {
            $this->pollRepository->end($poll);
            $closed++;
        }

        return $closed;
    }
}
