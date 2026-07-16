<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ValidateGameAnswerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'question_id' => ['nullable', 'string', 'max:50'],
            'submitted_answer' => ['required', 'string', 'max:500'],
            'correct_answer' => ['nullable', 'string', 'max:500'],
            'participant_name' => ['nullable', 'string', 'max:100'],
        ];
    }
}
