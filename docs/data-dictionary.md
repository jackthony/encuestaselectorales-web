# Diccionario de Datos

Estado: catálogo nuevo + encuestas activas actuales.

## Criterio de limpieza

- **Núcleo runtime**: tablas y objetos que la app usa al renderizar la home, resolver encuestas y registrar votos.
- **Soporte técnico**: tablas, comandos y servicios útiles para importar o auditar datos, pero no necesarios para mostrar la UI.
- **Artefactos puntuales**: archivos de reconciliación o backups que no forman parte del runtime.

## Resumen Ejecutivo

### Núcleo runtime

- `electoral_territories`
- `electoral_parties`
- `electoral_candidates`
- `electoral_candidacies`
- `survey_rounds`
- `survey_options`
- `interactive_votes`

### Soporte técnico

- `import_runs`
- `import_rows`
- `app/Console/Commands/ImportElectoralCatalog.php`
- `app/Infrastructure/Import/*`

### Artefactos puntuales

- `data/territories_ubigeo_map.json`
- `database/database-backup-*.sqlite`

## Tablas

### `electoral_territories`

Uso:
- Catálogo geográfico canónico.
- Fuente para la búsqueda de territorios.
- Base para resolver la encuesta activa por región, provincia o distrito.
- Fuente del texto visible en la home y en los endpoints públicos.

Campos:
- `id`: PK ULID.
- `official_code`: ubigeo oficial; clave de negocio principal.
- `scope_type`: `region`, `province`, `district`.
- `name`: nombre visible.
- `canonical_name`: nombre normalizado para búsqueda.
- `slug`: slug público de ruta.
- `parent_id`: jerarquía territorio-padre.
- `source_system`: origen del dato.
- `source_key`: identidad estable del origen.
- `publication_state`: `draft`, `published`, `archived`.
- `published_at`: fecha de publicación.
- `source_url`: referencia externa.
- `created_at`, `updated_at`: auditoría.

Relaciones:
- `parent()` y `children()` auto-relacionadas.
- `candidacies()`
- `surveyRounds()`
- `validatedVotes()`
- `importRuns()`

Uso real en código:
- `TerritoryCatalog`
- `EloquentSurveyRoundQuery`
- `RegisterVote`
- `ConfiguredGeographicValidator`
- `SurveyRoundController`
- `TerritoryController`

### `electoral_parties`

Uso:
- Catálogo de organizaciones políticas.
- Logo y nombre de partido para tarjetas y resultados.

Campos:
- `id`: PK ULID.
- `source_system`, `source_key`: identidad estable.
- `name`: nombre del partido.
- `acronym`: sigla.
- `logo_url`: imagen del partido.
- `logo_storage_disk`, `logo_storage_path`: soporte de almacenamiento local si se usa.
- `logo_source_attribution`: atribución del logo.
- `source_url`: referencia externa.
- `status`: `active` o `inactive`.
- `created_at`, `updated_at`: auditoría.

Relaciones:
- `candidacies()`

Uso real en código:
- `EloquentSurveyRoundQuery`
- `RegisterVote`
- `TransactionalElectoralCatalogImporter`
- UI de rondas y opciones

### `electoral_candidates`

Uso:
- Catálogo de personas candidatas.
- Foto y nombre visibles en la UI.

Campos:
- `id`: PK ULID.
- `source_system`, `source_key`: identidad estable.
- `full_name`: nombre completo.
- `photo_url`: foto de candidato.
- `photo_storage_disk`, `photo_storage_path`: soporte de almacenamiento local si se usa.
- `photo_source_attribution`: atribución de la foto.
- `source_url`: referencia externa.
- `status`: `active` o `inactive`.
- `created_at`, `updated_at`: auditoría.

Relaciones:
- `candidacies()`

Uso real en código:
- `EloquentSurveyRoundQuery`
- `RegisterVote`
- `TransactionalElectoralCatalogImporter`
- UI de opciones de encuesta

### `electoral_candidacies`

