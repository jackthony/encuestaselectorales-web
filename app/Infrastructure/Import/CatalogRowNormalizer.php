<?php

namespace App\Infrastructure\Import;

use App\Application\Import\Data\CatalogImportOptions;
use App\Application\Import\Data\NormalizedCatalogRow;
use App\Application\Import\Data\SourceRecord;
use App\Application\Import\Data\StagedCatalogRow;
use Illuminate\Support\Str;

final class CatalogRowNormalizer
{
    private const OFFICE_SCOPE = [
        'regional_governor' => 'region',
        'provincial_mayor' => 'province',
        'district_mayor' => 'district',
    ];

    public function stage(SourceRecord $source, CatalogImportOptions $options): StagedCatalogRow
    {
        $row = $source->values;
        $level = strtoupper($this->value($row, ['election_level', 'Nivel Elección', 'Nivel de Elección', 'Nivel']) ?? '');
        $scopeType = match ($level) {
            'REGIONAL', 'REGION', 'REGIÓN' => 'region',
            'PROVINCIAL', 'PROVINCE', 'PROVINCIA' => 'province',
            'DISTRITAL', 'DISTRICT', 'DISTRITO' => 'district',
            default => null,
        };

        $officeType = $this->officeType(
            $this->value($row, ['office_code', 'Cargo', 'cargo', 'office_name']),
        );
        $cycle = $options->sourceCycle
            ?? $this->value($row, ['election_process_code', 'Proceso Electoral', 'election_cycle']);
        $sourceSystem = $this->value($row, ['source_system', 'Fuente']) ?? $options->sourceSystem;

        [$territoryName, $officialCode] = $this->territory($row, $scopeType);
        $territorySourceKey = $this->value($row, ['scope_uid']);
        if ($territorySourceKey === null && $scopeType !== null && $territoryName !== null) {
            $territorySourceKey = $this->stableKey('scope', [$scopeType, $territoryName]);
        }
        $officialCode = $this->resolveApprovedOfficialCode(
            $row,
            $scopeType,
            $territoryName,
            $officialCode,
        );
        [$regionCode, $regionName, $provinceCode, $provinceName] = $this->ancestry(
            $row,
            $scopeType,
            $officialCode,
        );

        $batchKey = null;
        if ($scopeType !== null && $territorySourceKey !== null && $officeType !== null && $cycle !== null) {
            $batchKey = implode('|', [$sourceSystem, $territorySourceKey, $officeType, $cycle]);
        }

        $partyName = $this->value($row, [
            'organization_name',
            'Organización Política',
            'Organizacion Politica',
            'Partido',
            'Nombre Partido',
        ]);
        $candidateName = $this->value($row, [
            'candidate_full_name',
            'Nombre Candidato',
            'Candidato',
            'Nombres y Apellidos',
        ]);
        $candidacyStatus = $this->candidacyStatus(
            $this->value($row, ['candidacy_status', 'Estado']),
        );

        $diagnostics = [];
        $this->require($diagnostics, $scopeType, 'election_level es obligatorio y debe ser REGIONAL, PROVINCIAL o DISTRITAL.');
        $this->require($diagnostics, $territoryName, 'El nombre del territorio es obligatorio.');
        $this->require($diagnostics, $territorySourceKey, 'No se pudo resolver una identidad estable para el territorio.');
        $this->require($diagnostics, $officialCode, 'El territorio requiere ubigeo o scope_uid.');
        if ($scopeType !== 'region') {
            $this->require($diagnostics, $regionCode, 'La región requiere ubigeo para evitar territorios homónimos.');
            $this->require($diagnostics, $regionName, 'La región es obligatoria para provincia o distrito.');
        }
        if ($scopeType === 'district') {
            $this->require($diagnostics, $provinceCode, 'La provincia requiere ubigeo para evitar distritos homónimos.');
            $this->require($diagnostics, $provinceName, 'La provincia es obligatoria para un distrito.');
        }
        $this->require($diagnostics, $officeType, 'El cargo electoral es obligatorio.');
        if ($officeType !== null && $scopeType !== null && self::OFFICE_SCOPE[$officeType] !== $scopeType) {
            $diagnostics[] = "El cargo {$officeType} no corresponde al ámbito {$scopeType}.";
        }
        $this->require($diagnostics, $cycle, 'El ciclo electoral es obligatorio.');
        $this->require($diagnostics, $partyName, 'El nombre del partido es obligatorio.');
        $this->require($diagnostics, $candidateName, 'El nombre del candidato es obligatorio.');
        $this->require(
            $diagnostics,
            $candidacyStatus,
            'candidacy_status es obligatorio y debe usar un estado electoral soportado.',
        );
        $this->validateGeography($diagnostics, $row, $scopeType);
        $this->validateUbigeos(
            $diagnostics,
            $scopeType,
            $officialCode,
            $regionCode,
            $provinceCode,
        );

        if ($batchKey === null || $diagnostics !== []) {
            return new StagedCatalogRow($source, null, $diagnostics, $batchKey);
        }

        $partySourceKey = $this->value($row, ['organization_uid'])
            ?? $this->stableKey('party', [$sourceSystem, $partyName]);
        $candidateSourceKey = $this->value($row, ['candidate_uid', 'jne_candidate_id', 'DNI'])
            ?? $this->stableKey('candidate', [$sourceSystem, $candidateName]);
        $candidacySourceKey = $this->value($row, ['candidacy_uid'])
            ?? $this->stableKey('candidacy', [
                $candidateSourceKey,
                $partySourceKey,
                $territorySourceKey,
                $officeType,
                $cycle,
            ]);

        // The legacy source named these columns incorrectly. Never use Link Logo Partido.
        $partyLogo = $this->mediaUrl($this->value($row, ['party_logo_url', 'Link Foto Candidato']));
        $candidatePhoto = $this->mediaUrl($this->value($row, ['candidate_photo_url', 'Foto Adicional']));

        $normalized = new NormalizedCatalogRow(
            sourceRowNumber: $source->rowNumber,
            sourceSystem: $sourceSystem,
            electionCycle: $cycle,
            scopeType: $scopeType,
            territorySourceKey: $territorySourceKey,
            territoryOfficialCode: $officialCode,
            territoryName: $territoryName,
            regionOfficialCode: $regionCode,
            regionName: $regionName,
            provinceOfficialCode: $provinceCode,
            provinceName: $provinceName,
            officeType: $officeType,
            partySourceKey: $partySourceKey,
            partyName: $partyName,
            partyAcronym: $this->value($row, ['organization_abbreviation', 'Siglas']),
            partyLogoUrl: $partyLogo,
            partySourceUrl: $this->mediaUrl($this->value($row, ['organization_profile_url'])),
            candidateSourceKey: $candidateSourceKey,
            candidateName: $candidateName,
            candidatePhotoUrl: $candidatePhoto,
            candidateSourceUrl: $this->mediaUrl($this->value($row, ['candidate_profile_url'])),
            candidacySourceKey: $candidacySourceKey,
            ballotOrder: $this->integer($this->value($row, ['ballot_order', 'Orden'])),
            candidacyStatus: $candidacyStatus,
            sourceFile: $this->value($row, ['source_file']),
            declaredSourceRow: $this->integer($this->value($row, ['source_row'])),
            sourceUrl: $this->mediaUrl($this->value($row, ['source_url'])),
            retrievedAt: $this->dateTime($this->value($row, ['retrieved_at'])),
        );

        return new StagedCatalogRow($source, $normalized, [], $normalized->batchKey());
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array{0:?string,1:?string}
     */
    private function territory(array $row, ?string $scopeType): array
    {
        return match ($scopeType) {
            'region' => [
                $this->value($row, ['region_name', 'Región', 'Region']),
                $this->value($row, ['region_ubigeo', 'Ubigeo Región', 'Ubigeo Region']),
            ],
            'province' => [
                $this->value($row, ['province_name', 'Provincia']),
                $this->value($row, ['province_ubigeo', 'Ubigeo Provincia']),
            ],
            'district' => [
                $this->value($row, ['district_name', 'Distrito']),
                $this->value($row, ['district_ubigeo', 'Ubigeo Distrito']),
            ],
            default => [null, null],
        };
    }

    /**
     * @param  list<string>  $diagnostics
     * @param  array<string, mixed>  $row
     */
    private function validateGeography(array &$diagnostics, array $row, ?string $scopeType): void
    {
        $province = $this->value($row, ['province_name', 'Provincia']);
        $district = $this->value($row, ['district_name', 'Distrito']);

        if ($scopeType === 'region' && ($province !== null || $district !== null)) {
            $diagnostics[] = 'Una fila regional no puede declarar provincia ni distrito.';
        }
        if ($scopeType === 'province' && ($province === null || $district !== null)) {
            $diagnostics[] = 'Una fila provincial requiere provincia y no puede declarar distrito.';
        }
        if ($scopeType === 'district' && ($province === null || $district === null)) {
            $diagnostics[] = 'Una fila distrital requiere provincia y distrito.';
        }
    }

    /**
     * @param  list<string>  $diagnostics
     */
    private function validateUbigeos(
        array &$diagnostics,
        ?string $scopeType,
        ?string $officialCode,
        ?string $regionCode,
        ?string $provinceCode,
    ): void {
        foreach ([
            'territorio' => $officialCode,
            'región' => $regionCode,
            'provincia' => $provinceCode,
        ] as $label => $code) {
            if ($code !== null && preg_match('/^\d{6}$/', $code) !== 1) {
                $diagnostics[] = "El ubigeo de {$label} debe contener exactamente seis dígitos.";
            }
        }

        if ($scopeType === null || $officialCode === null || preg_match('/^\d{6}$/', $officialCode) !== 1) {
            return;
        }

        $expectedRegion = substr($officialCode, 0, 2).'0000';
        $expectedProvince = substr($officialCode, 0, 4).'00';

        if ($scopeType === 'region' && substr($officialCode, 2) !== '0000') {
            $diagnostics[] = 'El ubigeo regional debe terminar en 0000.';
        }

        if ($scopeType === 'province' && (substr($officialCode, 2) === '0000' || substr($officialCode, 4) !== '00')) {
            $diagnostics[] = 'El ubigeo provincial debe identificar una provincia y terminar en 00.';
        }

        if ($scopeType === 'district' && substr($officialCode, 4) === '00') {
            $diagnostics[] = 'El ubigeo distrital debe identificar un distrito y no puede terminar en 00.';
        }

        if ($scopeType !== 'region' && $regionCode !== null && $regionCode !== $expectedRegion) {
            $diagnostics[] = 'El ubigeo regional no es coherente con el ubigeo del territorio.';
        }

        if ($scopeType === 'province' && $provinceCode !== null && $provinceCode !== $officialCode) {
            $diagnostics[] = 'El ubigeo provincial no coincide con el territorio provincial.';
        }

        if ($scopeType === 'district' && $provinceCode !== null && $provinceCode !== $expectedProvince) {
            $diagnostics[] = 'El ubigeo provincial no es coherente con el ubigeo distrital.';
        }
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array{0:?string,1:?string,2:?string,3:?string}
     */
    private function ancestry(array $row, ?string $scopeType, ?string $officialCode): array
    {
        $regionName = $this->value($row, ['region_name', 'Región', 'Region']);
        $provinceName = $this->value($row, ['province_name', 'Provincia']);
        $regionCode = $this->value($row, ['region_ubigeo', 'Ubigeo Región', 'Ubigeo Region']);
        $provinceCode = $this->value($row, ['province_ubigeo', 'Ubigeo Provincia']);

        if ($officialCode !== null && preg_match('/^\d{6}$/', $officialCode) === 1) {
            $regionCode ??= substr($officialCode, 0, 2).'0000';
            if ($scopeType === 'province') {
                $provinceCode ??= $officialCode;
            } elseif ($scopeType === 'district') {
                $provinceCode ??= substr($officialCode, 0, 4).'00';
            }
        }

        return [$regionCode, $regionName, $provinceCode, $provinceName];
    }

    /**
     * @param  list<string>  $diagnostics
     */
    private function require(array &$diagnostics, mixed $value, string $message): void
    {
        if ($value === null || $value === '') {
            $diagnostics[] = $message;
        }
    }

    /**
     * @param  array<string, mixed>  $row
     * @param  list<string>  $aliases
     */
    private function value(array $row, array $aliases): ?string
    {
        foreach ($aliases as $alias) {
            if (! array_key_exists($alias, $row) || is_array($row[$alias]) || is_object($row[$alias])) {
                continue;
            }

            $value = trim((string) $row[$alias]);
            if ($value !== '') {
                return $value;
            }
        }

        return null;
    }

    private function stableKey(string $namespace, array $parts): string
    {
        $normalized = array_map(
            static fn (mixed $part): string => Str::lower(Str::ascii(trim((string) $part))),
            $parts,
        );

        return $namespace.'-'.substr(hash('sha256', implode('|', $normalized)), 0, 32);
    }

    private function mediaUrl(?string $value): ?string
    {
        if ($value === null || filter_var($value, FILTER_VALIDATE_URL) === false) {
            return null;
        }

        $scheme = strtolower((string) parse_url($value, PHP_URL_SCHEME));

        return in_array($scheme, ['http', 'https'], true) ? $value : null;
    }

    private function integer(?string $value): ?int
    {
        return $value !== null && preg_match('/^\d+$/', $value) === 1 ? (int) $value : null;
    }

    private function dateTime(?string $value): ?string
    {
        if ($value === null || strtotime($value) === false) {
            return null;
        }

        return date('Y-m-d H:i:s', strtotime($value));
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function resolveApprovedOfficialCode(
        array $row,
        ?string $scopeType,
        ?string $territoryName,
        ?string $officialCode,
    ): ?string {
        if ($officialCode !== null) {
            return $officialCode;
        }

        $territory = strtoupper(Str::ascii($territoryName ?? ''));
        $region = strtoupper(Str::ascii($this->value($row, ['region_name', 'Región', 'Region']) ?? ''));

        return match (true) {
            $scopeType === 'region' && $territory === 'CALLAO' => '070000',
            $scopeType === 'province' && $territory === 'LIMA' && $region === 'LIMA' => '150100',
            default => null,
        };
    }

    private function candidacyStatus(?string $value): ?string
    {
        $normalized = $this->canonicalToken($value);

        return match ($normalized) {
            'ADMITIDO', 'INSCRITO', 'APTO', 'ACTIVE' => 'active',
            // En esta línea de trabajo, RECIBIDO ya cuenta como elegible para la ronda 1.
            'RECIBIDO' => 'active',
            'EXCLUIDO', 'RETIRADO', 'IMPROCEDENTE', 'INADMISIBLE',
            'TACHA_FUNDADA', 'INACTIVE' => 'inactive',
            'PENDIENTE', 'EN_TRAMITE', 'SOLICITUD_PRESENTADA', 'PENDING' => 'pending',
            default => null,
        };
    }

    private function officeType(?string $value): ?string
    {
        $normalized = $this->canonicalToken($value);

        return match ($normalized) {
            'REGIONAL_GOVERNOR', 'GOBERNADOR_REGIONAL' => 'regional_governor',
            'PROVINCIAL_MAYOR', 'ALCALDE_PROVINCIAL' => 'provincial_mayor',
            'DISTRICT_MAYOR', 'ALCALDE_DISTRITAL' => 'district_mayor',
            default => null,
        };
    }

    private function canonicalToken(?string $value): string
    {
        if ($value === null) {
            return '';
        }

        $normalized = preg_replace(
            '/[^A-Z0-9]+/',
            '_',
            strtoupper(Str::ascii(trim($value))),
        );

        return trim((string) $normalized, '_');
    }
}
