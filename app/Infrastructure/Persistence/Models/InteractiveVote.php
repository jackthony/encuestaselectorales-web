<?php

namespace App\Infrastructure\Persistence\Models;

use App\Domain\Vote\VoteType;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InteractiveVote extends Model
{
    use HasUlids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'survey_round_id',
        'survey_option_id',
        'validated_territory_id',
        'vote_type',
        'gps_latitude',
        'gps_longitude',
        'gps_accuracy_meters',
        'geo_validation_method',
        'geo_validation_result',
        'interaction_time_ms',
        'ip_ciphertext',
        'ip_nonce',
        'ip_auth_tag',
        'ip_encryption_key_version',
        'ip_hmac',
        'ip_hmac_key_version',
        'device_token_hmac',
        'device_hmac_key_version',
        'browser_fingerprint_hmac',
        'browser_hmac_key_version',
        'status',
    ];

    protected $hidden = [
        'ip_ciphertext',
        'ip_nonce',
        'ip_auth_tag',
        'ip_hmac',
        'device_token_hmac',
        'browser_fingerprint_hmac',
    ];

    protected function casts(): array
    {
        return [
            'vote_type' => VoteType::class,
            'gps_latitude' => 'decimal:8',
            'gps_longitude' => 'decimal:8',
            'gps_accuracy_meters' => 'decimal:2',
            'interaction_time_ms' => 'integer',
            'ip_encryption_key_version' => 'integer',
            'ip_hmac_key_version' => 'integer',
            'device_hmac_key_version' => 'integer',
            'browser_hmac_key_version' => 'integer',
        ];
    }

    public function surveyRound(): BelongsTo
    {
        return $this->belongsTo(SurveyRound::class, 'survey_round_id', 'id');
    }

    public function surveyOption(): BelongsTo
    {
        return $this->belongsTo(SurveyOption::class, 'survey_option_id', 'id');
    }

    public function validatedTerritory(): BelongsTo
    {
        return $this->belongsTo(Territory::class, 'validated_territory_id', 'id');
    }
}
