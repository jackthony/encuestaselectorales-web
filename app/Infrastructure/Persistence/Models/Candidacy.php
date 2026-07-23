<?php

namespace App\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Candidacy extends Model
{
    use HasUlids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $table = 'electoral_candidacies';

    protected $fillable = [
        'candidate_id',
        'political_party_id',
        'territory_id',
        'office_type',
        'election_cycle',
        'source_system',
        'source_key',
        'ballot_order',
        'status',
        'source_file',
        'source_row',
        'source_url',
        'retrieved_at',
    ];

    protected function casts(): array
    {
        return [
            'ballot_order' => 'integer',
            'source_row' => 'integer',
            'retrieved_at' => 'immutable_datetime',
        ];
    }

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Candidate::class, 'candidate_id', 'id');
    }

    public function politicalParty(): BelongsTo
    {
        return $this->belongsTo(PoliticalParty::class, 'political_party_id', 'id');
    }

    public function territory(): BelongsTo
    {
        return $this->belongsTo(Territory::class, 'territory_id', 'id');
    }

    public function surveyOptions(): HasMany
    {
        return $this->hasMany(SurveyOption::class, 'candidacy_id', 'id');
    }
}
