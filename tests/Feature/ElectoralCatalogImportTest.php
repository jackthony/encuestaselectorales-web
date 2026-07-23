<?php

namespace Tests\Feature;

use App\Application\Import\Data\CatalogImportOptions;
use App\Application\Import\Exceptions\CatalogImportException;
use App\Infrastructure\Import\TransactionalElectoralCatalogImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class ElectoralCatalogImportTest extends TestCase
{
    use RefreshDatabase;

    private const HEADERS = [
        'schema_version',
        'election_process_code',
        'election_level',
        'office_code',
        'region_ubigeo',
        'region_name',
        'province_ubigeo',
        'province_name',
        'district_ubigeo',
        'district_name',
        'scope_uid',
        'organization_uid',
        'organization_name',
        'candidate_uid',
        'candidate_full_name',
        'candidacy_uid',
        'candidacy_status',
        'source_system',
        'Link Logo Partido',
        'Link Foto Candidato',
        'Foto Adicional',
    ];

    public function test_first_csv_import_maps_media_and_identical_rerun_is_idempotent(): void
    {
        $path = $this->csv([
            $this->validRow(),
        ]);

        $first = $this->importer()->import($path, new CatalogImportOptions);
        $second = $this->importer()->import($path, new CatalogImportOptions);

        $this->assertSame(1, $first->createdRows);
        $this->assertTrue($second->runs[0]['idempotent']);
        $this->assertDatabaseCount('electoral_parties', 1);
        $this->assertDatabaseCount('electoral_candidates', 1);
        $this->assertDatabaseCount('electoral_candidacies', 1);
        $this->assertDatabaseHas('electoral_territories', [
            'scope_type' => 'province',
            'official_code' => '150100',
        ]);
        $this->assertDatabaseHas('electoral_candidacies', [
            'source_key' => 'CAD-JNE-1',
            'office_type' => 'provincial_mayor',
            'status' => 'active',
        ]);
        $this->assertDatabaseCount('import_runs', 1);
        $this->assertDatabaseHas('electoral_parties', [
            'source_key' => 'ORG-JNE-4',
            'logo_url' => 'https://jne.test/simbolo-correcto.png',
        ]);
        $this->assertDatabaseHas('electoral_candidates', [
            'source_key' => 'CAN-JNE-1',
            'photo_url' => 'https://jne.test/foto-candidato.jpg',
        ]);
        $this->assertDatabaseMissing('electoral_parties', [
            'logo_url' => 'https://dead.test/logo-legacy.jpg',
        ]);
    }

    public function test_it_normalizes_supported_jne_office_codes(): void
    {
        $regional = $this->validRow();
        $regional['election_level'] = 'REGIONAL';
        $regional['office_code'] = 'GOBERNADOR_REGIONAL';
        $regional['region_ubigeo'] = '070000';
        $regional['region_name'] = 'CALLAO';
        $regional['province_name'] = '';
        $regional['scope_uid'] = 'SCP-CALLAO-REGION';
        $regional['candidate_uid'] = 'CAN-JNE-REGIONAL';
        $regional['candidacy_uid'] = 'CAD-JNE-REGIONAL';

        $this->importer()->import(
            $this->csv([$this->validRow(), $regional]),
            new CatalogImportOptions,
        );

        $this->assertDatabaseHas('electoral_candidacies', [
            'source_key' => 'CAD-JNE-1',
            'office_type' => 'provincial_mayor',
        ]);
        $this->assertDatabaseHas('electoral_candidacies', [
            'source_key' => 'CAD-JNE-REGIONAL',
            'office_type' => 'regional_governor',
        ]);
    }

    public function test_it_rejects_unknown_or_scope_incompatible_offices(): void
    {
        $unknown = $this->validRow();
        $unknown['office_code'] = '999';

        $this->assertImportFailsWith($unknown, 'cargo electoral es obligatorio');

        $incompatible = $this->validRow();
        $incompatible['election_level'] = 'REGIONAL';
        $incompatible['office_code'] = 'ALCALDE_PROVINCIAL';
        $incompatible['region_ubigeo'] = '070000';
        $incompatible['region_name'] = 'CALLAO';
        $incompatible['province_name'] = '';
        $incompatible['scope_uid'] = 'SCP-CALLAO-REGION';

        $this->assertImportFailsWith($incompatible, 'no corresponde al ámbito region');
    }

    public function test_it_requires_a_supported_candidacy_status(): void
    {
        $missing = $this->validRow();
        $missing['candidacy_status'] = '';
        $this->assertImportFailsWith($missing, 'candidacy_status es obligatorio');

        $unknown = $this->validRow();
        $unknown['candidacy_status'] = 'ESTADO INVENTADO';
        $this->assertImportFailsWith($unknown, 'candidacy_status es obligatorio');
    }

    public function test_it_maps_received_jne_status_to_pending_instead_of_active(): void
    {
        $row = $this->validRow();
        $row['candidacy_status'] = 'RECIBIDO';

        $this->importer()->import($this->csv([$row]), new CatalogImportOptions);

        $this->assertDatabaseHas('electoral_candidacies', [
            'source_key' => 'CAD-JNE-1',
            'status' => 'pending',
        ]);
        $this->assertDatabaseMissing('electoral_candidacies', [
            'source_key' => 'CAD-JNE-1',
            'status' => 'active',
        ]);
    }

    public function test_it_rejects_malformed_or_incoherent_ubigeos(): void
    {
        $malformed = $this->validRow();
        $malformed['province_ubigeo'] = '15010';
        $this->assertImportFailsWith($malformed, 'exactamente seis dígitos');

        $incoherent = $this->validRow();
        $incoherent['region_ubigeo'] = '140000';
        $incoherent['province_ubigeo'] = '150100';
        $this->assertImportFailsWith($incoherent, 'regional no es coherente');

        $district = $this->validDistrictRow();
        $district['province_ubigeo'] = '150200';
        $this->assertImportFailsWith($district, 'provincial no es coherente');
    }

    public function test_idempotency_is_mapping_version_aware(): void
    {
        $path = $this->csv([$this->validRow()]);

        $this->importer()->import($path, new CatalogImportOptions);
        $candidacyId = DB::table('electoral_candidacies')
            ->where('source_key', 'CAD-JNE-1')
            ->value('id');
        DB::table('import_runs')->update(['mapping_version' => 'electoral-catalog-v1']);

        $summary = $this->importer()->import($path, new CatalogImportOptions);

        $this->assertFalse($summary->runs[0]['idempotent']);
        $this->assertDatabaseCount('import_runs', 2);
        $this->assertDatabaseHas('import_runs', ['mapping_version' => 'electoral-catalog-v1']);
        $this->assertDatabaseHas('import_runs', ['mapping_version' => 'electoral-catalog-v2']);
        $this->assertDatabaseCount('electoral_candidacies', 1);
        $this->assertSame(
            $candidacyId,
            DB::table('electoral_candidacies')->where('source_key', 'CAD-JNE-1')->value('id'),
        );
    }

    public function test_published_snapshot_deactivates_removed_candidacies_in_its_exact_scope(): void
    {
        $removed = $this->validRow();
        $removed['candidate_uid'] = 'CAN-JNE-REMOVED';
        $removed['candidate_full_name'] = 'CANDIDATO RETIRADO DEL SNAPSHOT';
        $removed['candidacy_uid'] = 'CAD-JNE-REMOVED';

        $pending = $this->validRow();
        $pending['candidate_uid'] = 'CAN-JNE-PENDING';
        $pending['candidate_full_name'] = 'CANDIDATO PENDIENTE RETIRADO';
        $pending['candidacy_uid'] = 'CAD-JNE-PENDING';
        $pending['candidacy_status'] = 'RECIBIDO';

        $this->importer()->import(
            $this->csv([$this->validRow(), $removed, $pending]),
            new CatalogImportOptions(publish: true),
        );

        $district = $this->validDistrictRow();
        $district['candidate_uid'] = 'CAN-JNE-DISTRICT';
        $district['candidate_full_name'] = 'CANDIDATO DISTRITAL';
        $district['candidacy_uid'] = 'CAD-JNE-DISTRICT';
        $this->importer()->import(
            $this->csv([$district]),
            new CatalogImportOptions(publish: true),
        );

        $this->importer()->import(
            $this->csv([$this->validRow()]),
            new CatalogImportOptions(publish: true),
        );

        $this->assertDatabaseHas('electoral_candidacies', [
            'source_key' => 'CAD-JNE-1',
            'status' => 'active',
        ]);
        $this->assertDatabaseHas('electoral_candidacies', [
            'source_key' => 'CAD-JNE-REMOVED',
            'status' => 'inactive',
        ]);
        $this->assertDatabaseHas('electoral_candidacies', [
            'source_key' => 'CAD-JNE-PENDING',
            'status' => 'inactive',
        ]);
        $this->assertDatabaseHas('electoral_candidacies', [
            'source_key' => 'CAD-JNE-DISTRICT',
            'status' => 'active',
        ]);
    }

    public function test_draft_or_rejected_import_does_not_deactivate_missing_candidacies(): void
    {
        $second = $this->validRow();
        $second['candidate_uid'] = 'CAN-JNE-SECOND';
        $second['candidate_full_name'] = 'SEGUNDO CANDIDATO';
        $second['candidacy_uid'] = 'CAD-JNE-SECOND';

        $this->importer()->import(
            $this->csv([$this->validRow(), $second]),
            new CatalogImportOptions(publish: true),
        );
        $this->importer()->import(
            $this->csv([$this->validRow()]),
            new CatalogImportOptions,
        );

        $this->assertDatabaseHas('electoral_candidacies', [
            'source_key' => 'CAD-JNE-SECOND',
            'status' => 'active',
        ]);

        $invalid = $this->validRow();
        $invalid['candidate_uid'] = 'CAN-JNE-INVALID';
        $invalid['candidate_full_name'] = '';
        $invalid['candidacy_uid'] = 'CAD-JNE-INVALID';
        $this->importer()->import(
            $this->csv([$this->validRow(), $invalid]),
            new CatalogImportOptions(publish: true),
        );

        $this->assertDatabaseHas('electoral_candidacies', [
            'source_key' => 'CAD-JNE-SECOND',
            'status' => 'active',
        ]);
    }

    public function test_invalid_row_is_diagnosed_without_creating_partial_candidacy(): void
    {
        $invalid = $this->validRow();
        $invalid['candidate_full_name'] = '';
        $invalid['candidate_uid'] = 'CAN-JNE-INVALID';
        $invalid['candidacy_uid'] = 'CAD-JNE-INVALID';

        $summary = $this->importer()->import(
            $this->csv([$this->validRow(), $invalid]),
            new CatalogImportOptions,
        );

        $this->assertSame(2, $summary->totalRows);
        $this->assertSame(1, $summary->createdRows);
        $this->assertSame(1, $summary->rejectedRows);
        $this->assertDatabaseCount('electoral_candidacies', 1);
        $this->assertDatabaseHas('import_rows', [
            'source_row_number' => 3,
            'status' => 'rejected',
        ]);
        $diagnostics = DB::table('import_rows')
            ->where('source_row_number', 3)
            ->value('diagnostics');
        $this->assertStringContainsString('nombre del candidato', (string) $diagnostics);
    }

    public function test_database_failure_rolls_back_catalog_entities_and_marks_run_failed(): void
    {
        $failureInjected = false;
        DB::connection()->beforeExecuting(static function (string $query) use (&$failureInjected): void {
            if (! $failureInjected && str_contains($query, 'electoral_candidacies')) {
                $failureInjected = true;
                throw new \RuntimeException('Injected candidacy write failure.');
            }
        });

        try {
            $this->importer()->import($this->csv([$this->validRow()]), new CatalogImportOptions);
            $this->fail('The injected persistence failure was not propagated.');
        } catch (CatalogImportException $exception) {
            $this->assertStringContainsString('fue revertido', $exception->getMessage());
        }

        $this->assertDatabaseCount('electoral_parties', 0);
        $this->assertDatabaseCount('electoral_candidates', 0);
        $this->assertDatabaseCount('electoral_candidacies', 0);
        $this->assertDatabaseHas('import_runs', ['status' => 'failed']);
    }

    private function importer(): TransactionalElectoralCatalogImporter
    {
        return $this->app->make(TransactionalElectoralCatalogImporter::class);
    }

    /**
     * @return array<string, string>
     */
    private function validRow(): array
    {
        return [
            'schema_version' => '1.0',
            'election_process_code' => 'ERM2026',
            'election_level' => 'PROVINCIAL',
            'office_code' => 'ALCALDE_PROVINCIAL',
            'region_ubigeo' => '150000',
            'region_name' => 'LIMA',
            'province_ubigeo' => '',
            'province_name' => 'LIMA',
            'district_ubigeo' => '',
            'district_name' => '',
            'scope_uid' => 'SCP-LIMA-PROVINCIA',
            'organization_uid' => 'ORG-JNE-4',
            'organization_name' => 'ACCION POPULAR',
            'candidate_uid' => 'CAN-JNE-1',
            'candidate_full_name' => 'CANDIDATO VERIFICADO',
            'candidacy_uid' => 'CAD-JNE-1',
            'candidacy_status' => 'ADMITIDO',
            'source_system' => 'JNE',
            'Link Logo Partido' => 'https://dead.test/logo-legacy.jpg',
            'Link Foto Candidato' => 'https://jne.test/simbolo-correcto.png',
            'Foto Adicional' => 'https://jne.test/foto-candidato.jpg',
        ];
    }

    /**
     * @return array<string, string>
     */
    private function validDistrictRow(): array
    {
        return array_replace($this->validRow(), [
            'election_level' => 'DISTRITAL',
            'office_code' => 'ALCALDE_DISTRITAL',
            'province_ubigeo' => '150100',
            'province_name' => 'LIMA',
            'district_ubigeo' => '150101',
            'district_name' => 'LIMA',
            'scope_uid' => 'SCP-LIMA-DISTRITO',
        ]);
    }

    /**
     * @param  array<string, string>  $row
     */
    private function assertImportFailsWith(array $row, string $diagnostic): void
    {
        try {
            $this->importer()->import($this->csv([$row]), new CatalogImportOptions);
            $this->fail('The invalid catalog row was accepted.');
        } catch (CatalogImportException $exception) {
            $messages = $exception->getMessage().' '.implode(' ', $exception->diagnostics);
            $this->assertStringContainsString($diagnostic, $messages);
        }
    }

    /**
     * @param  list<array<string, string>>  $rows
     */
    private function csv(array $rows): string
    {
        $temporaryPath = tempnam(sys_get_temp_dir(), 'electoral-catalog-');
        $path = $temporaryPath.'.csv';
        rename($temporaryPath, $path);
        $handle = fopen($path, 'wb');
        fputcsv($handle, self::HEADERS);

        foreach ($rows as $row) {
            fputcsv($handle, array_map(
                static fn (string $header): string => $row[$header] ?? '',
                self::HEADERS,
            ));
        }

        fclose($handle);

        return $path;
    }
}
