<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class LegacyVoteRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if ($this->has('candidato_id')) {
            $this->merge(['candidato_id' => (string) $this->input('candidato_id')]);
        }
    }

    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'ubigeo_votacion' => ['required', 'string', 'max:64'],
            'tipo_voto' => ['required', 'in:candidato'],
            'candidato_id' => ['required', 'string', 'max:64'],
            'gps_lat' => ['required', 'numeric', 'between:-90,90'],
            'gps_lng' => ['required', 'numeric', 'between:-180,180'],
            'gps_accuracy_meters' => ['required', 'numeric', 'min:0', 'max:10000'],
            'interaction_time_ms' => ['required', 'integer', 'min:200', 'max:600000'],
            'browser_fingerprint' => ['required', 'string', 'min:16', 'max:512'],
            'device_token' => ['sometimes', 'nullable', 'string', 'min:32', 'max:128'],
        ];
    }
}
