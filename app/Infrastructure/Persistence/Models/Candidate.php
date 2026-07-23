<?php

namespace App\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Candidate extends Model
{
    use HasUlids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $table = 'electoral_candidates';

    protected $fillable = [
        'source_system',
        'source_key',
        'full_name',
        'photo_url',
        'photo_storage_disk',
        'photo_storage_path',
        'photo_source_attribution',
        'source_url',
        'status',
    ];

    public function candidacies(): HasMany
    {
        return $this->hasMany(Candidacy::class, 'candidate_id', 'id');
    }
}
