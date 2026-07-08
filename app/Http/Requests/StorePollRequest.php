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
            'question' => ['required', 'string', 'max:500'],
            'options' => ['required', 'array', 'min:2', 'max:10'],
            'options.*' => ['required', 'string', 'max:255', 'distinct'],
        ];
    }

    public function messages(): array
    {
        return [
            'question.required' => 'A poll question is required.',
            'options.required' => 'At least 2 options are required.',
            'options.min' => 'A poll must have at least 2 options.',
            'options.max' => 'A poll can have at most 10 options.',
            'options.*.distinct' => 'Duplicate options are not allowed.',
        ];
    }
}
