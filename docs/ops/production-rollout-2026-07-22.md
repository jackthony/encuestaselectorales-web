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

- Push the verified release commit and complete Hostinger Git Deploy.
- Confirm the external production `.env` is available above `public_html`.
- Verify `/api/health`, public pages, sharing, mobile GPS, first vote persistence,
  and duplicate rejection on the deployed Laravel runtime.
- Rotate credentials after the production observation gate.
