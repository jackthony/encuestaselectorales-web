## Context

`distrito.json` is the root entity every later item keys off of (BL-09 candidates, BL-10 nav, BL-11 district page, BL-14 results). Its `id` values become part of URLs (`/distritos/<id>.html` or similar, decided later in BL-10) and get referenced by `distritoId` in `candidato.json` and `encuesta.json`. Getting the id format and completeness right now avoids renaming keys across every downstream file later.

The 43 districts are the official set that makes up the province of Lima ("Lima Metropolitana"), per INEI/JNE administrative division — a fixed, well-known list that doesn't need external lookup.

## Goals / Non-Goals

**Goals:**
- One authoritative `data/distrito.json` with exactly the 43 Lima Metropolitana districts.
- A deterministic, collision-free `id` slug per district (kebab-case, ASCII, no accents/spaces).
- A repeatable validation script so any future hand-edit to this file can't silently break the count, a required field, or a duplicate/malformed slug.

**Non-Goals:**
- Provinces outside Lima Metropolitana, or the full 25-region/`ubigeo` hierarchy (entity #2, Phase 2 later item, not this one).
- Any consumer of this data (nav dropdown, district page) — that's BL-10/BL-11.
- CI wiring (`node scripts/validate-data.js` as a required check is BL-21) — the script just needs to exist and be runnable locally now.

## Decisions

- **Slug algorithm**: lowercase the district name, strip Spanish accents/diacritics (á→a, é→e, í→i, ó→o, ú→u, ñ→n), replace spaces and any non `[a-z0-9]` character with `-`, collapse repeats. Applied by hand once (43 is a small, fixed list) rather than a runtime slugify dependency — consistent with the no-build/no-dependency stance (`CLAUDE.md` principle 5 / `BL-04`'s no-webfont precedent).
  - Alternative considered: pull a slugify npm package. Rejected — one-time transform on a static list, a dependency buys nothing.
- **`provincia`/`region` values**: both fixed to `"lima"` for all 43 records (all of Lima Metropolitana is province Lima, region Lima) — matches the existing example row in `docs/data-model.md`. Kept as explicit fields (not hardcoded downstream) so entity #2 (Phase 2) can later extend without a schema change.
- **Validation script location/tooling**: `scripts/validate-distritos.js`, plain Node (no test framework — matches the static/no-build stack), run via `node scripts/validate-distritos.js`, exits non-zero on any failure so it's CI-ready later (BL-21) without rework.
- **Validation rules** (all must pass):
  1. File parses as valid JSON, is an array.
  2. Array length is exactly 43.
  3. Every record has non-empty `id`, `nombre`, `provincia`, `region` (string type).
  4. Every `id` matches `/^[a-z0-9]+(-[a-z0-9]+)*$/` (lowercase kebab-case, no leading/trailing/double hyphens).
  5. All 43 `id` values are unique.

## Risks / Trade-offs

- [Risk] Hand-typed list of 43 district names could contain a factual error (wrong name, wrong count) → Mitigation: cross-checked against the standard INEI/JNE administrative division of Lima province during implementation; the validation script's exact-43 check catches an accidental omission/duplication, though not a wrong-but-plausible name — that's a manual review item at PR time.
- [Risk] Slug collisions (two districts producing the same slug) → Mitigation: none of Lima's 43 district names collide once accent-stripped; validation rule 5 would catch it regardless if a future edit introduced one.

## Open Questions

None — scope is fixed by `docs/data-model.md`'s existing shape and `docs/backlog.md`'s BL-07 definition.
