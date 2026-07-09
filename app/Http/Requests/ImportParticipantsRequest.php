<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ImportParticipantsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'max:5120', 'mimetypes:text/plain,text/csv,application/vnd.ms-excel', 'extensions:csv,txt'],
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => 'Please select a file to import.',
            'file.file' => 'The uploaded file is invalid.',
            'file.max' => 'The file may not be greater than 5 MB.',
            'file.mimetypes' => 'Only CSV and TXT files are supported.',
            'file.extensions' => 'Only .csv and .txt files are supported.',
        ];
    }
}
