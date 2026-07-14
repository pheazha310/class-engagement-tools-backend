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
        ];

        if ($poll && $poll->is_open_text) {
            $rules['option_id'] = ['nullable', 'integer', 'exists:poll_options,id'];
            $rules['text_response'][] = 'required_without:option_id';
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

            $optionId = $this->option_id;

            if ($optionId) {
                $option = PollOption::find($optionId);
                if ($option && $option->poll_id !== $poll->id) {
                    $validator->errors()->add('option_id', 'Selected option does not belong to this poll.');
                }
            }

            if ($this->user() && $poll->votes()->where('student_id', $this->user()->id)->exists()) {
                $validator->errors()->add('vote', 'You have already voted on this poll.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'option_id.exists' => 'Selected option is invalid.',
            'text_response.required_without' => 'Please provide a response.',
            'text_response.max' => 'Response must not exceed 1000 characters.',
            'points.max' => 'Points exceed the maximum allowed for this poll.',
        ];
    }
}
