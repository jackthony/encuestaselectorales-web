<?php

namespace App\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PoliticalParty extends Model
{
    use HasUlids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $table = 'electoral_parties';

    protected $fillable = [
        'source_system',
        'source_key',
        'name',
        'acronym',
        'logo_url',
        'logo_storage_disk',
        'logo_storage_path',
        'logo_source_attribution',
        'source_url',
        'status',
    ];

    public function candidacies(): HasMany
    {
        return $this->hasMany(Candidacy::class, 'political_party_id', 'id');
    }
}
