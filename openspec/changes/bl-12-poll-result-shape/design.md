## Context

`docs/data-model.md` already specifies the `encuesta.json` (poll round metadata) and `resultados-lima.json` (denormalized per-candidate results) shapes, with `encuestadoraId` as the field that distinguishes a third-party institutional poll from the site's own opt-in poll (`"propia"`). `BL-12`'s backlog note explicitly allows dummy/historical data to unblock `BL-13`/`BL-14` before real 2026 data exists, but requires `encuestadoraId != "propia"` — i.e., the shape must be exercised as a third-party poll, not the self-poll path (that's `BL-23`).

The tension this design resolves: exercising the "third-party poll" code path without a real third-party poll to cite. Inventing specific percentages and attributing them to IEP, Ipsos, Datum, or CPI would put a false factual claim about a real institution's published work into a public repository — a real accuracy/legal risk (`CLAUDE.md` constraint 8), independent of whether any page currently renders it. The user decided (2026-07-14) to avoid this entirely rather than accept it as an acceptable interim risk.

## Goals / Non-Goals

**Goals:**
- `data/encuesta.json` and `data/resultado.json` exist, following `docs/data-model.md`'s shapes, for the pilot district (Miraflores).
- The poll is unmistakably example data — not attributed to any real institutional pollster.
- Validation catches structural errors, broken cross-references (district/candidate/pollster ids), and percentage-sum errors (the actual "math" logic `CLAUDE.md` constraint 7 calls out).

**Non-Goals:**
- Automatic ingestion/scraping of real pollster PDFs (`docs/backlog.md` BL-12 Out — manual in MVP).
- Any page rendering this data (`BL-13`/`BL-14`).
- Researching whether a real Miraflores 2022 poll exists (user declined this session — revisit if a real source surfaces later; swapping the `encuestadoraId` and numbers to a cited real source is a small follow-up, not a redesign).

## Decisions

- **Add a 6th, explicitly-fictitious pollster to `data/encuestadora.json`**: `{ "id": "ejemplo", "nombre": "Encuestadora de ejemplo (dato ficticio, no es una institución real)", "tipo": "ejemplo", "web": null }`. The name states its own fictionality in Spanish (matches the site's content language) so it can't be mistaken for a real institution even out of context. `tipo: "ejemplo"` (not `institucional`/`propia`) is a distinct enum value — `scripts/validate-encuestadoras.js`'s `tipo` check becomes structural proof this record can never silently pass as a real institutional pollster. `web: null` (allowed as a new nullable case) since a fictitious pollster has no real site — `validate-encuestadoras.js`'s "required field" check for `web` is relaxed specifically for `tipo: "ejemplo"`.
  - Alternative considered: reuse an existing real `encuestadoraId` with `metodologia` text disclaiming "example data." Rejected — the false claim would still be machine-readable as `encuestadoraId: "iep"` etc. regardless of prose disclaimers elsewhere, and a future page render could surface it without carrying the disclaimer forward.
- **`data/encuesta.json` and `data/resultado.json` are arrays**, consistent with every other data file in this repo (`distrito.json`, `candidato.json`, `partido.json`, `encuestadora.json`) — `docs/data-model.md`'s inline examples show one object each, but the established repo convention is "array of all records of that type," so this item follows it rather than the literal single-object example.
- **File naming**: `data/resultado.json` (singular type name, matching the `distrito.json`/`candidato.json`/`partido.json`/`encuestadora.json` convention), not `docs/data-model.md`'s literal example name `resultados-lima.json` — that name was Lima-mayoralty-scoped shorthand in the doc, not a filename mandate; the array holds any scope (district, Lima-wide, regional) keyed by `encuestaId`.
- **Percentage-sum validation**: `scripts/validate-resultados.js` checks `sum(resultados[].porcentaje) + indecisos + votoBlancoNulo` is within `±0.5` of `100` (floating-point tolerance, not exact equality) for every result record. This is the concrete "% math" logic `CLAUDE.md` constraint 7 names explicitly — the reason this item needs failing-test-first, not a checklist.
- **Cross-reference validation**: `scripts/validate-encuestas.js` checks `distritoId` (if not null) resolves in `data/distrito.json` and `encuestadoraId` resolves in `data/encuestadora.json`. `scripts/validate-resultados.js` checks `encuestaId` resolves in `data/encuesta.json`, and every `resultados[].candidatoId` resolves in `data/candidato.json` AND belongs to that poll's `distritoId`/`cargo` (catches a result entry pointing at a candidate from the wrong race).

## Risks / Trade-offs

- [Risk] A 6th pollster catalog entry technically reopens `BL-08` (shipped, "done," fixed 5-record catalog) → Mitigation: additive, not a redesign — `pollster-catalog`'s existing 5 records and `BL-08`'s original done-when are unaffected; this is a `MODIFIED` delta adding one clearly-scoped record for a documented reason (example data, not dynamic onboarding — `BL-08`'s actual "Out" clause).
- [Risk] Once real 2026 poll data exists, this example poll needs to be removed/replaced, not just left alongside real data → Mitigation: `encuestadoraId: "ejemplo"` makes it trivially filterable/removable later; not this item's concern (`BL-28`'s replication phase or a dedicated cleanup task will drop it).
- [Risk] `±0.5` percentage-sum tolerance could mask a real data error if set too loose → Mitigation: `0.5` chosen as tight enough to catch a genuine arithmetic mistake (typically off by whole points) while tolerating legitimate rounding in hand-entered example data; revisit if `BL-14` real-data ingestion shows this is too strict or too loose.

## Open Questions

None — scope fixed by this session's explicit user decision (no real-pollster attribution) and `docs/backlog.md` BL-12's "done when."
