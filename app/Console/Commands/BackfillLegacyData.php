<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Throwable;

class BackfillLegacyData extends Command
{
    protected $signature = 'legacy:backfill
        {--dry-run : Execute the complete backfill and roll it back}
        {--batch=500 : Number of legacy rows processed per transaction}';

    protected $description = 'Backfill normalized Laravel tables from restartable legacy data mappings';

    /**
     * @var array<string, array{read:int,created:int,existing:int,skipped:int}>
     */
    private array $stats = [];

    /**
     * @var array<int, string>
     */
    private array $skipMessages = [];

    public function handle(): int
    {
        $batchSize = filter_var($this->option('batch'), FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1, 'max_range' => 10000],
        ]);

        if ($batchSize === false) {
            $this->error('--batch must be an integer between 1 and 10000.');

            return self::FAILURE;
        }

        $legacyTables = [
            'election_scopes',
            'political_organizations',
            'candidates',
            'candidacies',
            'encuestas',
            'votos_interactivos',
        ];
        $availableTables = array_values(array_filter(
            $legacyTables,
            static fn (string $table): bool => Schema::hasTable($table)
        ));

        if ($availableTables === []) {
            $this->info('No legacy tables were found; nothing to backfill.');

            return self::SUCCESS;
        }

        $dryRun = (bool) $this->option('dry-run');

        if ($dryRun) {
            DB::beginTransaction();
            $this->warn('Dry run enabled: all writes will be rolled back.');
        }

        try {
            $this->backfill($availableTables, $batchSize, $dryRun);

            if ($dryRun) {
                DB::rollBack();
            }
        } catch (Throwable $exception) {
            if ($dryRun && DB::transactionLevel() > 0) {
                DB::rollBack();
            }

            $this->error('Legacy backfill failed: '.$exception->getMessage());

            return self::FAILURE;
        }

        $this->renderReport($availableTables);

        return self::SUCCESS;
    }

    /**
     * @param  array<int, string>  $availableTables
     */
    private function backfill(array $availableTables, int $batchSize, bool $dryRun): void
    {
        if (in_array('election_scopes', $availableTables, true)) {
            foreach (['REGIONAL', 'PROVINCIAL', 'DISTRITAL'] as $level) {
                $this->processQuery(
                    'election_scopes',
                    DB::table('election_scopes')->where('election_level', $level),
                    $batchSize,
                    $dryRun,
                    fn (object $row): bool => $this->mapTerritory($row)
                );
            }
        }

        $this->processIfAvailable(
            'political_organizations',
            $availableTables,
            $batchSize,
            $dryRun,
            fn (object $row): bool => $this->mapParty($row)
        );
        $this->processIfAvailable(
            'candidates',
            $availableTables,
            $batchSize,
            $dryRun,
            fn (object $row): bool => $this->mapCandidate($row)
        );
        $this->processIfAvailable(
            'candidacies',
            $availableTables,
            $batchSize,
            $dryRun,
            fn (object $row): bool => $this->mapCandidacy($row)
        );
        $this->processIfAvailable(
            'encuestas',
            $availableTables,
            $batchSize,
            $dryRun,
            fn (object $row): bool => $this->mapSurveyRound($row)
        );

        if (
            in_array('encuestas', $availableTables, true)
            && in_array('candidacies', $availableTables, true)
        ) {
            $this->processQuery(
                'survey_options',
                DB::table('encuestas'),
                $batchSize,
                $dryRun,
                fn (object $row): bool => $this->mapSurveyOptions($row)
            );
        }

        $this->processIfAvailable(
            'votos_interactivos',
            $availableTables,
            $batchSize,
            $dryRun,
            fn (object $row): bool => $this->mapVote($row)
        );
    }

    /**
     * @param  array<int, string>  $availableTables
     */
    private function processIfAvailable(
        string $table,
        array $availableTables,
        int $batchSize,
        bool $dryRun,
        callable $mapper
    ): void {
        if (! in_array($table, $availableTables, true)) {
            return;
        }

        $this->processQuery($table, DB::table($table), $batchSize, $dryRun, $mapper);
    }

    private function processQuery(
        string $reportName,
        Builder $query,
        int $batchSize,
        bool $dryRun,
        callable $mapper
    ): void {
        $this->stats[$reportName] ??= [
            'read' => 0,
            'created' => 0,
            'existing' => 0,
            'skipped' => 0,
        ];

        $query->orderBy('id')->chunkById(
            $batchSize,
            function (Collection $rows) use ($reportName, $mapper, $dryRun): void {
                $runBatch = function () use ($rows, $reportName, $mapper): void {
                    foreach ($rows as $row) {
                        $this->stats[$reportName]['read']++;

                        try {
                            $created = $mapper($row);
                            $key = $created ? 'created' : 'existing';
                            $this->stats[$reportName][$key]++;
                        } catch (LegacyRowSkipped $exception) {
                            $this->stats[$reportName]['skipped']++;
                            $this->recordSkip($reportName, (string) $row->id, $exception->getMessage());
                        }
                    }
                };

                if ($dryRun) {
                    DB::transaction($runBatch);

                    return;
                }

                DB::transaction($runBatch, 3);
            },
            'id',
            'id'
        );
    }

    private function mapTerritory(object $row): bool
    {
        if ($this->mappedTarget('election_scopes', $row->id, 'electoral_territories')) {
            return false;
        }

        $scopeType = match (strtoupper((string) $row->election_level)) {
            'REGIONAL' => 'region',
            'PROVINCIAL' => 'province',
            'DISTRITAL' => 'district',
            default => throw new LegacyRowSkipped('unsupported election level'),
        };
        $codeColumn = $scopeType.'_ubigeo';
        $nameColumn = $scopeType.'_name';
        $officialCode = trim((string) ($row->{$codeColumn} ?? ''));
        $name = trim((string) ($row->{$nameColumn} ?? ''));

        if ($officialCode === '' || $name === '') {
            throw new LegacyRowSkipped('missing official territory code or name');
        }

        $sourceSystem = $this->valueOrDefault($row->source_system ?? null, 'legacy');
        $sourceKey = $this->valueOrDefault($row->source_key ?? null, 'election_scopes:'.$row->id);
        $targetId = DB::table('electoral_territories')
            ->where('source_system', $sourceSystem)
            ->where('source_key', $sourceKey)
            ->value('id');
        $targetId ??= DB::table('electoral_territories')
            ->where('scope_type', $scopeType)
            ->where('official_code', $officialCode)
            ->value('id');

        $created = $targetId === null;
        $targetId ??= (string) Str::ulid();
        $now = now();

        if ($created) {
            DB::table('electoral_territories')->insert([
                'id' => $targetId,
                'official_code' => $officialCode,
                'scope_type' => $scopeType,
                'name' => $name,
                'canonical_name' => Str::lower($name),
                'slug' => $this->valueOrDefault(
                    $row->territory_slug ?? null,
                    Str::slug($name).'-'.$scopeType
                ),
                'parent_id' => $this->resolveParentTerritory($row, $scopeType),
                'source_system' => $sourceSystem,
                'source_key' => $sourceKey,
                'publication_state' => 'published',
                'published_at' => $row->updated_at ?? $row->created_at ?? $now,
                'created_at' => $row->created_at ?? $now,
                'updated_at' => $row->updated_at ?? $now,
            ]);
        }

        $this->storeMapping('election_scopes', $row->id, 'electoral_territories', $targetId);

        return $created;
    }

    private function mapParty(object $row): bool
    {
        return $this->mapCatalogRecord(
            'political_organizations',
            $row,
            'electoral_parties',
            [
                'source_system' => $this->valueOrDefault($row->source_system ?? null, 'legacy'),
                'source_key' => $this->valueOrDefault(
                    $row->source_key ?? null,
                    'political_organizations:'.$row->id
                ),
                'name' => $this->required($row->organization_name ?? null, 'missing organization name'),
                'acronym' => $row->organization_abbreviation ?? null,
                'logo_url' => $row->party_logo_url ?? null,
                'logo_storage_path' => $row->party_logo_local_path ?? null,
                'source_url' => $row->organization_profile_url ?? null,
                'status' => 'active',
            ]
        );
    }

    private function mapCandidate(object $row): bool
    {
        return $this->mapCatalogRecord(
            'candidates',
            $row,
            'electoral_candidates',
            [
                'source_system' => $this->valueOrDefault($row->source_system ?? null, 'legacy'),
                'source_key' => $this->valueOrDefault($row->source_key ?? null, 'candidates:'.$row->id),
                'full_name' => $this->required(
                    $row->candidate_full_name ?? null,
                    'missing candidate name'
                ),
                'photo_url' => $row->candidate_photo_url ?? null,
                'photo_storage_path' => $row->candidate_photo_local_path ?? null,
                'source_url' => $row->candidate_profile_url ?? null,
                'status' => 'active',
            ]
        );
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function mapCatalogRecord(
        string $sourceTable,
        object $row,
        string $targetTable,
        array $attributes
    ): bool {
        if ($this->mappedTarget($sourceTable, $row->id, $targetTable)) {
            return false;
        }

        $targetId = DB::table($targetTable)
            ->where('source_system', $attributes['source_system'])
            ->where('source_key', $attributes['source_key'])
            ->value('id');
        $created = $targetId === null;
        $targetId ??= (string) Str::ulid();
        $now = now();

        if ($created) {
            DB::table($targetTable)->insert($attributes + [
                'id' => $targetId,
                'created_at' => $row->created_at ?? $now,
                'updated_at' => $row->updated_at ?? $now,
            ]);
        }

        $this->storeMapping($sourceTable, $row->id, $targetTable, $targetId);

        return $created;
    }

    private function mapCandidacy(object $row): bool
    {
        if ($this->mappedTarget('candidacies', $row->id, 'electoral_candidacies')) {
            return false;
        }

        $territoryId = $this->requireMapping(
            'election_scopes',
            $row->scope_id,
            'electoral_territories',
            'scope is not mapped'
        );
        $partyId = $this->requireMapping(
            'political_organizations',
            $row->organization_id,
            'electoral_parties',
            'organization is not mapped'
        );
        $candidateId = $this->requireMapping(
            'candidates',
            $row->candidate_id,
            'electoral_candidates',
            'candidate is not mapped'
        );
        $scope = DB::table('election_scopes')->where('id', $row->scope_id)->first();

        if (! $scope) {
            throw new LegacyRowSkipped('scope row is missing');
        }

        $officeType = $this->valueOrDefault(
            $scope->office_code ?? null,
            $this->required($scope->office_name ?? null, 'scope office is missing')
        );
        $electionCycle = $this->electionCycle($scope);
        $sourceSystem = $this->valueOrDefault($row->source_system ?? null, 'legacy');
        $sourceKey = $this->valueOrDefault($row->source_key ?? null, 'candidacies:'.$row->id);
        $targetId = DB::table('electoral_candidacies')
            ->where('source_system', $sourceSystem)
            ->where('source_key', $sourceKey)
            ->value('id');
        $targetId ??= DB::table('electoral_candidacies')
            ->where('candidate_id', $candidateId)
            ->where('political_party_id', $partyId)
            ->where('territory_id', $territoryId)
            ->where('office_type', $officeType)
            ->where('election_cycle', $electionCycle)
            ->value('id');
        $created = $targetId === null;
        $targetId ??= (string) Str::ulid();
        $now = now();

        if ($created) {
            DB::table('electoral_candidacies')->insert([
                'id' => $targetId,
                'candidate_id' => $candidateId,
                'political_party_id' => $partyId,
                'territory_id' => $territoryId,
                'office_type' => $officeType,
                'election_cycle' => $electionCycle,
                'source_system' => $sourceSystem,
                'source_key' => $sourceKey,
                'ballot_order' => $row->ballot_order ?? null,
                'status' => Str::limit(
                    $this->valueOrDefault($row->candidacy_status ?? null, 'active'),
                    30,
                    ''
                ),
                'source_file' => $row->source_file ?? null,
                'source_row' => $row->source_row ?? null,
                'source_url' => $row->source_url ?? null,
                'retrieved_at' => $row->retrieved_at ?? null,
                'created_at' => $row->created_at ?? $now,
                'updated_at' => $row->updated_at ?? $now,
            ]);
        }

        $this->storeMapping('candidacies', $row->id, 'electoral_candidacies', $targetId);

        return $created;
    }

    private function mapSurveyRound(object $row): bool
    {
        if ($this->mappedTarget('encuestas', $row->id, 'survey_rounds')) {
            return false;
        }

        $scope = $this->resolveSurveyScope($row);
        $territoryId = $this->requireMapping(
            'election_scopes',
            $scope->id,
            'electoral_territories',
            'survey scope is not mapped'
        );
        $officeType = $this->valueOrDefault(
            $scope->office_code ?? null,
            $this->required($scope->office_name ?? null, 'survey office is missing')
        );
        $electionCycle = $this->electionCycle($scope);
        $sourceKey = 'encuestas:'.$row->id;
        $targetId = DB::table('survey_rounds')
            ->where('source_system', 'legacy_bl13')
            ->where('source_key', $sourceKey)
            ->value('id');
        $created = $targetId === null;
        $targetId ??= (string) Str::ulid();
        $now = now();
        $hasCandidacies = DB::table('candidacies')
            ->where('scope_id', $scope->id)
            ->whereIn('id', DB::table('legacy_mappings')
                ->where('source_table', 'candidacies')
                ->where('target_table', 'electoral_candidacies')
                ->select('legacy_id'))
            ->exists();

        if ($created) {
            DB::table('survey_rounds')->insert([
                'id' => $targetId,
                'territory_id' => $territoryId,
                'round_number' => (int) ($row->numero_ronda ?? 1),
                'election_cycle' => $electionCycle,
                'survey_type' => ($row->tipo ?? null) === 'campo_externa'
                    ? 'external_field'
                    : 'online_owned',
                'office_type' => $officeType,
                'title' => $this->required($row->titulo ?? null, 'survey title is missing'),
                'opens_at' => $this->required(
                    $row->fecha_apertura ?? null,
                    'survey opening date is missing'
                ),
                'closes_at' => $this->required(
                    $row->fecha_cierre ?? null,
                    'survey closing date is missing'
                ),
                'publication_state' => ($row->estado_publicacion ?? null) === 'producción'
                    ? 'published'
                    : 'draft',
                'readiness_state' => $hasCandidacies ? 'active' : 'blocked',
                'blocked_reason' => $hasCandidacies ? null : 'candidate_data_unavailable',
                'source_system' => 'legacy_bl13',
                'source_key' => $sourceKey,
                'created_at' => $row->created_at ?? $now,
                'updated_at' => $row->created_at ?? $now,
            ]);
        }

        $this->storeMapping('encuestas', $row->id, 'survey_rounds', $targetId);

        return $created;
    }

    private function mapSurveyOptions(object $round): bool
    {
        $roundId = $this->mappedTarget('encuestas', $round->id, 'survey_rounds');

        if (! $roundId) {
            throw new LegacyRowSkipped('survey round is not mapped');
        }

        $scope = $this->resolveSurveyScope($round);
        $legacyCandidacies = DB::table('candidacies')
            ->where('scope_id', $scope->id)
            ->orderByRaw('CASE WHEN ballot_order IS NULL THEN 1 ELSE 0 END')
            ->orderBy('ballot_order')
            ->orderBy('id')
            ->get();

        if ($legacyCandidacies->isEmpty()) {
            throw new LegacyRowSkipped('scope has no candidacies');
        }

        $createdAny = false;
        $displayOrder = 0;

        foreach ($legacyCandidacies as $legacyCandidacy) {
            $displayOrder++;
            $candidacyId = $this->mappedTarget(
                'candidacies',
                $legacyCandidacy->id,
                'electoral_candidacies'
            );

            if (! $candidacyId) {
                $this->recordSkip(
                    'survey_options',
                    $round->id.':'.$legacyCandidacy->id,
                    'candidacy is not mapped'
                );

                continue;
            }

            $candidacy = DB::table('electoral_candidacies')->where('id', $candidacyId)->first();
            $targetRound = DB::table('survey_rounds')->where('id', $roundId)->first();

            if (
                ! $candidacy
                || ! $targetRound
                || $candidacy->territory_id !== $targetRound->territory_id
                || $candidacy->office_type !== $targetRound->office_type
                || $candidacy->election_cycle !== $targetRound->election_cycle
            ) {
                $this->recordSkip(
                    'survey_options',
                    $round->id.':'.$legacyCandidacy->id,
                    'candidacy does not match the round contract'
                );

                continue;
            }

            $legacyId = $round->id.':'.$legacyCandidacy->id;
            $optionId = $this->mappedTarget(
                'encuesta_candidacies',
                $legacyId,
                'survey_options'
            );

            if ($optionId) {
                continue;
            }

            $optionId = DB::table('survey_options')
                ->where('survey_round_id', $roundId)
                ->where('candidacy_id', $candidacyId)
                ->value('id');
            $isNew = $optionId === null;
            $optionId ??= (string) Str::ulid();

            if ($isNew) {
                DB::table('survey_options')->insert([
                    'id' => $optionId,
                    'survey_round_id' => $roundId,
                    'candidacy_id' => $candidacyId,
                    'display_order' => $legacyCandidacy->ballot_order ?: $displayOrder,
                    'status' => 'eligible',
                    'created_at' => $round->created_at ?? now(),
                    'updated_at' => $round->created_at ?? now(),
                ]);
                $createdAny = true;
            }

            $this->storeMapping(
                'encuesta_candidacies',
                $legacyId,
                'survey_options',
                $optionId
            );
        }

        if (DB::table('survey_options')->where('survey_round_id', $roundId)->exists()) {
            DB::table('survey_rounds')->where('id', $roundId)->update([
                'readiness_state' => 'active',
                'blocked_reason' => null,
                'updated_at' => now(),
            ]);
        }

        return $createdAny;
    }

    private function mapVote(object $row): bool
    {
        if ($this->mappedTarget('votos_interactivos', $row->id, 'interactive_votes')) {
            return false;
        }

        $roundId = $this->requireMapping(
            'encuestas',
            $row->encuesta_id,
            'survey_rounds',
            'survey round is not mapped'
        );
        $round = DB::table('survey_rounds')->where('id', $roundId)->first();

        if (! $round) {
            throw new LegacyRowSkipped('mapped survey round is missing');
        }

        if (($row->tipo_voto ?? null) !== 'candidato' || empty($row->candidato_id)) {
            throw new LegacyRowSkipped('non-candidate votes have no normalized survey option');
        }

        $candidateId = $this->requireMapping(
            'candidates',
            $row->candidato_id,
            'electoral_candidates',
            'candidate is not mapped'
        );
        $candidacyIds = DB::table('electoral_candidacies')
            ->where('candidate_id', $candidateId)
            ->where('territory_id', $round->territory_id)
            ->where('office_type', $round->office_type)
            ->where('election_cycle', $round->election_cycle)
            ->pluck('id');

        if ($candidacyIds->count() !== 1) {
            throw new LegacyRowSkipped('candidate does not resolve to one round candidacy');
        }

        $optionId = DB::table('survey_options')
            ->where('survey_round_id', $roundId)
            ->where('candidacy_id', $candidacyIds->first())
            ->value('id');

        if (! $optionId) {
            throw new LegacyRowSkipped('candidate survey option is not mapped');
        }

        $validatedTerritoryId = $this->resolveVoteTerritory(
            (string) $row->ubigeo_votacion,
            (string) $round->territory_id
        );

        if (($row->gps_accuracy_meters ?? null) === null) {
            throw new LegacyRowSkipped('GPS accuracy is missing');
        }

        if (($row->interaction_time_ms ?? null) === null) {
            throw new LegacyRowSkipped('interaction time is missing');
        }

        $duplicate = DB::table('interactive_votes')
            ->where('survey_round_id', $roundId)
            ->where(function (Builder $query) use ($row): void {
                $query->where('ip_hmac', $row->ip_hash);

                if (! empty($row->device_token)) {
                    $query->orWhere('device_token_hmac', $this->deviceHmac($row->device_token));
                }
            })
            ->exists();

        if ($duplicate) {
            throw new LegacyRowSkipped('normalized duplicate constraint already has this vote signal');
        }

        $targetId = (string) Str::ulid();
        DB::table('interactive_votes')->insert([
            'id' => $targetId,
            'survey_round_id' => $roundId,
            'survey_option_id' => $optionId,
            'validated_territory_id' => $validatedTerritoryId,
            'vote_type' => 'candidate',
            'gps_latitude' => $row->gps_lat,
            'gps_longitude' => $row->gps_lng,
            'gps_accuracy_meters' => $row->gps_accuracy_meters,
            'geo_validation_method' => 'legacy_bl14',
            'geo_validation_result' => 'legacy_validated',
            'interaction_time_ms' => $row->interaction_time_ms,
            'ip_ciphertext' => $row->ip_cifrada,
            'ip_nonce' => $row->ip_iv,
            'ip_auth_tag' => $row->ip_tag,
            'ip_encryption_key_version' => 1,
            'ip_hmac' => $row->ip_hash,
            'ip_hmac_key_version' => 1,
            'device_token_hmac' => $row->device_token
                ? $this->deviceHmac($row->device_token)
                : null,
            'device_hmac_key_version' => $row->device_token ? 1 : null,
            'browser_fingerprint_hmac' => null,
            'browser_hmac_key_version' => null,
            'status' => match ($row->estado ?? null) {
                'anulado' => 'voided',
                'sospechoso' => 'suspicious',
                default => 'accepted',
            },
            'created_at' => $row->created_at ?? now(),
            'updated_at' => $row->created_at ?? now(),
        ]);
        $this->storeMapping('votos_interactivos', $row->id, 'interactive_votes', $targetId);

        return true;
    }

    private function deviceHmac(string $deviceToken): string
    {
        $configured = (string) config('vote.device_hmac_key', '');
        $key = str_starts_with($configured, 'base64:')
            ? base64_decode(substr($configured, 7), true)
            : $configured;

        if ($key === false || strlen($key) < 32) {
            throw new LegacyRowSkipped('VOTE_DEVICE_HMAC_KEY is missing or invalid');
        }

        return hash_hmac('sha256', $deviceToken, $key);
    }

    private function resolveSurveyScope(object $survey): object
    {
        if (! Schema::hasTable('election_scopes')) {
            throw new LegacyRowSkipped('election_scopes table is absent');
        }

        $level = match (strtolower((string) ($survey->nivel ?? 'distrito'))) {
            'region' => 'REGIONAL',
            'provincia' => 'PROVINCIAL',
            'distrito' => 'DISTRITAL',
            default => throw new LegacyRowSkipped('unsupported survey level'),
        };
        $identifier = trim((string) ($survey->distrito_id ?? ''));
        $codeColumn = strtolower($level).'_ubigeo';
        $codeColumn = match ($level) {
            'REGIONAL' => 'region_ubigeo',
            'PROVINCIAL' => 'province_ubigeo',
            default => 'district_ubigeo',
        };
        $matches = DB::table('election_scopes')
            ->where('election_level', $level)
            ->where(function (Builder $query) use ($identifier, $codeColumn): void {
                $query->where('territory_slug', $identifier)
                    ->orWhere($codeColumn, $identifier);
            })
            ->get()
            ->filter(fn (object $scope): bool => (bool) $this->mappedTarget(
                'election_scopes',
                $scope->id,
                'electoral_territories'
            ))
            ->unique(fn (object $scope): string => implode('|', [
                (string) $this->mappedTarget(
                    'election_scopes',
                    $scope->id,
                    'electoral_territories'
                ),
                $this->valueOrDefault($scope->office_code ?? null, (string) $scope->office_name),
                $this->electionCycle($scope),
            ]))
            ->values();

        if ($matches->count() !== 1) {
            throw new LegacyRowSkipped(
                $matches->isEmpty()
                    ? 'no mapped election scope matches the survey'
                    : 'survey scope is ambiguous across offices or cycles'
            );
        }

        return $matches->first();
    }

    private function resolveParentTerritory(object $row, string $scopeType): ?string
    {
        if ($scopeType === 'region') {
            return null;
        }

        $parentLevel = $scopeType === 'province' ? 'REGIONAL' : 'PROVINCIAL';
        $parentCodeColumn = $scopeType === 'province' ? 'region_ubigeo' : 'province_ubigeo';
        $parentCode = trim((string) ($row->{$parentCodeColumn} ?? ''));

        if ($parentCode === '') {
            return null;
        }

        $targetIds = DB::table('election_scopes')
            ->where('election_level', $parentLevel)
            ->where($parentCodeColumn, $parentCode)
            ->pluck('id')
            ->map(fn (mixed $legacyId): ?string => $this->mappedTarget(
                'election_scopes',
                (string) $legacyId,
                'electoral_territories'
            ))
            ->filter()
            ->unique()
            ->values();

        return $targetIds->count() === 1 ? $targetIds->first() : null;
    }

    private function resolveVoteTerritory(string $legacyIdentifier, string $roundTerritoryId): string
    {
        if (! Schema::hasTable('election_scopes')) {
            throw new LegacyRowSkipped('election_scopes table is absent');
        }

        $targetIds = DB::table('election_scopes')
            ->where(function (Builder $query) use ($legacyIdentifier): void {
                $query->where('territory_slug', $legacyIdentifier)
                    ->orWhere('region_ubigeo', $legacyIdentifier)
                    ->orWhere('province_ubigeo', $legacyIdentifier)
                    ->orWhere('district_ubigeo', $legacyIdentifier);
            })
            ->pluck('id')
            ->map(fn (mixed $legacyId): ?string => $this->mappedTarget(
                'election_scopes',
                (string) $legacyId,
                'electoral_territories'
            ))
            ->filter()
            ->unique()
            ->values();

        if ($targetIds->count() !== 1 || $targetIds->first() !== $roundTerritoryId) {
            throw new LegacyRowSkipped('validated vote territory cannot be mapped to the round');
        }

        return $roundTerritoryId;
    }

    private function electionCycle(object $scope): string
    {
        return $this->valueOrDefault(
            $scope->election_process_code ?? null,
            (string) $this->required($scope->election_year ?? null, 'election cycle is missing')
        );
    }

    private function mappedTarget(
        string $sourceTable,
        mixed $legacyId,
        string $targetTable
    ): ?string {
        return DB::table('legacy_mappings')
            ->where('source_table', $sourceTable)
            ->where('legacy_id', (string) $legacyId)
            ->where('target_table', $targetTable)
            ->value('target_id');
    }

    private function requireMapping(
        string $sourceTable,
        mixed $legacyId,
        string $targetTable,
        string $message
    ): string {
        return $this->mappedTarget($sourceTable, $legacyId, $targetTable)
            ?? throw new LegacyRowSkipped($message);
    }

    private function storeMapping(
        string $sourceTable,
        mixed $legacyId,
        string $targetTable,
        string $targetId
    ): void {
        DB::table('legacy_mappings')->insertOrIgnore([
            'id' => (string) Str::ulid(),
            'source_table' => $sourceTable,
            'legacy_id' => (string) $legacyId,
            'target_table' => $targetTable,
            'target_id' => $targetId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function required(mixed $value, string $message): mixed
    {
        if ($value === null || trim((string) $value) === '') {
            throw new LegacyRowSkipped($message);
        }

        return $value;
    }

    private function valueOrDefault(mixed $value, string $default): string
    {
        $normalized = trim((string) $value);

        return $normalized === '' ? $default : $normalized;
    }

    private function recordSkip(string $table, string $legacyId, string $reason): void
    {
        if (count($this->skipMessages) < 50) {
            $this->skipMessages[] = "{$table}[{$legacyId}]: {$reason}";
        }
    }

    /**
     * @param  array<int, string>  $availableTables
     */
    private function renderReport(array $availableTables): void
    {
        $rows = [];

        foreach ($this->stats as $table => $stats) {
            $rows[] = [$table, $stats['read'], $stats['created'], $stats['existing'], $stats['skipped']];
        }

        $this->table(['Source', 'Read', 'Created', 'Existing', 'Skipped'], $rows);

        $missing = array_values(array_diff([
            'election_scopes',
            'political_organizations',
            'candidates',
            'candidacies',
            'encuestas',
            'votos_interactivos',
        ], $availableTables));

        if ($missing !== []) {
            $this->line('Absent legacy tables: '.implode(', ', $missing));
        }

        foreach ($this->skipMessages as $message) {
            $this->warn($message);
        }

        if (count($this->skipMessages) === 50) {
            $this->warn('Additional skipped rows were omitted from console output.');
        }
    }
}

class LegacyRowSkipped extends \RuntimeException {}
