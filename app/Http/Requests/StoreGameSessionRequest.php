<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreGameSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'game_type' => ['required', 'string', 'max:50'],
            'settings' => ['nullable', 'array'],
        ];
    }
}
