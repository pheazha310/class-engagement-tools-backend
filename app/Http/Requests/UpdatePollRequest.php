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
        $pollType = (string) $this->input('poll_type', $this->route('poll')?->poll_type ?? Poll::POLL_TYPE_MULTIPLE_CHOICE);

        $optionsRules = ['sometimes', 'nullable', 'array', 'max:20'];
        $optionItemRules = ['required', 'string', 'max:255', 'distinct'];

        if ($pollType === Poll::POLL_TYPE_MULTIPLE_CHOICE) {
            $optionsRules = ['sometimes', 'required', 'array', 'min:2', 'max:20'];
        }

        return [
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'question' => ['sometimes', 'required', 'string', 'max:1000'],
            'poll_type' => ['sometimes', 'required', 'string', 'in:multiple_choice,yes_no,rating'],
            'duration_minutes' => ['nullable', 'integer', 'min:1', 'max:1440'],
            'allow_multiple_votes' => ['boolean'],
            'anonymous' => ['boolean'],
            'show_results' => ['boolean'],
            'options' => $optionsRules,
            'options.*' => $optionItemRules,
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
