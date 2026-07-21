# BL-11c — Tasks

## 1. The failing check (before the deletion)
- [x] 1.1 Write a small grep-based check that fails if `ejemplo|dato ficticio` appears in any root page's **rendered output**, or in any `data/*.json` file. *(Implemented as `checkNoFictitiousData()` in `scripts/check-refactor.php` rather than a separate file — checks rendered HTML, not raw PHP source, so legitimate source comments documenting this fix don't false-positive.)*
- [x] 1.2 Run it. **Confirm it fails** today. *(Failed against 7 markers across 4 files before the fixes below; after fixing sondeos/encuesta/candidato/encuestadoras and the JSON, only `index.php` still fails — expected, that page is `bl-11b`'s scope, not this item's.)*
- [x] 1.3 Commit the failing check. *(Landed in `f2cdf92`, PR #20 — merged alongside the fixes it validates.)*

## 2. Delete the fabricated data
- [x] 2.1 Remove the `"ejemplo"` object from `data/encuestadora.json`.
- [x] 2.2 Remove the `2022-miraflores-alcaldia-ejemplo` object from `data/encuesta.json`.
- [x] 2.3 Remove its corresponding object from `data/resultado.json`.
- [x] 2.4 Remove `encuestadoraEjemplo()` from `includes/helpers.php`.

## 3. Fix the three pages (plus one found while implementing)
- [x] 3.1 `sondeos.php`: removed the hardcoded "Carlos Canales Anchorena" sidebar vote form and the fabricated JS `mockData` entry with `pct` fields. The sidebar now gates on `VOTACION_EN_VIVO` (bl-11-responsive-wcag's flag) and shows a WhatsApp CTA when off; the feed falls through to the page's existing real "trabajo de campo en progreso" empty state for every district, since none has a real result yet.
- [x] 3.2 `encuesta.php`: rewritten to look up `?id=` against `data/encuesta.json` (excluding `ejemplo`) and render a real empty state when there's no match — which is every request today, since no real campo study exists yet. All fabricated result bars/ficha técnica/analysis prose removed.
- [x] 3.3 `candidato.php`: rewritten to look up `?id=` (not the never-wired `?dni=`) against `data/candidato.json`, with a real empty state for the trend chart and "últimos registros" (no real per-candidate poll history exists yet). Photo replaced with the initials-on-party-color avatar (new `iniciales()` helper in `includes/helpers.php`), matching `CLAUDE.md`'s image-fallback rule.
- [x] 3.3b *(found during implementation, not in the original list)* `encuestadoras.php` had its own separate fabricated entity — a 4th directory card, "Encuestadora X / Ejemplo de Suspensión S.A.C." — presented alongside the three real, JNE-registered pollsters. Removed.
- [x] 3.4 Grepped rendered output of `sondeos.php`, `encuesta.php`, `candidato.php` for `Canales` — zero matches (the historical `distrito.php` roster context is unaffected, out of scope).

## 4. Green
- [x] 4.1 Re-ran `scripts/check-refactor.php`. `no-fictitious-data` passes for every root page except `index.php` (expected — `bl-11b`'s scope).
- [x] 4.2 Load `sondeos.php`, `encuesta.php`, `candidato.php`, `encuestadoras.php` in a real browser. *(Confirmed in Chrome: `sondeos.php` renders 43 real districts with the "trabajo de campo en progreso" empty state plus the WhatsApp gated sidebar CTA; `encuesta.php`/`candidato.php` show their real "no encontrado" empty states with no id; `encuestadoras.php` renders 5 real REE-JNE pollsters, no 4th fabricated card. No console errors on any page beyond the expected Tailwind CDN production warning.)*
- [x] 4.3 Commit the deletions and the now-passing check together. *(Landed in `f2cdf92`, PR #20.)*

## Out of scope — do not touch
- `index.php` — `bl-11b` rebuilds it wholesale from a prototype that never had this content.
- `data/candidato.json`'s real 2022 roster — historical, not fictitious, already handled by `bl-11-responsive-wcag`'s "activo: false" labeling requirement.
