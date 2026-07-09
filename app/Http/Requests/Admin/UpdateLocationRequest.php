<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLocationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'country' => ['required', 'string', 'max:255'],
            'province' => ['required', 'string', 'max:255'],
            'school_name' => ['required', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'country.required' => __('Please select a country.'),
            'province.required' => __('Please select a province.'),
            'school_name.required' => __('Please enter the school name.'),
        ];
    }
}
