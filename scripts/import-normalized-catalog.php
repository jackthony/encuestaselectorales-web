<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/helpers.php';

/**
 * Imports the normalized electoral catalog into MySQL and optionally seeds
 * the first public survey rounds.
 *
 * Usage:
 *   php scripts/import-normalized-catalog.php
 *   php scripts/import-normalized-catalog.php --source=_tmp_paquete_csv_estandar_elecciones/catalogo_candidaturas_erm2026.json
 *   php scripts/import-normalized-catalog.php --seed-rounds --replace-rounds
 *   php scripts/import-normalized-catalog.php --dry-run
 */

function stripBom(string $value): string
{
    return preg_replace('/^\xEF\xBB\xBF/', '', $value) ?? $value;
}

function normalizeText(?string $value): string
{
    $value = trim((string) $value);
    if ($value === '') {
        return '';
    }

    if (function_exists('mb_convert_case')) {
        $value = mb_convert_case(mb_strtolower($value, 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
    } else {
        $value = ucwords(strtolower($value));
    }

    return $value;
}

function normalizeNullableString(mixed $value): ?string
{
    if ($value === null) {
        return null;
    }

    if (is_bool($value)) {
        return $value ? '1' : '0';
    }

    if (is_array($value) || is_object($value)) {
        return null;
    }

    $value = trim((string) $value);
    return $value === '' ? null : $value;
}

function normalizeNullableInt(mixed $value): ?int
{
    $value = normalizeNullableString($value);
    if ($value === null || !preg_match('/^-?\d+$/', $value)) {
        return null;
    }

    return (int) $value;
}

function normalizeTerritorySlug(string $value): string
{
    $value = trim($value);
    if ($value === '') {
        return '';
    }

    $ascii = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
    if ($ascii === false) {
        $ascii = $value;
    }

    $ascii = strtolower($ascii);
    $ascii = preg_replace('/[^a-z0-9]+/', '-', $ascii) ?? $ascii;

    return trim($ascii, '-');
}

function stableUid(string $namespace, string $value): string
{
    return strtolower(substr(hash('sha256', $namespace . '|' . $value), 0, 32));
}

function resolvePdo(string $root): PDO
{
    $configFiles = [
        $root . '/config/db.php',
        dirname($root) . '/config/db.php',
    ];

    foreach ($configFiles as $configFile) {
        if (!is_file($configFile)) {
            continue;
        }

        $config = require $configFile;
        if (!is_array($config) || !isset($config['dsn'], $config['user'], $config['pass'])) {
            continue;
        }

        return new PDO(
            $config['dsn'],
            $config['user'],
            $config['pass'],
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );
    }

    $dsn = getenv('DB_DSN') ?: '';
    $user = getenv('DB_USER') ?: '';
    $pass = getenv('DB_PASS') ?: '';
    if ($dsn !== '' && $user !== '') {
        return new PDO(
            $dsn,
            $user,
            $pass,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );
    }

    throw new RuntimeException('No database config found. Create config/db.php or set DB_DSN, DB_USER and DB_PASS.');
}

function executeSqlFile(PDO $pdo, string $path): void
{
    if (!is_file($path)) {
        return;
    }

    $sql = stripBom((string) file_get_contents($path));
    $lines = preg_split('/\R/', $sql) ?: [];
    $buffer = [];

    foreach ($lines as $line) {
        $trimmed = ltrim($line);
        if ($trimmed === '' || str_starts_with($trimmed, '--')) {
            continue;
        }

        $buffer[] = $line;
    }

    $statementBlock = trim(implode("\n", $buffer));
    if ($statementBlock === '') {
        return;
    }

    $statements = preg_split('/;\s*(?:\R|$)/', $statementBlock) ?: [];
    foreach ($statements as $statement) {
        $statement = trim($statement);
        if ($statement === '') {
            continue;
        }

        $pdo->exec($statement);
    }
}

function loadJsonRows(string $path): array
{
    if (!is_file($path)) {
        return [];
    }

    $decoded = json_decode(stripBom((string) file_get_contents($path)), true);
    if (!is_array($decoded)) {
        return [];
    }

    if (isset($decoded['candidacies']) && is_array($decoded['candidacies'])) {
        return $decoded['candidacies'];
    }

    return array_is_list($decoded) ? $decoded : [];
}

function loadCsvRows(string $path): array
{
    if (!is_file($path)) {
        return [];
    }

    $handle = fopen($path, 'rb');
    if ($handle === false) {
        return [];
    }

    $rows = [];
    $headers = null;

    while (($line = fgetcsv($handle)) !== false) {
        if ($headers === null) {
            $headers = array_map(static fn ($header) => stripBom((string) $header), $line);
            continue;
        }

        $row = [];
        foreach ($headers as $index => $header) {
            $row[$header] = $line[$index] ?? '';
        }

        $rows[] = $row;
    }

    fclose($handle);

    return $rows;
}

function loadCatalogRows(string $sourcePath): array
{
    $extension = strtolower(pathinfo($sourcePath, PATHINFO_EXTENSION));

    return match ($extension) {
        'json' => loadJsonRows($sourcePath),
        'csv' => loadCsvRows($sourcePath),
        default => throw new RuntimeException('Unsupported source format. Use .json or .csv.'),
    };
}

function parseDateTimeOption(string $value, string $label): string
{
    $value = trim($value);
    $dt = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $value);
    if (!$dt || $dt->format('Y-m-d H:i:s') !== $value) {
        throw new RuntimeException("Invalid {$label}. Use YYYY-MM-DD HH:MM:SS.");
    }

    return $dt->format('Y-m-d H:i:s');
}

function surveyTitleForScope(string $level, string $slug): string
{
    $article = $level === 'distrito' ? 'del' : 'de la';

    return trim(
        'Encuesta web activa ' .
        $article . ' ' .
        surveyLevelLabel($level) . ' ' .
        territoryDisplayName($slug)
    );
}

function containsForbiddenMarker(array $row): bool
{
    foreach ($row as $value) {
        if (is_array($value)) {
            if (containsForbiddenMarker($value)) {
                return true;
            }
            continue;
        }

        if (!is_string($value)) {
            continue;
        }

        if (preg_match('/\b(ejemplo|fictici[oa]s?|demo|placeholder)\b/i', $value) === 1) {
            return true;
        }
    }

    return false;
}

function surveyLevelValueFromElectionLevel(string $electionLevel): string
{
    return match (strtoupper(trim($electionLevel))) {
        'REGIONAL' => 'region',
        'PROVINCIAL' => 'provincia',
        'DISTRITAL' => 'distrito',
        default => 'distrito',
    };
}

function normalizePackageRow(array $row, int $lineNumber): ?array
{
    $scopeUid = normalizeNullableString($row['scope_uid'] ?? null);
    $organizationUid = normalizeNullableString($row['organization_uid'] ?? null);
    $candidateUid = normalizeNullableString($row['candidate_uid'] ?? null);
    $candidacyUid = normalizeNullableString($row['candidacy_uid'] ?? null);
    $level = strtoupper(normalizeNullableString($row['election_level'] ?? null) ?? '');
    $officeCode = normalizeNullableString($row['office_code'] ?? null);
    $officeName = normalizeNullableString($row['office_name'] ?? null);

    if ($scopeUid === null || $organizationUid === null || $candidateUid === null || $candidacyUid === null) {
        return null;
    }

    if (!in_array($level, ['REGIONAL', 'PROVINCIAL', 'DISTRITAL'], true)) {
        return null;
    }

    if ($officeCode === null || $officeName === null) {
        return null;
    }

    if (containsForbiddenMarker($row)) {
        return null;
    }

    $regionName = normalizeText(normalizeNullableString($row['region_name'] ?? null) ?? '');
    $provinceName = normalizeNullableString($row['province_name'] ?? null);
    $districtName = normalizeNullableString($row['district_name'] ?? null);
    $regionUbigeo = normalizeNullableString($row['region_ubigeo'] ?? null);
    $provinceUbigeo = normalizeNullableString($row['province_ubigeo'] ?? null);
    $districtUbigeo = normalizeNullableString($row['district_ubigeo'] ?? null);

    if ($regionName === '') {
        return null;
    }

    if ($level === 'REGIONAL') {
        if ($provinceName !== null || $districtName !== null) {
            return null;
        }
    } elseif ($level === 'PROVINCIAL') {
        if ($provinceName === null || $districtName !== null) {
            return null;
        }
        $provinceName = normalizeText($provinceName);
    } elseif ($level === 'DISTRITAL') {
        if ($provinceName === null || $districtName === null) {
            return null;
        }
        $provinceName = normalizeText($provinceName);
        $districtName = normalizeText($districtName);
    }

    $territorySlugSource = match ($level) {
        'REGIONAL' => $regionName,
        'PROVINCIAL' => $provinceName ?? '',
        'DISTRITAL' => $districtName ?? '',
        default => '',
    };
    $territorySlug = normalizeTerritorySlug($territorySlugSource);

    if ($territorySlug === '') {
        return null;
    }

    return [
        'line_number' => $lineNumber,
        'scope' => [
            'id' => stableUid('scope', $scopeUid),
            'scope_uid' => $scopeUid,
            'source_system' => normalizeNullableString($row['source_system'] ?? null) ?? 'JNE',
            'source_key' => $scopeUid,
            'territory_slug' => $territorySlug,
            'election_process_code' => normalizeNullableString($row['election_process_code'] ?? null) ?? 'ERM2026',
            'election_year' => normalizeNullableInt($row['election_year'] ?? null) ?? 2026,
            'election_level' => $level,
            'office_code' => $officeCode,
            'office_name' => $officeName,
            'country_code' => normalizeNullableString($row['country_code'] ?? null) ?? 'PE',
            'country_name' => normalizeNullableString($row['country_name'] ?? null) ?? 'PERU',
            'region_ubigeo' => $regionUbigeo,
            'region_name' => $regionName,
            'province_ubigeo' => $level === 'REGIONAL' ? null : $provinceUbigeo,
            'province_name' => $level === 'REGIONAL' ? null : $provinceName,
            'district_ubigeo' => $level === 'DISTRITAL' ? $districtUbigeo : null,
            'district_name' => $level === 'DISTRITAL' ? $districtName : null,
        ],
        'organization' => [
            'id' => stableUid('organization', $organizationUid),
            'organization_uid' => $organizationUid,
            'source_system' => normalizeNullableString($row['source_system'] ?? null) ?? 'JNE',
            'source_key' => $organizationUid,
            'jne_organization_id' => normalizeNullableInt($row['jne_organization_id'] ?? null),
            'organization_name' => normalizeNullableString($row['organization_name'] ?? null) ?? '',
            'organization_abbreviation' => normalizeNullableString($row['organization_abbreviation'] ?? null),
            'organization_type' => normalizeNullableString($row['organization_type'] ?? null),
            'party_logo_url' => normalizeNullableString($row['party_logo_url'] ?? null),
            'party_logo_local_path' => normalizeNullableString($row['party_logo_local_path'] ?? null),
            'organization_profile_url' => normalizeNullableString($row['organization_profile_url'] ?? null),
            'legacy_party_logo_url' => normalizeNullableString($row['legacy_party_logo_url'] ?? null),
        ],
        'candidate' => [
            'id' => stableUid('candidate', $candidateUid),
            'candidate_uid' => $candidateUid,
            'source_system' => normalizeNullableString($row['source_system'] ?? null) ?? 'JNE',
            'source_key' => $candidateUid,
            'jne_candidate_id' => normalizeNullableString($row['jne_candidate_id'] ?? null),
            'candidate_full_name' => normalizeNullableString($row['candidate_full_name'] ?? null) ?? '',
            'candidate_age' => normalizeNullableInt($row['candidate_age'] ?? null),
            'candidate_photo_url' => normalizeNullableString($row['candidate_photo_url'] ?? null),
            'candidate_photo_local_path' => normalizeNullableString($row['candidate_photo_local_path'] ?? null),
            'candidate_profile_url' => normalizeNullableString($row['candidate_profile_url'] ?? null),
        ],
        'candidacy' => [
            'id' => stableUid('candidacy', $candidacyUid),
            'candidacy_uid' => $candidacyUid,
            'source_system' => normalizeNullableString($row['source_system'] ?? null) ?? 'JNE',
            'source_key' => $candidacyUid,
            'scope_uid' => $scopeUid,
            'organization_uid' => $organizationUid,
            'candidate_uid' => $candidateUid,
            'candidacy_status' => normalizeNullableString($row['candidacy_status'] ?? null) ?? 'ADMITIDO',
            'ballot_order' => normalizeNullableInt($row['ballot_order'] ?? null),
            'source_file' => normalizeNullableString($row['source_file'] ?? null),
            'source_row' => normalizeNullableInt($row['source_row'] ?? null),
            'source_url' => normalizeNullableString($row['source_url'] ?? null),
            'retrieved_at' => normalizeNullableString($row['retrieved_at'] ?? null),
            'data_quality_status' => normalizeNullableString($row['data_quality_status'] ?? null) ?? 'CORE_COMPLETE',
            'notes' => normalizeNullableString($row['notes'] ?? null),
        ],
    ];
}

function upsertRow(PDO $pdo, string $sql, array $row): void
{
    $stmt = $pdo->prepare($sql);
    $stmt->execute($row);
}

function seedSurveyRounds(
    PDO $pdo,
    array $scopeRows,
    string $fechaApertura,
    string $fechaCierre,
    bool $replaceExisting
): array {
    $roundRows = [];

    foreach ($scopeRows as $scopeRow) {
        $level = surveyLevelValueFromElectionLevel((string) ($scopeRow['election_level'] ?? 'distrito'));
        $slug = (string) ($scopeRow['territory_slug'] ?? '');
        if ($slug === '') {
            continue;
        }

        $surveyId = stableUid('encuesta', (string) ($scopeRow['scope_uid'] ?? '') . '|1');
        $roundRows[$surveyId] = [
            'id' => $surveyId,
            'distrito_id' => $slug,
            'nivel' => $level,
            'tipo' => 'online_propia',
            'numero_ronda' => 1,
            'titulo' => surveyTitleForScope($level, $slug),
            'fecha_apertura' => $fechaApertura,
            'fecha_cierre' => $fechaCierre,
            'estado_publicacion' => 'producción',
        ];
    }

    if ($replaceExisting && $roundRows !== []) {
        $seededIds = array_keys($roundRows);
        $placeholders = implode(',', array_fill(0, count($seededIds), '?'));

        $deleteSql = <<<SQL
DELETE FROM encuestas
WHERE tipo = 'online_propia'
  AND estado_publicacion = 'producción'
  AND id NOT IN ($placeholders)
SQL;

        $stmt = $pdo->prepare($deleteSql);
        $stmt->execute($seededIds);
    }

    $upsertSql = <<<SQL
INSERT INTO encuestas (
    id,
    distrito_id,
    nivel,
    tipo,
    numero_ronda,
    titulo,
    fecha_apertura,
    fecha_cierre,
    estado_publicacion
)
VALUES (
    :id,
    :distrito_id,
    :nivel,
    :tipo,
    :numero_ronda,
    :titulo,
    :fecha_apertura,
    :fecha_cierre,
    :estado_publicacion
)
ON DUPLICATE KEY UPDATE
    distrito_id = VALUES(distrito_id),
    nivel = VALUES(nivel),
    tipo = VALUES(tipo),
    numero_ronda = VALUES(numero_ronda),
    titulo = VALUES(titulo),
    fecha_apertura = VALUES(fecha_apertura),
    fecha_cierre = VALUES(fecha_cierre),
    estado_publicacion = VALUES(estado_publicacion)
SQL;

    foreach ($roundRows as $row) {
        upsertRow($pdo, $upsertSql, $row);
    }

    return $roundRows;
}

$options = getopt('', [
    'source::',
    'dry-run',
    'seed-rounds',
    'replace-rounds',
    'seed-open::',
    'seed-close::',
    'include-legacy',
]);

$root = dirname(__DIR__);
$dryRun = array_key_exists('dry-run', $options);
$seedRounds = array_key_exists('seed-rounds', $options);
$replaceRounds = array_key_exists('replace-rounds', $options);
$includeLegacy = array_key_exists('include-legacy', $options);
$seedOpen = parseDateTimeOption((string) ($options['seed-open'] ?? '2026-07-21 00:00:00'), 'seed-open');
$seedClose = parseDateTimeOption((string) ($options['seed-close'] ?? '2026-08-05 23:59:59'), 'seed-close');

if ($includeLegacy) {
    fwrite(STDERR, "Warning: --include-legacy is deprecated. This importer now targets the normalized CSV/JSON package.\n");
}

$defaultSources = [
    $root . '/_tmp_paquete_csv_estandar_elecciones/catalogo_candidaturas_erm2026.json',
    $root . '/_tmp_paquete_csv_estandar_elecciones/catalogo_candidaturas_erm2026.csv',
];

$sourcePath = (string) ($options['source'] ?? '');
if ($sourcePath === '') {
    foreach ($defaultSources as $candidateSource) {
        if (is_file($candidateSource)) {
            $sourcePath = $candidateSource;
            break;
        }
    }
}

if ($sourcePath === '' || !is_file($sourcePath)) {
    throw new RuntimeException('No catalog source found. Pass --source with the JSON or CSV package file.');
}

$pdo = null;
if (!$dryRun) {
    $pdo = resolvePdo($root);
    executeSqlFile($pdo, $root . '/db/migrations/003_create_catalogo_normalizado.sql');
    executeSqlFile($pdo, $root . '/db/migrations/002_create_encuestas.sql');
}

$rows = loadCatalogRows($sourcePath);
$normalizedRows = [];
$errors = [];

    foreach ($rows as $index => $row) {
        if (!is_array($row)) {
            $errors[] = 'Row ' . ($index + 1) . ': invalid record format.';
            continue;
        }

        $normalized = normalizePackageRow($row, $index + 1);
        if ($normalized === null) {
            $errors[] = 'Row ' . ($index + 1) . ': failed validation or missing required fields.';
            continue;
        }

    $normalizedRows[] = $normalized;
}

$scopeRows = [];
$organizationRows = [];
$candidateRows = [];
$candidacyRows = [];

foreach ($normalizedRows as $row) {
    $scopeRows[$row['scope']['scope_uid']] = $row['scope'];
    $organizationRows[$row['organization']['organization_uid']] = $row['organization'];
    $candidateRows[$row['candidate']['candidate_uid']] = $row['candidate'];
    $candidacyRows[$row['candidacy']['candidacy_uid']] = $row['candidacy'];
}

if ($dryRun) {
    echo 'Source: ' . $sourcePath . PHP_EOL;
    echo 'Rows: ' . count($rows) . PHP_EOL;
    echo 'Valid rows: ' . count($normalizedRows) . PHP_EOL;
    echo 'Scopes: ' . count($scopeRows) . PHP_EOL;
    echo 'Organizations: ' . count($organizationRows) . PHP_EOL;
    echo 'Candidates: ' . count($candidateRows) . PHP_EOL;
    echo 'Candidacies: ' . count($candidacyRows) . PHP_EOL;
    echo 'Seed rounds: ' . ($seedRounds ? 'yes' : 'no') . PHP_EOL;
    if ($errors !== []) {
        echo 'Errors: ' . count($errors) . PHP_EOL;
        foreach ($errors as $error) {
            echo $error . PHP_EOL;
        }
    }
    exit(0);
}

if (!$pdo instanceof PDO) {
    throw new RuntimeException('Database connection could not be established.');
}

$pdo->beginTransaction();

try {
    $scopeSql = <<<SQL
INSERT INTO election_scopes (
    id,
    scope_uid,
    source_system,
    source_key,
    territory_slug,
    election_process_code,
    election_year,
    election_level,
    office_code,
    office_name,
    country_code,
    country_name,
    region_ubigeo,
    region_name,
    province_ubigeo,
    province_name,
    district_ubigeo,
    district_name
)
VALUES (
    :id,
    :scope_uid,
    :source_system,
    :source_key,
    :territory_slug,
    :election_process_code,
    :election_year,
    :election_level,
    :office_code,
    :office_name,
    :country_code,
    :country_name,
    :region_ubigeo,
    :region_name,
    :province_ubigeo,
    :province_name,
    :district_ubigeo,
    :district_name
)
ON DUPLICATE KEY UPDATE
    scope_uid = VALUES(scope_uid),
    source_system = VALUES(source_system),
    source_key = VALUES(source_key),
    territory_slug = VALUES(territory_slug),
    election_process_code = VALUES(election_process_code),
    election_year = VALUES(election_year),
    election_level = VALUES(election_level),
    office_code = VALUES(office_code),
    office_name = VALUES(office_name),
    country_code = VALUES(country_code),
    country_name = VALUES(country_name),
    region_ubigeo = VALUES(region_ubigeo),
    region_name = VALUES(region_name),
    province_ubigeo = VALUES(province_ubigeo),
    province_name = VALUES(province_name),
    district_ubigeo = VALUES(district_ubigeo),
    district_name = VALUES(district_name)
SQL;

    $organizationSql = <<<SQL
INSERT INTO political_organizations (
    id,
    organization_uid,
    source_system,
    source_key,
    jne_organization_id,
    organization_name,
    organization_abbreviation,
    organization_type,
    party_logo_url,
    party_logo_local_path,
    organization_profile_url,
    legacy_party_logo_url
)
VALUES (
    :id,
    :organization_uid,
    :source_system,
    :source_key,
    :jne_organization_id,
    :organization_name,
    :organization_abbreviation,
    :organization_type,
    :party_logo_url,
    :party_logo_local_path,
    :organization_profile_url,
    :legacy_party_logo_url
)
ON DUPLICATE KEY UPDATE
    organization_uid = VALUES(organization_uid),
    source_system = VALUES(source_system),
    source_key = VALUES(source_key),
    jne_organization_id = VALUES(jne_organization_id),
    organization_name = VALUES(organization_name),
    organization_abbreviation = VALUES(organization_abbreviation),
    organization_type = VALUES(organization_type),
    party_logo_url = VALUES(party_logo_url),
    party_logo_local_path = VALUES(party_logo_local_path),
    organization_profile_url = VALUES(organization_profile_url),
    legacy_party_logo_url = VALUES(legacy_party_logo_url)
SQL;

    $candidateSql = <<<SQL
INSERT INTO candidates (
    id,
    candidate_uid,
    source_system,
    source_key,
    jne_candidate_id,
    candidate_full_name,
    candidate_age,
    candidate_photo_url,
    candidate_photo_local_path,
    candidate_profile_url
)
VALUES (
    :id,
    :candidate_uid,
    :source_system,
    :source_key,
    :jne_candidate_id,
    :candidate_full_name,
    :candidate_age,
    :candidate_photo_url,
    :candidate_photo_local_path,
    :candidate_profile_url
)
ON DUPLICATE KEY UPDATE
    candidate_uid = VALUES(candidate_uid),
    source_system = VALUES(source_system),
    source_key = VALUES(source_key),
    jne_candidate_id = VALUES(jne_candidate_id),
    candidate_full_name = VALUES(candidate_full_name),
    candidate_age = VALUES(candidate_age),
    candidate_photo_url = VALUES(candidate_photo_url),
    candidate_photo_local_path = VALUES(candidate_photo_local_path),
    candidate_profile_url = VALUES(candidate_profile_url)
SQL;

    $candidacySql = <<<SQL
INSERT INTO candidacies (
    id,
    candidacy_uid,
    source_system,
    source_key,
    scope_id,
    organization_id,
    candidate_id,
    candidacy_status,
    ballot_order,
    source_file,
    source_row,
    source_url,
    retrieved_at,
    data_quality_status,
    notes
)
VALUES (
    :id,
    :candidacy_uid,
    :source_system,
    :source_key,
    :scope_id,
    :organization_id,
    :candidate_id,
    :candidacy_status,
    :ballot_order,
    :source_file,
    :source_row,
    :source_url,
    :retrieved_at,
    :data_quality_status,
    :notes
)
ON DUPLICATE KEY UPDATE
    candidacy_uid = VALUES(candidacy_uid),
    source_system = VALUES(source_system),
    source_key = VALUES(source_key),
    scope_id = VALUES(scope_id),
    organization_id = VALUES(organization_id),
    candidate_id = VALUES(candidate_id),
    candidacy_status = VALUES(candidacy_status),
    ballot_order = VALUES(ballot_order),
    source_file = VALUES(source_file),
    source_row = VALUES(source_row),
    source_url = VALUES(source_url),
    retrieved_at = VALUES(retrieved_at),
    data_quality_status = VALUES(data_quality_status),
    notes = VALUES(notes)
SQL;

    foreach ($scopeRows as $row) {
        upsertRow($pdo, $scopeSql, $row);
    }

    foreach ($organizationRows as $row) {
        upsertRow($pdo, $organizationSql, $row);
    }

    foreach ($candidateRows as $row) {
        upsertRow($pdo, $candidateSql, $row);
    }

    foreach ($candidacyRows as $row) {
        $scopeId = $scopeRows[$row['scope_uid']]['id'] ?? null;
        $organizationId = $organizationRows[$row['organization_uid']]['id'] ?? null;
        $candidateId = $candidateRows[$row['candidate_uid']]['id'] ?? null;

        if ($scopeId === null || $organizationId === null || $candidateId === null) {
            continue;
        }

        $payload = [
            'id' => $row['id'],
            'candidacy_uid' => $row['candidacy_uid'],
            'source_system' => $row['source_system'],
            'source_key' => $row['source_key'],
            'scope_id' => $scopeId,
            'organization_id' => $organizationId,
            'candidate_id' => $candidateId,
            'candidacy_status' => $row['candidacy_status'],
            'ballot_order' => $row['ballot_order'],
            'source_file' => $row['source_file'],
            'source_row' => $row['source_row'],
            'source_url' => $row['source_url'],
            'retrieved_at' => $row['retrieved_at'],
            'data_quality_status' => $row['data_quality_status'],
            'notes' => $row['notes'],
        ];

        upsertRow($pdo, $candidacySql, $payload);
    }

    $seededRounds = [];
    if ($seedRounds) {
        $seededRounds = seedSurveyRounds($pdo, $scopeRows, $seedOpen, $seedClose, $replaceRounds);
    }

    $pdo->commit();
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    fwrite(STDERR, 'Import failed: ' . $e->getMessage() . PHP_EOL);
    exit(1);
}

echo 'Imported ' . count($scopeRows) . ' scopes, ' . count($organizationRows) . ' organizations, ' . count($candidateRows) . ' candidates and ' . count($candidacyRows) . ' candidacies.' . PHP_EOL;

if ($seedRounds) {
    echo 'Seeded ' . count($seededRounds) . ' live rounds.' . PHP_EOL;
    if ($replaceRounds) {
        echo 'Replaced any prior production online rounds outside the canonical set.' . PHP_EOL;
    }
}

if ($errors !== []) {
    fwrite(STDERR, 'Skipped ' . count($errors) . ' invalid rows.' . PHP_EOL);
    foreach (array_slice($errors, 0, 10) as $error) {
        fwrite(STDERR, $error . PHP_EOL);
    }
}

$forbiddenCheck = $pdo->query(
    "SELECT 'encuestas' AS table_name, COUNT(*) AS matches FROM encuestas WHERE titulo REGEXP '(^|[^a-zA-Z0-9])(ejemplo|fictici[oa]s?|demo|placeholder)([^a-zA-Z0-9]|$)'
     UNION ALL
     SELECT 'political_organizations', COUNT(*) FROM political_organizations WHERE organization_name REGEXP '(^|[^a-zA-Z0-9])(ejemplo|fictici[oa]s?|demo|placeholder)([^a-zA-Z0-9]|$)'
     UNION ALL
     SELECT 'candidates', COUNT(*) FROM candidates WHERE candidate_full_name REGEXP '(^|[^a-zA-Z0-9])(ejemplo|fictici[oa]s?|demo|placeholder)([^a-zA-Z0-9]|$)'
     UNION ALL
     SELECT 'candidacies', COUNT(*) FROM candidacies WHERE COALESCE(notes, '') REGEXP '(^|[^a-zA-Z0-9])(ejemplo|fictici[oa]s?|demo|placeholder)([^a-zA-Z0-9]|$)'"
)->fetchAll();

foreach ($forbiddenCheck as $row) {
    if ((int) ($row['matches'] ?? 0) > 0) {
        throw new RuntimeException('Forbidden demo markers detected in production table ' . (string) ($row['table_name'] ?? 'unknown') . '.');
    }
}
