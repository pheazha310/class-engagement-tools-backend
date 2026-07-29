<?php

namespace App\Http\Requests;

use App\Models\Poll;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function poll(): ?Poll
    {
        $poll = $this->route('poll');

        if ($poll instanceof Poll) {
            return $poll;
        }

        $token = $this->route('token');

        if (is_string($token) && $token !== '') {
            return Poll::byPublicToken($token)->first();
        }

        if (is_string($poll) && $poll !== '') {
            return Poll::find($poll);
        }

        return null;
    }

    public function rules(): array
    {
        $poll = $this->poll();

        $optionRules = ['required', 'string'];

        if ($poll instanceof Poll) {
            $optionRules[] = Rule::exists('poll_options', 'id')->where('poll_id', $poll->id);
        } else {
            $optionRules[] = 'exists:poll_options,id';
        }

        $pointsRules = ['nullable', 'integer', 'min:1', 'max:5'];

        if ($poll?->poll_type === Poll::POLL_TYPE_RATING) {
            $pointsRules = ['required', 'integer', 'min:1', 'max:5'];
        }

        return [
            'option_id' => $optionRules,
            'points' => $pointsRules,
            'guest_token' => ['nullable', 'string', 'max:64'],
        ];
    }
}
