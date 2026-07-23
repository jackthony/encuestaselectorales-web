<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class RegisterVoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'survey_round_id' => ['required', 'string', 'size:26'],
            'survey_option_id' => ['required', 'string', 'size:26'],
            'gps_latitude' => ['required', 'numeric', 'between:-90,90'],
            'gps_longitude' => ['required', 'numeric', 'between:-180,180'],
            'gps_accuracy_meters' => ['required', 'numeric', 'min:0', 'max:10000'],
            'interaction_time_ms' => ['required', 'integer', 'min:200', 'max:600000'],
            'browser_fingerprint' => ['required', 'string', 'min:16', 'max:512'],
            'device_token' => ['sometimes', 'nullable', 'string', 'min:32', 'max:128'],
        ];
    }
}
