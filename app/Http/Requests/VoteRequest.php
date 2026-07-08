<?php

namespace App\Http\Requests;

use App\Models\Poll;
use App\Models\PollOption;
use Illuminate\Foundation\Http\FormRequest;

class VoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isStudent() ?? false;
    }

    public function rules(): array
    {
        return [
            'option_id' => [
                'required',
                'integer',
                'exists:poll_options,id',
            ],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $poll = $this->route('poll');

            if (! $poll instanceof Poll) {
                $validator->errors()->add('poll', 'Poll not found.');

                return;
            }

            if (! $poll->isActive()) {
                $validator->errors()->add('poll', 'This poll is not active.');
            }

            $option = PollOption::find($this->option_id);
            if ($option && $option->poll_id !== $poll->id) {
                $validator->errors()->add('option_id', 'Selected option does not belong to this poll.');
            }

            if ($this->user() && $poll->votes()->where('student_id', $this->user()->id)->exists()) {
                $validator->errors()->add('vote', 'You have already voted on this poll.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'option_id.required' => 'Please select an option.',
            'option_id.exists' => 'Selected option is invalid.',
        ];
    }
}
