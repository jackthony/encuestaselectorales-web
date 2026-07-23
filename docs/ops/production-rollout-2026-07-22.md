# Production rollout evidence - 2026-07-22

This record contains counts and outcomes only. It intentionally excludes credentials,
network identifiers, encryption material, and voter location evidence.

## Backup and migration

- A full pre-Laravel MySQL dump was created outside the repository.
- The three additive Laravel migration batches completed successfully.
- Legacy tables remain available for rollback and were not dropped.
- The approved migration mode is `clean-start`: the Laravel `interactive_votes`
  table starts empty. The three legacy votes remain only in `votos_interactivos`
  and in the database backup.

## Electoral catalog import

- Source: `data/import/catalogo_candidaturas_erm2026.csv`
- Source cycle: `ERM2026`
- Checksum: `43886157a104b529cba08f1235b9f8c1446cf4a915a9aa9b76dddd1e95dd13df`
- Mapping version: `electoral-catalog-v2`.
- Callao regional: 8 rows processed, 0 rejected.
- Lima provincial: 22 rows processed, 0 rejected.
- Resulting catalog: 3 territories, 23 parties, 30 candidates, and 30 candidacies.
- JNE office codes were normalized to the canonical application values
  `regional_governor` and `provincial_mayor`.
- Final candidacy states: 10 active, 18 inactive, and 2 pending.

## Initial public rounds

- Callao region: published and active, 5 eligible options.
- Lima province: published and active, 5 eligible options.
- Both rounds close at `2026-08-05 23:59:59` in `America/Lima`.
- Laravel votes at the evidence checkpoint: 0.

## Remaining gates

- The reproducible release artifact for commit `ba1d57934c54` was built with
  production Composer dependencies and stored outside the repository.
- Production now runs through the Laravel front controller and keeps its `.env`
  under the hosting account home, outside the GitDeploy tree and `public_html`.
- `/`, `/api/health`, `/api/survey-rounds`, both initial territorial rounds, and
  the legacy-compatible `sondeos.php` URL returned HTTP 200 after cache clearing.
- The read API returned the Callao regional and Lima provincial rounds with five
  eligible candidate/party options each, including media fallback metadata.
- A controlled production GPS vote returned HTTP 201, persisted as accepted with
  an `inside` geographic result and 15-meter accuracy, and an identical retry
  returned HTTP 409 `duplicate_vote`.
- The final GitHub Actions run passed Laravel tests on SQLite and MariaDB.
- All temporary Hostinger cron jobs and encrypted deployment transport files were
  removed after verification.
- Rotate credentials after the production observation gate.
