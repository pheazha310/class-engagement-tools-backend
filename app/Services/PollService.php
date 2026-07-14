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

    public function create(array $data, User $teacher): Poll
    {
        $poll = $this->pollRepository->create([
            'teacher_id' => $teacher->id,
            'school_id' => $teacher->schoolId(),
            'province_id' => $teacher->provinceId(),
            'question' => $data['question'],
            'status' => 'draft',
            'is_multiple_choice' => $data['is_multiple_choice'] ?? false,
            'duration_minutes' => $data['duration_minutes'] ?? null,
            'is_anonymous' => $data['is_anonymous'] ?? false,
            'is_quiz' => $data['is_quiz'] ?? false,
            'is_open_text' => $data['is_open_text'] ?? false,
            'max_points' => $data['max_points'] ?? null,
        ]);

        $optionsData = collect($data['options'])->map(function (string $text, int $index) use ($data) {
            $optionData = ['option_text' => $text];

            if (isset($data['options_correct'][$index])) {
                $optionData['is_correct'] = (bool) $data['options_correct'][$index];
            }

            return $optionData;
        })->toArray();

        $poll->options()->createMany($optionsData);

        if (isset($data['correct_option_id'])) {
            $this->pollRepository->update($poll, ['correct_option_id' => $data['correct_option_id']]);
        }

        return $poll->load('options');
    }

    public function update(Poll $poll, array $data): Poll
    {
        $updateData = [
            'question' => $data['question'] ?? $poll->question,
            'is_multiple_choice' => $data['is_multiple_choice'] ?? $poll->is_multiple_choice,
            'duration_minutes' => $data['duration_minutes'] ?? $poll->duration_minutes,
            'is_anonymous' => $data['is_anonymous'] ?? $poll->is_anonymous,
            'is_quiz' => $data['is_quiz'] ?? $poll->is_quiz,
            'is_open_text' => $data['is_open_text'] ?? $poll->is_open_text,
            'max_points' => $data['max_points'] ?? $poll->max_points,
        ]);

        if (isset($data['options'])) {
            $poll->options()->delete();
            $optionsData = collect($data['options'])->map(function (string $text, int $index) use ($data) {
                $optionData = ['option_text' => $text];

                if (isset($data['options_correct'][$index])) {
                    $optionData['is_correct'] = (bool) $data['options_correct'][$index];
                }

                return $optionData;
            })->toArray();
            $poll->options()->createMany($optionsData);
        }

        if (isset($data['correct_option_id'])) {
            $this->pollRepository->update($poll, ['correct_option_id' => $data['correct_option_id']]);
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
}
