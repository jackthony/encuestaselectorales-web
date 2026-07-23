<?php

namespace App\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImportRow extends Model
{
    use HasUlids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'import_run_id',
        'source_row_number',
        'source_key',
        'status',
        'action',
        'entity_type',
        'entity_id',
        'normalized_payload',
        'diagnostics',
        'message',
    ];

    protected function casts(): array
    {
        return [
            'source_row_number' => 'integer',
            'normalized_payload' => 'array',
            'diagnostics' => 'array',
        ];
    }

    public function importRun(): BelongsTo
    {
        return $this->belongsTo(ImportRun::class, 'import_run_id', 'id');
    }
}
