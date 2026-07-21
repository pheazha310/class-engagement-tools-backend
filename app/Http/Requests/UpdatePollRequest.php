<?php

namespace App\Http\Requests;

use App\Models\Poll;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePollRequest extends FormRequest
{
    public function authorize(): bool
    {
        $poll = $this->route('poll');

        if (! $poll instanceof Poll) {
            return false;
        }

        return $this->user()?->isTeacher()
            && $poll->created_by === $this->user()->id
            && $poll->isDraft();
    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'question' => ['sometimes', 'required', 'string', 'max:1000'],
            'poll_type' => ['sometimes', 'required', 'string', 'in:multiple_choice,yes_no,rating'],
            'duration_minutes' => ['nullable', 'integer', 'min:1', 'max:1440'],
            'allow_multiple_votes' => ['boolean'],
            'anonymous' => ['boolean'],
            'show_results' => ['boolean'],
            'options' => ['sometimes', 'required', 'array', 'min:2', 'max:20'],
            'options.*' => ['required', 'string', 'max:255', 'distinct'],
        ];
    }

    public function messages(): array
    {
        return [
            'options.min' => 'At least 2 options are required.',
            'options.max' => 'Maximum 20 options allowed.',
            'options.*.distinct' => 'Duplicate options are not allowed.',
        ];
    }
}
