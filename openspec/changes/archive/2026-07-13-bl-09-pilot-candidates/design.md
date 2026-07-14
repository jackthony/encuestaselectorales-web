## Context

`docs/data-model.md` fixes the shape for both `partido.json` (`{ id, nombre, siglas, color, logo }`) and `candidato.json` (`{ id, nombre, partidoId, cargo, distritoId, foto, numero, activo }`). `BL-09`'s own backlog entry allows using "a documented dummy/historical set to unblock the stack" before Aug 5, 2026 (JNE admitted candidate lists). Real 2022 Miraflores mayoral race data is verifiable and publicly reported (ONPE/JNE-certified results, covered by El Comercio, La República, Andina), so it's used instead of inventing names — this is the historical set, not a fabricated one.

## Goals / Non-Goals

**Goals:**
- Populate `partido.json`/`candidato.json` with the real 8-candidate, 8-party 2022 Miraflores mayoral race, so `BL-10`/`BL-11` have real, citable rows to render before any 2026 data exists.
- Make it unambiguous that this is 2022 historical data, not a 2026 candidacy claim (`activo: false` on every record).
- Don't fabricate values that aren't verified: JNE list numbers, candidate photos, and party logos are `null`, not guessed.
- A repeatable cross-referential validation: every `candidato.distritoId` resolves in `distrito.json`, every `candidato.partidoId` resolves in `partido.json`.

**Non-Goals:**
- Any other district's candidates (`BL-28` replicates the pattern later, by data).
- Real 2026 candidate data — blocked by `CLAUDE.md` constraint 1 until Aug 5, 2026.
- Sourcing real JNE candidate photos or party logos — deferred to whichever item actually renders them (`BL-11`), since `BL-05`'s legal-attribution item explicitly says JNE photo reuse terms are unverified, don't inherit simulatuvoto's assumption.
- Vote results/percentages — that's `encuesta.json`/`resultados-lima.json` territory (`BL-12`/`BL-14`), a different entity than the candidate roster this item builds.

## Decisions

1. **Source and citation**: the 8 candidates and their parties are taken from ONPE-certified/JNE-reported 2022 Miraflores municipal election coverage — El Comercio, La República, Andina (specific URLs in the PR description). Winner: Carlos Fernando Canales Anchorena (Renovación Popular). Full candidate list (alphabetical, as reported): Carlos Fernando Canales Anchorena (Renovación Popular), María Rocío Cano Guerinoni (Podemos Perú), Alessandra Camila Krause Alva (Avanza País), Manuel Alejandro Masías Oyanguren (Alianza para el Progreso), Ernesto Javier Mendoza De La Puente (Somos Perú), Sergio Manuel Meza Salazar (Acción Popular), Diego Sebastián Mora Olivares (Partido Morado), Sitza Lita Romero Peralta (Fuerza Popular).
2. **`activo: false` for every record, unconditionally.** This is the load-bearing decision that keeps this dataset from being mistaken for 2026 candidacy — `CLAUDE.md` constraint 1 is non-negotiable, and marking historical rows `activo: false` is how the data itself (not just a comment) reflects that these aren't current 2026 candidates, independent of whether any of these individuals also runs in 2026.
3. **`numero` (JNE list number) and `foto` (candidate photo) are `null` for every candidate; `logo` is `null` for every party.** Not verified/sourced for this interim dataset — left `null` rather than guessed, consistent with `BL-05`'s "don't inherit an unverified reuse assumption" stance and constraint 8's "when in doubt, publish less." Tracked as a `BL-11` follow-up (whichever item first renders a photo/logo sources it for real, with attribution).
4. **Party `id`s are sequential integers (1-8)**, `siglas` chosen to avoid collision (`Acción Popular` = `AP`, `Avanza País` = `AVP` — both parties are colloquially referred to as "AP", so `AVP` disambiguates in this catalog; not necessarily JNE's own registry abbreviation, documented as a display-only choice, not a legal claim). `color` values are representative party-brand colors (approximate, not verified pixel-exact against official brand guidelines — cosmetic metadata only, revisited if `BL-13`'s chart component needs stricter validation later). Fuerza Popular reuses `docs/data-model.md`'s own example row (`"Fuerza Popular"`, `"FP"`, `"#FF6B00"`) verbatim for consistency.
5. **Candidate `id`s are sequential integers (1-8)**, referencing `partidoId` 1-8 by array position. `cargo` is `"alcalde_distrital"` for all 8 (mayoral race only — no council/`regidor` records, out of scope). `distritoId` is `"miraflores"` for all 8, matching `BL-07`'s existing slug.
6. **Two validation scripts, same per-file precedent as `BL-07`/`BL-08`**: `scripts/validate-partidos.js` (record count = 8, required non-empty `id`/`nombre`/`siglas`/`color`, `logo` nullable, unique `id` and `siglas`, `color` matches `^#[0-9a-fA-F]{6}$`) and `scripts/validate-candidatos.js` (record count = 8, required fields per shape with `foto`/`numero` nullable, `cargo` is exactly `"alcalde_distrital"` for this dataset, `distritoId` must exist as an `id` in `data/distrito.json`, `partidoId` must exist as an `id` in `data/partido.json`, unique candidate `id`).

## Risks / Trade-offs

- **[Risk]** Using real people's full names/party affiliations in a public political data product, even for a settled 2022 race. **Mitigation**: this is uncontroversial, already-published, ONPE-certified public fact (the same information El Comercio/La República/Andina already published) — not a private-data or defamation concern like `BL-24`'s judicial-record badges are. `activo: false` prevents any read that this is a 2026 claim.
- **[Risk]** Approximate (not pixel-verified) party colors and a non-JNE-registry `siglas` disambiguation (`AVP`). **Mitigation**: cosmetic metadata, explicitly documented as approximate; revisited before any real chart/branding use (`BL-13`) if it matters then.
- **[Trade-off]** `numero`/`foto`/`logo` all `null` means `BL-11`'s district page will need a placeholder/fallback render path for missing photos — accepted now rather than fabricating asset paths that don't exist, `BL-11`'s own spec will need to account for it.
