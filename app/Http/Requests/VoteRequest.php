<?php

namespace App\Http\Requests;

use App\Models\Poll;
use App\Models\PollOption;
use Illuminate\Foundation\Http\FormRequest;

class VoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $poll = $this->route('poll');

        $rules = [
            'option_id' => [
                'integer',
                'exists:poll_options,id',
            ],
            'points' => [
                'integer',
                'min:1',
            ],
            'text_response' => [
                'string',
                'max:1000',
            ],
            'voter_token' => [
                'string',
                'max:100',
            ],
        ];

        if ($poll && $poll->is_open_text) {
            $rules['option_id'] = ['nullable', 'integer', 'exists:poll_options,id'];
            $rules['text_response'][] = 'required_without:option_id';
        } else {
            $rules['option_id'][] = 'required';
        }

        if ($poll && $poll->max_points) {
            $rules['points'][] = 'max:'.$poll->max_points;
        }

        return $rules;
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

            $user = $this->user();

            if ($user && $poll->school_id && $user->schoolId() !== $poll->school_id) {
                $validator->errors()->add('poll', 'This poll is not available for your school.');
            }

            $optionId = $this->option_id;

            if ($optionId) {
                $option = PollOption::find($optionId);
                if ($option && $option->poll_id !== $poll->id) {
                    $validator->errors()->add('option_id', 'Selected option does not belong to this poll.');
                }
            }

            if ($user && $poll->votes()->where('student_id', $user->id)->exists()) {
                $validator->errors()->add('vote', 'You have already voted on this poll.');
            } elseif (! $user && $this->voter_token && $poll->votes()->where('voter_token', $this->voter_token)->exists()) {
                $validator->errors()->add('vote', 'You have already voted on this poll.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'option_id.required' => 'Please select an option.',
            'option_id.exists' => 'Selected option is invalid.',
            'text_response.required_without' => 'Please provide a response.',
            'text_response.max' => 'Response must not exceed 1000 characters.',
            'points.max' => 'Points exceed the maximum allowed for this poll.',
        ];
    }
}
