<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePollRequest extends FormRequest
{
    public function authorize(): bool
    {
        $poll = $this->route('poll');

        return $this->user()?->isTeacher()
            && $poll?->teacher_id === $this->user()->id
            && $poll?->isDraft();
    }

    public function rules(): array
    {
        return [
            'question' => ['sometimes', 'required', 'string', 'max:500'],
            'options' => ['sometimes', 'required', 'array', 'min:2', 'max:10'],
            'options.*' => ['required', 'string', 'max:255', 'distinct'],
            'is_multiple_choice' => ['sometimes', 'boolean'],
            'duration_minutes' => ['sometimes', 'integer', 'min:1', 'max:120'],
            'is_anonymous' => ['sometimes', 'boolean'],
            'is_quiz' => ['sometimes', 'boolean'],
            'is_open_text' => ['sometimes', 'boolean'],
            'max_points' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'options_correct' => ['sometimes', 'array'],
            'options_correct.*' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'question.required' => 'A poll question is required.',
            'options.min' => 'A poll must have at least 2 options.',
            'options.max' => 'A poll can have at most 10 options.',
            'options.*.distinct' => 'Duplicate options are not allowed.',
            'duration_minutes.max' => 'Maximum duration is 120 minutes.',
            'max_points.max' => 'Maximum points is 100.',
        ];
    }
}
