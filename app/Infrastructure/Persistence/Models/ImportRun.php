<?php

namespace App\Infrastructure\Persistence\Models;

use App\Domain\Import\ImportStatus;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ImportRun extends Model
{
    use HasUlids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'territory_id',
        'source_system',
        'source_identity',
        'source_checksum',
        'mapping_version',
        'election_cycle',
        'office_type',
        'source_file',
        'source_size_bytes',
        'operator_identifier',
        'status',
        'total_rows',
        'created_rows',
        'updated_rows',
        'unchanged_rows',
        'rejected_rows',
        'failure_summary',
        'started_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'source_size_bytes' => 'integer',
            'status' => ImportStatus::class,
            'total_rows' => 'integer',
            'created_rows' => 'integer',
            'updated_rows' => 'integer',
            'unchanged_rows' => 'integer',
            'rejected_rows' => 'integer',
            'started_at' => 'immutable_datetime',
            'completed_at' => 'immutable_datetime',
        ];
    }

    public function territory(): BelongsTo
    {
        return $this->belongsTo(Territory::class, 'territory_id', 'id');
    }

    public function rows(): HasMany
    {
        return $this->hasMany(ImportRow::class, 'import_run_id', 'id');
    }
}
