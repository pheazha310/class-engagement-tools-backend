<?php

namespace App\Http\Requests;

use App\Models\Poll;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePollStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        $poll = $this->route('poll');

        return $this->user()?->isTeacher()
            && $poll instanceof Poll
            && $poll->teacher_id === $this->user()->id;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'string', 'in:active,closed'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $poll = $this->route('poll');

            if (! $poll instanceof Poll) {
                return;
            }

            $requestedStatus = $this->input('status');

            if ($requestedStatus === 'active' && ! $poll->isDraft()) {
                $validator->errors()->add('status', 'Only draft polls can be opened.');
            }

            if ($requestedStatus === 'closed' && ! $poll->isActive()) {
                $validator->errors()->add('status', 'Only active polls can be closed.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'status.required' => 'Status is required.',
            'status.in' => 'Status must be either "active" or "closed".',
        ];
    }
}
