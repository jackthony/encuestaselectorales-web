<?php

namespace App\Infrastructure\Persistence\Models;

use App\Domain\Survey\PublicationState;
use App\Domain\Survey\RoundAvailability;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SurveyRound extends Model
{
    use HasUlids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'territory_id',
        'round_number',
        'election_cycle',
        'survey_type',
        'office_type',
        'title',
        'opens_at',
        'closes_at',
        'publication_state',
        'readiness_state',
        'blocked_reason',
        'source_system',
        'source_key',
        'source_url',
    ];

    protected function casts(): array
    {
        return [
            'round_number' => 'integer',
            'opens_at' => 'immutable_datetime',
            'closes_at' => 'immutable_datetime',
            'publication_state' => PublicationState::class,
            'readiness_state' => RoundAvailability::class,
        ];
    }

    public function territory(): BelongsTo
    {
        return $this->belongsTo(Territory::class, 'territory_id', 'id');
    }

    public function options(): HasMany
    {
        return $this->hasMany(SurveyOption::class, 'survey_round_id', 'id');
    }

    public function votes(): HasMany
    {
        return $this->hasMany(InteractiveVote::class, 'survey_round_id', 'id');
    }
}