Uso:
- Tabla puente entre candidato, partido y territorio.
- Define quién compite en qué cargo y ciclo electoral.

Campos:
- `id`: PK ULID.
- `candidate_id`: FK a `electoral_candidates`.
- `political_party_id`: FK a `electoral_parties`.
- `territory_id`: FK a `electoral_territories`.
- `office_type`: `regional_governor`, `provincial_mayor`, `district_mayor`.
- `election_cycle`: por ejemplo `ERM2026`.
- `source_system`, `source_key`: identidad estable.
- `ballot_order`: orden de aparición.
- `status`: `active`, `inactive`, `pending`.
- `source_file`: archivo de origen.
- `source_row`: fila de origen.
- `source_url`: referencia externa.
- `retrieved_at`: momento de captura.
- `created_at`, `updated_at`: auditoría.

Relaciones:
- `candidate()`
- `politicalParty()`
- `territory()`
- `surveyOptions()`

Uso real en código:
- `EloquentSurveyRoundQuery`
- `RegisterVote`
- `SurveyOptionEligibility`
- `InitialSurveyRoundsSeeder`
- `TransactionalElectoralCatalogImporter`

### `survey_rounds`

Uso:
- Encuesta activa o programada para un territorio.
- Es el objeto principal que consume la home.

Campos:
- `id`: PK ULID.
- `territory_id`: FK a `electoral_territories`.
- `round_number`: número de ronda.
- `election_cycle`: ciclo electoral.
- `survey_type`: tipo de encuesta.
- `office_type`: cargo electoral.
- `title`: título visible.
- `opens_at`: inicio.
- `closes_at`: cierre.
- `publication_state`: publicación del round.
- `readiness_state`: `active`, `blocked`, `scheduled`, `closed`, `unavailable`.
- `blocked_reason`: motivo de bloqueo.
- `source_system`, `source_key`: identidad estable.
- `source_url`: referencia externa.
- `created_at`, `updated_at`: auditoría.

Relaciones:
- `territory()`
- `options()`
- `votes()`

Uso real en código:
- `HomeController`
- `SurveyRoundController`
- `EloquentSurveyRoundQuery`
- `RegisterVote`
- `InitialSurveyRoundsSeeder`

### `survey_options`

Uso:
- Opciones visibles de cada encuesta.
- Une la ronda con la candidacidad específica.

Campos:
- `id`: PK ULID.
- `survey_round_id`: FK a `survey_rounds`.
- `candidacy_id`: FK a `electoral_candidacies`.
- `display_order`: orden visual.
- `status`: `eligible` o `ineligible`.
- `created_at`, `updated_at`: auditoría.

Relaciones:
- `surveyRound()`
- `candidacy()`
- `votes()`

Uso real en código:
- `EloquentSurveyRoundQuery`
- `RegisterVote`
- `InitialSurveyRoundsSeeder`

### `interactive_votes`

Uso:
- Registro de votos reales.
- Guarda señales de seguridad y geolocalización.

Campos:
- `id`: PK ULID.
- `survey_round_id`: FK a `survey_rounds`.
- `survey_option_id`: FK a `survey_options`.
- `validated_territory_id`: FK a `electoral_territories`.
- `vote_type`: tipo de voto.
- `gps_latitude`, `gps_longitude`, `gps_accuracy_meters`: validación geográfica.
- `geo_validation_method`, `geo_validation_result`: trazabilidad de validación.
- `interaction_time_ms`: tiempo de interacción.
- `ip_ciphertext`, `ip_nonce`, `ip_auth_tag`: IP cifrada.
- `ip_encryption_key_version`: versión de cifrado.
- `ip_hmac`, `ip_hmac_key_version`: deduplicación y protección.
- `device_token_hmac`, `device_hmac_key_version`: deduplicación por dispositivo.
- `browser_fingerprint_hmac`, `browser_hmac_key_version`: deduplicación por navegador.
- `status`: `accepted` u otro estado operativo.
- `created_at`, `updated_at`: auditoría.

