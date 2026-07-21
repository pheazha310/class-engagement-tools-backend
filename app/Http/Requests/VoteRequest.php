<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'option_id' => ['required', 'string', 'exists:poll_options,id'],
            'guest_token' => ['nullable', 'string', 'max:64'],
        ];
    }
}
