<?php

namespace App\Infrastructure\Persistence\Models;

use App\Domain\Catalog\TerritoryType;
use App\Domain\Survey\PublicationState;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Territory extends Model
{
    use HasUlids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $table = 'electoral_territories';

    protected $fillable = [
        'official_code',
        'scope_type',
        'name',
        'canonical_name',
        'slug',
        'parent_id',
        'source_system',
        'source_key',
        'publication_state',
        'published_at',
        'source_url',
    ];

    protected function casts(): array
    {
        return [
            'scope_type' => TerritoryType::class,
            'publication_state' => PublicationState::class,
            'published_at' => 'immutable_datetime',
        ];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id', 'id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id', 'id');
    }

    public function candidacies(): HasMany
    {
        return $this->hasMany(Candidacy::class, 'territory_id', 'id');
    }

    public function surveyRounds(): HasMany
    {
        return $this->hasMany(SurveyRound::class, 'territory_id', 'id');
    }

    public function validatedVotes(): HasMany
    {
        return $this->hasMany(InteractiveVote::class, 'validated_territory_id', 'id');
    }

    public function importRuns(): HasMany
    {
        return $this->hasMany(ImportRun::class, 'territory_id', 'id');
    }
}
