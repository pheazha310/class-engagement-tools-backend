<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'role' => ['nullable', 'string', 'in:teacher,student'],
            'country_id' => ['nullable', 'exists:countries,id'],
            'province_id' => ['nullable', 'exists:provinces,id'],
            'school_id' => ['nullable', 'exists:schools,id'],
            'country_name' => ['nullable', 'string', 'max:255'],
            'province_name' => ['nullable', 'string', 'max:255'],
            'school_name' => ['nullable', 'string', 'max:255'],
        ];
    }
}