Relaciones:
- `surveyRound()`
- `surveyOption()`
- `validatedTerritory()`

Uso real en código:
- `RegisterVote`
- `EloquentSurveyRoundQuery` para contar votos por opción
- `Territory` como `validatedVotes()`

### `import_runs`

Uso:
- Auditoría por lote de importación.
- Idempotencia por fuente/ciclo/territorio/cargo.

Campos:
- `id`: PK ULID.
- `territory_id`: FK a `electoral_territories`.
- `source_system`, `source_identity`, `source_checksum`, `mapping_version`.
- `election_cycle`, `office_type`, `source_file`.
- `source_size_bytes`, `operator_identifier`.
- `status`: `pending`, `running`, `completed`, `failed`.
- `total_rows`, `created_rows`, `updated_rows`, `unchanged_rows`, `rejected_rows`.
- `failure_summary`.
- `started_at`, `completed_at`.
- `created_at`, `updated_at`.

Relaciones:
- `territory()`
- `rows()`

Uso real en código:
- `TransactionalElectoralCatalogImporter`
- `ImportElectoralCatalog`

### `import_rows`

Uso:
- Auditoría fila por fila de cada importación.

Campos:
- `id`: PK ULID.
- `import_run_id`: FK a `import_runs`.
- `source_row_number`: número de fila original.
- `source_key`: clave de fila.
- `status`: `accepted`, `rejected`.
- `action`: `created`, `updated`, `unchanged`.
- `entity_type`: normalmente `candidacy`.
- `entity_id`: entidad persistida.
- `normalized_payload`: payload normalizado.
- `diagnostics`: validaciones y mensajes.
- `message`: texto de error o resumen.
- `created_at`, `updated_at`.

Relación:
- `importRun()`

Uso real en código:
- `TransactionalElectoralCatalogImporter`
- `ImportElectoralCatalog`

## Objetos de aplicación y dominio

### DTOs de lectura

- `TerritoryData`
- `CandidateOptionData`
- `SurveyRoundData`
- `RoundResult`

Uso:
- Forman el contrato que devuelve la API y la home.
- No son persistencia; son serialización limpia del dominio.

### DTOs de voto

- `RegisterVoteData`
- `PrivacySignals`

Uso:
- Datos de entrada del flujo de voto.
- `PrivacySignals` representa la salida cifrada/hacheada antes de escribir `interactive_votes`.

### Queries y servicios de runtime

- `EloquentTerritoryCatalog`
- `EloquentSurveyRoundQuery`
- `RegisterVote`
- `ConfiguredGeographicValidator`
- `AesGcmVotePrivacy`
- `TrustedClientIp`

Uso:
- Son los componentes que sostienen la home, la búsqueda de territorios y el voto.

## Objetos de importación

- `ImportElectoralCatalog`
- `TransactionalElectoralCatalogImporter`
- `CatalogRowNormalizer`
- `VersionedCatalogReader`
- `CsvCatalogReader`
- `JsonCatalogReader`
- `CatalogImportDocument`
- `CatalogImportOptions`
- `CatalogImportSummary`
- `NormalizedCatalogRow`
- `SourceRecord`
- `StagedCatalogRow`
- `CatalogImportException`

Uso:
- Soporte para importar CSV/JSON auditado.
- Ya no es el camino manual que se usó para la carga puntual actual.

## Artefactos puntuales

- `data/territories_ubigeo_map.json`: mapa de territorios usado para reconciliar la BD local.
- `database/database-backup-*.sqlite`: backups de seguridad previos a la reconciliación.

## Qué queda como basura candidata

- Todo lo que no participe en:
  - `HomeController`
  - `SurveyRoundController`
  - `TerritoryController`
  - `VoteController`
  - `RegisterVote`
  - `EloquentTerritoryCatalog`
  - `EloquentSurveyRoundQuery`

- Cualquier artefacto de respaldo o reconciliación puntual que ya no se necesite para repetir la carga.

