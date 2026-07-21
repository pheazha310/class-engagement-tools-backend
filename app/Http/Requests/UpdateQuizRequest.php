<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateQuizRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string', 'max:2000'],
            'subject' => ['sometimes', 'required', 'string', 'max:255'],
            'class_name' => ['sometimes', 'required', 'string', 'max:255'],
            'duration' => ['sometimes', 'required', 'integer', 'min:1', 'max:480'],
            'passing_score' => ['sometimes', 'integer', 'min:0', 'max:100'],
            'due_date' => ['sometimes', 'required', 'date_format:Y-m-d\\TH:i'],
            'shuffle_questions' => ['sometimes', 'boolean'],
            'status' => ['sometimes', 'string', 'in:draft,published'],
            'teacher_id' => ['sometimes', 'nullable', 'string', 'max:36'],
        ];
    }

    public function messages(): array
    {
        return [
            'due_date.date_format' => 'The due date must be in the format Y-m-d\\\\TH:i (e.g. 2026-07-20T23:59).',
        ];
    }
}
