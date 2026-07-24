<?php

namespace App\Infrastructure\Import;

use App\Application\Import\Data\CatalogImportOptions;
use App\Application\Import\Data\CatalogImportSummary;
use App\Application\Import\Data\NormalizedCatalogRow;
use App\Application\Import\Data\StagedCatalogRow;
use App\Application\Import\Exceptions\CatalogImportException;
use App\Infrastructure\Persistence\Models\Candidacy;
use App\Infrastructure\Persistence\Models\Candidate;
use App\Infrastructure\Persistence\Models\ImportRow;
use App\Infrastructure\Persistence\Models\ImportRun;
use App\Infrastructure\Persistence\Models\PoliticalParty;
use App\Infrastructure\Persistence\Models\Territory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

final readonly class TransactionalElectoralCatalogImporter
{
    public function __construct(
        private VersionedCatalogReader $reader,
        private CatalogRowNormalizer $normalizer,
    ) {}

    public function import(string $path, CatalogImportOptions $options): CatalogImportSummary
    {
        $document = $this->reader->read($path);
        $staged = array_map(
            fn ($record): StagedCatalogRow => $this->normalizer->stage($record, $options),
            $document->records,
        );

        $unscoped = array_filter($staged, static fn (StagedCatalogRow $row): bool => $row->batchKey === null);
        if ($unscoped !== []) {
            $diagnostics = [];
            foreach ($unscoped as $row) {
                $diagnostics[] = 'Fila '.$row->source->rowNumber.': '.implode(' ', $row->diagnostics);
            }
            throw new CatalogImportException(
                'Hay filas sin territorio, cargo o ciclo suficientes para crear un lote auditable.',
                $diagnostics,
            );
        }

        /** @var array<string, list<StagedCatalogRow>> $groups */
        $groups = [];
        foreach ($staged as $row) {
            $groups[$row->batchKey][] = $row;
        }

        foreach ($groups as $rows) {
            if (array_filter($rows, static fn (StagedCatalogRow $row): bool => $row->normalized !== null) !== []) {
                continue;
            }

            $diagnostics = [];
            foreach ($rows as $row) {
                $diagnostics[] = 'Fila '.$row->source->rowNumber.': '.implode(' ', $row->diagnostics);
            }

            throw new CatalogImportException(
                'El lote no contiene filas válidas y no se importó.',
                $diagnostics,
            );
        }

        $runSummaries = [];
        foreach ($groups as $rows) {
            $runSummaries[] = $options->dryRun
                ? $this->dryRunSummary($rows)
                : $this->importGroup($document, $rows, $options);
        }

        return new CatalogImportSummary(
            checksum: $document->checksum,
            dryRun: $options->dryRun,
            totalRows: array_sum(array_column($runSummaries, 'total_rows')),
            createdRows: array_sum(array_column($runSummaries, 'created_rows')),
            updatedRows: array_sum(array_column($runSummaries, 'updated_rows')),
            unchangedRows: array_sum(array_column($runSummaries, 'unchanged_rows')),
            rejectedRows: array_sum(array_column($runSummaries, 'rejected_rows')),
            runs: $runSummaries,
        );
    }

    /**
     * @param  list<StagedCatalogRow>  $rows
     * @return array<string, mixed>
     */
    private function dryRunSummary(array $rows): array
    {
        $first = $this->firstNormalized($rows);
        $rejected = count(array_filter($rows, static fn (StagedCatalogRow $row): bool => ! $row->isValid()));

        return [
            'run_id' => null,
            'territory' => $first->territoryName,
            'scope_type' => $first->scopeType,
            'office_type' => $first->officeType,
            'election_cycle' => $first->electionCycle,
            'status' => 'dry-run',
            'idempotent' => false,
            'total_rows' => count($rows),
            'created_rows' => count($rows) - $rejected,
            'updated_rows' => 0,
            'unchanged_rows' => 0,
            'rejected_rows' => $rejected,
        ];
    }

    /**
     * @param  list<StagedCatalogRow>  $rows
     * @return array<string, mixed>
     */
    private function importGroup(object $document, array $rows, CatalogImportOptions $options): array
    {
        $first = $this->firstNormalized($rows);
        $territory = $this->resolveTerritory($first);

        $existing = ImportRun::query()
            ->where('source_system', $first->sourceSystem)
            ->where('source_checksum', $document->checksum)
            ->where('mapping_version', $document->mappingVersion)
            ->where('territory_id', $territory->id)
            ->where('office_type', $first->officeType)
            ->where('election_cycle', $first->electionCycle)
            ->first();

        if (
            $existing?->status?->value === 'completed'
            && (! $options->publish || $territory->publication_state === 'published')
        ) {
            return $this->summaryFromRun($existing, true, $first);
        }

        $run = $existing ?? new ImportRun;
        $run->fill([
            'territory_id' => $territory->id,
            'source_system' => $first->sourceSystem,
            'source_identity' => $first->territorySourceKey.'|'.$first->officeType,
            'source_checksum' => $document->checksum,
            'mapping_version' => $document->mappingVersion,
            'election_cycle' => $first->electionCycle,
            'office_type' => $first->officeType,
            'source_file' => basename($document->path),
            'source_size_bytes' => $document->sizeBytes,
            'operator_identifier' => $options->operatorIdentifier,
            'status' => 'running',
            'total_rows' => count($rows),
            'created_rows' => 0,
            'updated_rows' => 0,
            'unchanged_rows' => 0,
            'rejected_rows' => 0,
            'failure_summary' => null,
            'started_at' => now(),
            'completed_at' => null,
        ]);
        $run->save();
        $run->rows()->delete();

        try {
            DB::transaction(function () use ($run, $rows, $territory, $options): void {
                $counts = [
                    'created_rows' => 0,
                    'updated_rows' => 0,
                    'unchanged_rows' => 0,
                    'rejected_rows' => 0,
                ];

                foreach ($rows as $staged) {
                    if (! $staged->isValid()) {
                        $counts['rejected_rows']++;
                        $this->recordRejectedRow($run, $staged);

                        continue;
                    }

                    $action = $this->reconcileRow($staged->normalized, $territory);
                    $counts[$action.'_rows']++;
                    $this->recordAcceptedRow($run, $staged, $action);
                }

                if ($options->publish && $counts['rejected_rows'] === 0) {
                    $this->reconcileRemovedCandidacies($rows, $territory);
                    $territory->forceFill([
                        'publication_state' => 'published',
                        'published_at' => $territory->published_at ?? now(),
                    ])->save();
                }

                $run->forceFill($counts + [
                    'status' => 'completed',
                    'completed_at' => now(),
                ])->save();
            }, 3);
        } catch (Throwable $exception) {
            $run->forceFill([
                'status' => 'failed',
                'failure_summary' => Str::limit($exception->getMessage(), 1000, ''),
                'completed_at' => now(),
            ])->save();

            throw new CatalogImportException(
                "El lote {$run->id} fue revertido por un error de persistencia.",
                [$exception->getMessage()],
            );
        }

        return $this->summaryFromRun($run->fresh(), false, $first);
    }

    private function resolveTerritory(NormalizedCatalogRow $row): Territory
    {
        $region = $row->scopeType === 'region'
            ? null
            : $this->resolveAncestor(
                'region',
                $row->regionOfficialCode,
                $row->regionName,
                null,
                $row->sourceSystem,
            );
        $province = $row->scopeType === 'district'
            ? $this->resolveAncestor(
                'province',
                $row->provinceOfficialCode,
                $row->provinceName,
                $region?->id,
                $row->sourceSystem,
            )
            : null;

        $territory = Territory::query()
            ->where('source_system', $row->sourceSystem)
            ->where('source_key', $row->territorySourceKey)
            ->first();

        $territory ??= Territory::query()
            ->where('scope_type', $row->scopeType)
            ->where('official_code', $row->territoryOfficialCode)
            ->first();

        $territory ??= new Territory;
        $territory->fill([
            'official_code' => $row->territoryOfficialCode,
            'scope_type' => $row->scopeType,
            'name' => $row->territoryName,
            'canonical_name' => Str::lower(Str::ascii($row->territoryName)),
            'slug' => Str::slug($row->territoryName).'-'.$row->scopeType,
            'parent_id' => $province?->id ?? $region?->id,
            'source_system' => $territory->source_system ?: $row->sourceSystem,
            'source_key' => $territory->source_key ?: $row->territorySourceKey,
            'publication_state' => $territory->publication_state ?: 'draft',
        ]);
        $territory->save();

        return $territory;
    }

    private function resolveAncestor(
        string $scopeType,
        ?string $officialCode,
        ?string $name,
        ?string $parentId,
        string $sourceSystem,
    ): ?Territory {
        if ($officialCode === null || $name === null) {
            return null;
        }

        $territory = Territory::query()
            ->where('scope_type', $scopeType)
            ->where('official_code', $officialCode)
            ->first() ?? new Territory;
        $territory->fill([
            'official_code' => $officialCode,
            'scope_type' => $scopeType,
            'name' => $name,
            'canonical_name' => Str::lower(Str::ascii($name)),
            'slug' => Str::slug($name).'-'.$scopeType,
            'parent_id' => $parentId,
            'source_system' => $territory->source_system ?: $sourceSystem,
            'source_key' => $territory->source_key ?: "territory:{$scopeType}:{$officialCode}",
            'publication_state' => $territory->publication_state ?: 'draft',
        ]);
        $territory->save();

        return $territory;
    }

    private function reconcileRow(NormalizedCatalogRow $row, Territory $territory): string
    {
        [$party, $partyAction] = $this->upsert(
            PoliticalParty::class,
            $row->sourceSystem,
            $row->partySourceKey,
            [
                'name' => $row->partyName,
                'acronym' => $row->partyAcronym,
                'logo_url' => $row->partyLogoUrl,
                'logo_source_attribution' => $row->partyLogoUrl ? $row->sourceSystem : null,
                'source_url' => $row->partySourceUrl,
                'status' => 'active',
            ],
        );
        [$candidate, $candidateAction] = $this->upsert(
            Candidate::class,
            $row->sourceSystem,
            $row->candidateSourceKey,
            [
                'full_name' => $row->candidateName,
                'photo_url' => $row->candidatePhotoUrl,
                'photo_source_attribution' => $row->candidatePhotoUrl ? $row->sourceSystem : null,
                'source_url' => $row->candidateSourceUrl,
                'status' => 'active',
            ],
        );
        [, $candidacyAction] = $this->upsert(
            Candidacy::class,
            $row->sourceSystem,
            $row->candidacySourceKey,
            [
                'candidate_id' => $candidate->id,
                'political_party_id' => $party->id,
                'territory_id' => $territory->id,
                'office_type' => $row->officeType,
                'election_cycle' => $row->electionCycle,
                'ballot_order' => $row->ballotOrder,
                'status' => strtolower($row->candidacyStatus),
                'source_file' => $row->sourceFile,
                'source_row' => $row->declaredSourceRow,
                'source_url' => $row->sourceUrl,
                'retrieved_at' => $row->retrievedAt,
            ],
        );

        $actions = [$partyAction, $candidateAction, $candidacyAction];
        if (in_array('created', $actions, true)) {
            return 'created';
        }

        return in_array('updated', $actions, true) ? 'updated' : 'unchanged';
    }

    /**
     * Published imports are authoritative snapshots for one source, territory, office and cycle.
     *
     * @param  list<StagedCatalogRow>  $rows
     */
    private function reconcileRemovedCandidacies(array $rows, Territory $territory): void
    {
        $first = $this->firstNormalized($rows);
        $sourceKeys = array_values(array_map(
            static fn (StagedCatalogRow $row): string => $row->normalized->candidacySourceKey,
            array_filter($rows, static fn (StagedCatalogRow $row): bool => $row->isValid()),
        ));

        Candidacy::query()
            ->where('source_system', $first->sourceSystem)
            ->where('territory_id', $territory->id)
            ->where('office_type', $first->officeType)
            ->where('election_cycle', $first->electionCycle)
            ->whereIn('status', ['active', 'pending'])
            ->whereNotIn('source_key', $sourceKeys)
            ->update([
                'status' => 'inactive',
                'updated_at' => now(),
            ]);
    }

    /**
     * @param  class-string<Model>  $modelClass
     * @param  array<string, mixed>  $attributes
     * @return array{0:Model,1:string}
     */
    private function upsert(string $modelClass, string $sourceSystem, string $sourceKey, array $attributes): array
    {
        /** @var Model $model */
        $model = $modelClass::query()->firstOrNew([
            'source_system' => $sourceSystem,
            'source_key' => $sourceKey,
        ]);
        $created = ! $model->exists;
        $model->fill($attributes);
        $updated = $model->isDirty();
        $model->save();

        return [$model, $created ? 'created' : ($updated ? 'updated' : 'unchanged')];
    }

    private function recordRejectedRow(ImportRun $run, StagedCatalogRow $staged): void
    {
        ImportRow::query()->create([
            'import_run_id' => $run->id,
            'source_row_number' => $staged->source->rowNumber,
            'source_key' => null,
            'status' => 'rejected',
            'action' => null,
            'entity_type' => 'candidacy',
            'normalized_payload' => null,
            'diagnostics' => $staged->diagnostics,
            'message' => implode(' ', $staged->diagnostics),
        ]);
    }

    private function recordAcceptedRow(ImportRun $run, StagedCatalogRow $staged, string $action): void
    {
        ImportRow::query()->create([
            'import_run_id' => $run->id,
            'source_row_number' => $staged->source->rowNumber,
            'source_key' => $staged->normalized->candidacySourceKey,
            'status' => 'accepted',
            'action' => $action,
            'entity_type' => 'candidacy',
            'entity_id' => Candidacy::query()
                ->where('source_system', $staged->normalized->sourceSystem)
                ->where('source_key', $staged->normalized->candidacySourceKey)
                ->value('id'),
            'normalized_payload' => $staged->normalized->toArray(),
            'diagnostics' => [],
            'message' => null,
        ]);
    }

    /**
     * @param  list<StagedCatalogRow>  $rows
     */
    private function firstNormalized(array $rows): NormalizedCatalogRow
    {
        foreach ($rows as $row) {
            if ($row->normalized !== null) {
                return $row->normalized;
            }
        }

        throw new CatalogImportException('El lote no contiene ninguna fila válida que identifique su territorio.');
    }

    /**
     * @return array<string, mixed>
     */
    private function summaryFromRun(
        ImportRun $run,
        bool $idempotent,
        NormalizedCatalogRow $first,
    ): array {
        return [
            'run_id' => $run->id,
            'territory' => $first->territoryName,
            'scope_type' => $first->scopeType,
            'office_type' => $first->officeType,
            'election_cycle' => $first->electionCycle,
            'status' => $run->status->value,
            'idempotent' => $idempotent,
            'total_rows' => $run->total_rows,
            'created_rows' => $run->created_rows,
            'updated_rows' => $run->updated_rows,
            'unchanged_rows' => $run->unchanged_rows,
            'rejected_rows' => $run->rejected_rows,
        ];
    }
}
