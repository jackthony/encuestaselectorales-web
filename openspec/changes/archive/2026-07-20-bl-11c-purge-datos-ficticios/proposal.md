# BL-11c — Purge Fictitious Poll Data From Production

## Why

Production (`sondeos.php`, `encuesta.php`, `candidato.php`, and — until `bl-11b`'s rebuild lands — `index.php`) renders a fabricated poll: pollster `"ejemplo"` (`data/encuestadora.json`), survey `2022-miraflores-alcaldia-ejemplo` (`data/encuesta.json`), and its `resultado.json` entry with invented percentages (24.5%, 18.2%, ...). It was created to validate the JSON shape before real data existed (its own `metodologia` field says so verbatim) and every page that renders it does label it "(dato ficticio, no es una institución real)" — but it is live, indexable, and screenshot-able today, which is the exact failure mode the owner flagged: labeled-fictitious is not the same as absent.

**Found while investigating, worth recording precisely because it's the sharper version of the same risk:** `index.php`'s current home page carries a "Sondeo Lima" vote form (`fieldset` around line 180) listing **Carlos Canales / Renovación Popular** as a candidate for **Alcaldía de Lima**. Carlos Fernando Canales Anchorena is a real person who really ran — but for Miraflores' distrital alcaldía in 2022, not Lima's provincial alcaldía, and not in 2026. A real name, real party, wrong race, presented in a form that looks votable but isn't wired to anything. This is superseded by `bl-11b`'s full `index.php` rebuild (the new prototype has no such form), so this item does not patch it — it is recorded here so the specific failure mode is on the record, not just the generic "ejemplo" label.

The `candidato.json` roster itself (real 2022 Miraflores candidates, all `"activo": false`) is not in scope here — it's real historical data, already correctly caveated in the `distrito.php` hybrid rebuild (`bl-11-responsive-wcag`). This item is about the fabricated poll/pollster/result triad, not the real candidate roster.

## What changes

1. Delete the `"ejemplo"` entry from `data/encuestadora.json`, the `2022-miraflores-alcaldia-ejemplo` entry from `data/encuesta.json`, and its corresponding entry from `data/resultado.json`.
2. Remove `includes/helpers.php`'s `encuestadoraEjemplo()` — its only callers are the pages being fixed here.
3. `sondeos.php`, `encuesta.php`, `candidato.php`: remove every hardcoded reference to the `ejemplo` pollster, the fabricated percentages, and "Carlos Canales" as a poll subject (not as a historical `distrito.php` roster entry, which stays). Each page falls back to its real empty state — no poll card, no chart, no result bars — when there is no real backing data.
4. `encuestadoras.php` (found while implementing, not in the original scope): a 4th directory card — "Encuestadora X / Ejemplo de Suspensión S.A.C." — presented a wholly fabricated suspended pollster alongside the three real, JNE-registered ones. Removed; the automated check added for this item (see design.md) is what surfaced it.
4. `index.php` is explicitly **not** touched by this item — `bl-11b`'s rebuild replaces the whole page from a prototype that never had this content. Sequencing: this item's JSON/helper deletions land fine either before or after `bl-11b`; if this item's JSON deletion lands first, `bl-11b`'s rebuild simply has one less thing to avoid reintroducing.

## Explicitly out of scope

- `index.php` markup — `bl-11b`.
- `data/candidato.json`'s real 2022 Miraflores roster — not fictitious, stays, already correctly labeled historical elsewhere.
- Any future `estado_publicacion` (prueba/producción) mechanism to prevent this recurring for real test data during development — that's a schema-level concern for whichever item first introduces a DB-backed `encuestas` table (tracked as `bl-13b-encuestas-rondas-schema`); this item only removes what already leaked, it doesn't build the guardrail against the next leak.

## Success criterion

Grep for `ejemplo|Encuestadora de ejemplo|dato ficticio` across `sondeos.php`, `encuesta.php`, `candidato.php`, `includes/`, and `data/*.json` returns zero hits. Each of the three pages, loaded with no real poll data present, shows its real empty state (no card, no chart, no bars) — verified in a browser, not just by reading the template.
