## Context

First Phase 2 item, and the first data + script files in a repo that's been pure static HTML/CSS until now (`BL-01`'s CodeQL note: `{"HTML":2291}`, 0 JS). Everything downstream — nav (`BL-10`), district pages (`BL-11`), poll shapes (`BL-12`) — keys off a district `id`, so getting the id scheme right here avoids a rename cascade later. `docs/data-model.md` already fixes the shape (`{ id, nombre, provincia, region }`); this item is choosing the 43 actual district ids/names and building the check that keeps the file honest.

## Goals / Non-Goals

**Goals:**
- Produce exactly 43 records for Lima Metropolitana, one per district, matching `docs/data-model.md`'s documented shape exactly (no extra/missing fields).
- `id` is a URL-safe slug (lowercase, hyphen-separated, ASCII — no accents, no spaces) so it can be used directly in a future `/distritos/<id>.html` path (`BL-11`) with zero transformation.
- Write `scripts/validate-data.js` first, watch it fail against an empty/missing `distrito.json`, then add the data to green — genuine test-first per `CLAUDE.md` constraint 7.

**Non-Goals:**
- Any other province/region (Callao, provinces outside Lima Metropolitana) — explicitly out per `docs/backlog.md` BL-07.
- Consuming this data in a page (nav/district page) — that's `BL-10`/`BL-11`.
- A full data-validation framework — `validate-data.js` only checks what exists today (`distrito.json`); it grows incrementally as `BL-08`/`BL-09`/`BL-12` add more catalogs, not built out speculatively now.

## Decisions

1. **43 districts, hand-written list.** Lima Metropolitana's district set is fixed public geography (INEI/JNE administrative division), not something that changes before the election — safe to hand-write now rather than wait for any JNE data drop. Cross-checked against the known 43-district set already referenced structurally in `docs/design-references.md` (`encuestas.com.pe`'s nav dropdown, "43 districts").
2. **`id` = ASCII slug of the district name** (e.g. `"San Juan de Lurigancho"` → `"san-juan-de-lurigancho"`, `"Magdalena del Mar"` → `"magdalena-del-mar"`). Accents stripped (`"Rímac"` → `"rimac"`, `"Chosica"` alias not used — official name `"Lurigancho"` per JNE/INEI convention, avoids a second identity for the same district). Matches `docs/data-model.md`'s own example (`"san-isidro"`).
3. **`provincia` and `region` are both the literal string `"lima"` for all 43 records** — every Lima Metropolitana district belongs to Provincia de Lima and Región Lima; no per-record variation exists at this scope. Matches the documented example exactly.
4. **`scripts/validate-data.js` is plain Node, zero dependencies, run via `node scripts/validate-data.js`**, exiting non-zero on failure — per `docs/engineering-standards.md` §5's "no framework yet" ponytail rule and matching `BL-21`'s planned CI usage (`node scripts/validate-data.js` as a required check). For this item it validates: file exists and parses as JSON array; exactly 43 records; each record has non-empty `id`/`nombre`/`provincia`/`region` string fields; `id` values are unique; `id` values match `^[a-z0-9]+(-[a-z0-9]+)*$` (slug shape).
5. **First `.js` file in the repo — CodeQL default setup re-attempt is a follow-up task, not a blocker.** `BL-01`'s deferred note says to retry once the first `.js` lands; this item's `tasks.md` includes retrying the `gh` command from `docs/devsecops.md`, but a failure there doesn't block merging this item (documented-deferred is an acceptable outcome, same precedent as `BL-01`).

## Risks / Trade-offs

- **[Risk]** A wrong district name/count (43 is easy to get subtly wrong — some sources conflate Lima Metropolitana with Lima Province differently). **Mitigation**: the `validate-data.js` count check (`=== 43`) catches an accidental duplicate/omission; the actual list is cross-checked against INEI's standard Lima Metropolitana division before commit.
- **[Risk]** Slug collisions from accent-stripping (unlikely with 43 unique Spanish names, but the uniqueness check in `validate-data.js` catches it either way).
- **[Trade-off]** Hand-writing 43 records now vs. waiting for an authoritative JNE JSON export — accepted because district boundaries don't depend on the election calendar (only candidates/polls do, per `CLAUDE.md` constraint 1), so waiting would only delay Phase 3 for no accuracy gain.
