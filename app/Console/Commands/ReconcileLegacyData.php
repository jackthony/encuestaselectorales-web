<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final class ReconcileLegacyData extends Command
{
    protected $signature = 'app:reconcile-legacy {--json : Emit machine-readable JSON}';

    protected $description = 'Compare legacy records, Laravel mappings, and normalized targets.';

    /** @var array<string, string> */
    private const TABLES = [
        'election_scopes' => 'electoral_territories',
        'political_organizations' => 'electoral_parties',
        'candidates' => 'electoral_candidates',
        'candidacies' => 'electoral_candidacies',
        'encuestas' => 'survey_rounds',
        'votos_interactivos' => 'interactive_votes',
    ];

    public function handle(): int
    {
        if (! Schema::hasTable('legacy_mappings')) {
            $this->error('legacy_mappings is unavailable. Run Laravel migrations first.');

            return self::FAILURE;
        }

        $report = [];
        $hasMismatch = false;

        foreach (self::TABLES as $source => $target) {
            if (! Schema::hasTable($source)) {
                $report[] = [
                    'source_table' => $source,
                    'target_table' => $target,
                    'status' => 'source_absent',
                    'source_rows' => 0,
                    'mapped_rows' => 0,
                    'verified_target_rows' => 0,
                    'unmapped_rows' => 0,
                ];

                continue;
            }

            $sourceRows = DB::table($source)->count();
            $mappedRows = DB::table('legacy_mappings')
                ->where('source_table', $source)
                ->where('target_table', $target)
                ->count();
            $verifiedTargetRows = Schema::hasTable($target)
                ? DB::table('legacy_mappings as mappings')
                    ->join("{$target} as target", 'target.id', '=', 'mappings.target_id')
                    ->where('mappings.source_table', $source)
                    ->where('mappings.target_table', $target)
                    ->count()
                : 0;
            $unmappedRows = max(0, $sourceRows - $verifiedTargetRows);
            $status = $unmappedRows === 0 ? 'matched' : 'mismatch';
            $hasMismatch = $hasMismatch || $status === 'mismatch';

            $report[] = compact(
                'source',
                'target',
                'status',
                'sourceRows',
                'mappedRows',
                'verifiedTargetRows',
                'unmappedRows',
            );
        }

        if ($this->option('json')) {
            $this->line(json_encode([
                'status' => $hasMismatch ? 'mismatch' : 'matched',
                'generated_at' => now()->toIso8601String(),
                'tables' => $report,
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        } else {
            $rows = array_map(static fn (array $row): array => [
                $row['source_table'] ?? $row['source'],
                $row['target_table'] ?? $row['target'],
                $row['source_rows'] ?? $row['sourceRows'],
                $row['mapped_rows'] ?? $row['mappedRows'],
                $row['verified_target_rows'] ?? $row['verifiedTargetRows'],
                $row['unmapped_rows'] ?? $row['unmappedRows'],
                $row['status'],
            ], $report);

            $this->table(
                ['Legacy', 'Laravel', 'Source', 'Mapped', 'Verified', 'Unmapped', 'Status'],
                $rows,
            );
        }

        return $hasMismatch ? self::FAILURE : self::SUCCESS;
    }
}
