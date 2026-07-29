<?php

namespace App\Http\Requests;

use App\Models\Poll;
use Illuminate\Foundation\Http\FormRequest;

class StorePollRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isTeacher() ?? false;
    }

    public function rules(): array
    {
        $pollType = (string) $this->input('poll_type', Poll::POLL_TYPE_MULTIPLE_CHOICE);

        $optionsRules = ['nullable', 'array', 'max:20'];
        $optionItemRules = ['sometimes', 'string', 'max:255', 'distinct'];

        if ($pollType === Poll::POLL_TYPE_MULTIPLE_CHOICE) {
            $optionsRules = ['required', 'array', 'min:2', 'max:20'];
            $optionItemRules = ['required', 'string', 'max:255', 'distinct'];
        }

        return [
            'title' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'question' => ['required', 'string', 'max:1000'],
            'poll_type' => ['required', 'string', 'in:multiple_choice,yes_no,rating'],
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
            'options.required' => 'At least 2 options are required.',
            'options.min' => 'At least 2 options are required.',
            'options.max' => 'Maximum 20 options allowed.',
            'options.*.distinct' => 'Duplicate options are not allowed.',
        ];
    }
}
