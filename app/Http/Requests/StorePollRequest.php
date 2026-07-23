<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePollRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isTeacher() ?? false;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'question' => ['required', 'string', 'max:1000'],
            'poll_type' => ['required', 'string', 'in:multiple_choice,yes_no,rating'],
            'duration_minutes' => ['nullable', 'integer', 'min:1', 'max:1440'],
            'allow_multiple_votes' => ['boolean'],
            'anonymous' => ['boolean'],
            'show_results' => ['boolean'],
            'options' => ['array', 'max:20'],
            'options.*' => ['required', 'string', 'max:255', 'distinct'],
        ];
    }

    public function messages(): array
    {
        return [
            'options.max' => 'Maximum 20 options allowed.',
            'options.*.distinct' => 'Duplicate options are not allowed.',
        ];
    }
}
