# BL-11 — Tasks

Two independent workstreams (`distrito.php` rebuild, GPS recovery modal) plus
one audit (contrast) that touches everything already shipped. Sequence
matters within each section; sections 1 and 2 can interleave, section 3
needs both finished first since it audits the union of all 9 pages.

## 1. Rebuild `distrito.php` from the hybrid Canvas prototype (superseding update, 2026-07-19 — highest priority, ships before section 2)

`canvas-gemini/tablero_electoral_growth_hack_hibrido.html` is the target, not `distrito.html` (see design.md "Priority 0" for why). It models the page as independently toggling blocks, not exclusive states — the live page can show more than one block at once.

- [x] 1.1 Extend `scripts/check-refactor.php`'s comparison to `distrito.php` against `canvas-gemini/tablero_electoral_growth_hack_hibrido.html`, same tag+class multiset diff BL-10 used for the other 8. **Run it, confirm it fails** against the current stub before touching the page — same discipline as BL-10 task 1. *(Confirmed failing against the stub, then implemented — see 1.10.)*
- [x] 1.2 Build each block as an independent conditional in `distrito.php`, driven by what `includes/data.php` returns for the requested `?slug=` (not `?id=` — `distrito.php` and `card-sondeo.php` already standardized on `?slug=`, per commit `2644a9e`; do not reintroduce `?id=`):
  - Growth-hack CTA (WhatsApp `wa.me` link, copy per proposal.md) → no `candidato.json` entries for this district
  - Candidate roster (reuse `distrito.html`'s initials-avatar-on-party-color card, not the hybrid's plain rows) → `candidato.json` entries exist
  - Vote widget → candidates exist **and** `VOTACION_EN_VIVO` (design.md) is `true`. Build the markup, but the flag defaults `false` — verify it does not render with the flag off.
  - Own-poll evolution chart (Chart.js, per hybrid file) → a closed `online_propia` round with results exists — none do yet, so this renders nothing today; verify it degrades to "nothing", not a broken empty chart
  - Campo-studies sidebar (reuse `distrito.html`'s ficha-técnica + result-bar layout) → a real field-study result exists for this district, independent of every block above
- [x] 1.3 Verify the growth-hack CTA and the campo sidebar can render together on the same request (no candidates yet, but a real field study exists) — this is the case the old 3-exclusive-states model couldn't represent; confirm the new one can. *(Verified by construction — the CTA and the sidebar are two independent `if` blocks, not an `elseif` chain. Not exercised with live data today: the only `data/encuesta.json` entry is the `ejemplo` placeholder, which `distrito.php` explicitly excludes — see 1.9. Re-verify with real data once a real campo study exists for a candidate-less district.)*
- [x] 1.4 The growth-hack CTA is what nearly every district hits today (42 of 43 in Lima, effectively all of Peru outside it). Verify it renders correctly for a district with zero `data/candidato.json` entries — not just for Miraflores, which is the one exception. *(Verified with `?slug=comas`.)*
- [x] 1.5 The 2022 Miraflores candidates carry `"activo": false`. Render an explicit "candidatura 2022, no vigente para 2026" label — do not let a false-labeled historical candidate look current. This is a correctness requirement, not styling. *(Verified with `?slug=miraflores`.)*
- [x] 1.6 Header/footer: confirm the prototype uses cluster B chrome (`portal_de_sondeos_ciudadanos.html`'s header — "Distritos de Lima ▾", search button). It does, per the 2026-07-18 audit. Reuse `partials/header.php`/`footer.php` verbatim; do not hand-copy markup from the prototype for these two regions. *(Verified — check-refactor.php's header/footer consistency check stays green.)*
- [x] 1.7 Include `partials/widget-gps.php` only when the vote widget block renders (i.e., only when `VOTACION_EN_VIVO` is `true` and candidates exist). Do not include it in the growth-hack CTA state — there is nothing to vote for yet, and rendering it with the flag off would be exactly the "form to nowhere" this item rules out. *(Verified: `modal-overlay`/`voto-gps.js` absent from rendered output while the flag is `false`.)*
- [x] 1.8 Party colors via `includes/helpers.php`'s `partyColor()`, same as the rest of the site — no hardcoded hex.
- [x] 1.9 Legal scrub pass identical to BL-10 section 6: grep the new page for `Ipsos|Datum|CPI|IEP`; any hit outside `encuestadoras.php`-style factual listing gets attributed to the `ejemplo` entry, and the whole `ejemplo` entry itself gets purged per `bl-11c-purge-datos-ficticios` (do not duplicate that work here, just don't reintroduce it). *(`distrito.php` explicitly excludes `encuestadoraId === 'ejemplo'` from its campo-study lookup, independent of whether `bl-11c` has landed yet — verified Miraflores shows the real "sin estudios" empty state, not the ejemplo record.)*
- [x] 1.10 Run the check. Green. *(`php scripts/check-refactor.php` — 8 of 8 pages match, including `distrito.php`'s new growth-hack CTA structural diff.)*

## 2. GPS permission-denied recovery modal

Reference implementation already built and reviewed: `openspec/changes/bl-11-responsive-wcag/reference-modal-rescate.html`. Port it — do not redesign it.

- [ ] 2.1 Move the modal markup into `partials/widget-gps.php` as a new state alongside the existing soft-ask / loading / smart-match / success states.
- [ ] 2.2 Add `brand.surface` and `brand.greenText` (`#0f7a4a`) to the single `tailwind.config` in `partials/head.php` — the reference file defines them locally; BL-11 is where they join the real config.
- [ ] 2.3 Port the reference file's inline `<script>` into `assets/js/voto-gps.js`, wired to its existing state machine. The reference already emits `gps:reintentar` and `gps:cancelar` as `CustomEvent`s — the state machine listens for both instead of the port re-deriving the flow.
- [ ] 2.4 Wire `navigator.permissions.query({name:'geolocation'})`: if `denied` on load, show the recovery modal directly rather than firing a prompt that fails silently. Feature-detect — fall through to the normal request path where the API is unavailable (historically Safari).
- [ ] 2.5 Replace the `alert()` on missing candidate selection (a separate, smaller defect noted in BL-10's task log) with an inline validation message near the form — no browser-native `alert()` remains anywhere in the vote flow.
- [ ] 2.6 Confirm in a real browser: deny geolocation, confirm the modal appears (not a dead `alert`), confirm Tab cycles only within the modal, confirm Escape triggers `gps:cancelar`, confirm the previously-selected candidate is still selected after a successful retry.

## 3. WCAG AA contrast audit — all 9 pages

- [ ] 3.1 Enumerate every distinct foreground/background color pair actually rendered across the 8 BL-10 pages plus the new `distrito.php`. Include the recovery modal.
- [ ] 3.2 Measure each pair's contrast ratio. Record in `docs/wcag-contrast-audit.md`: pair, ratio, pass/fail against 4.5:1 (text) or 3:1 (non-text UI — borders, icons, graph bars).
- [ ] 3.3 `brand.green` `#15ba75` is the predicted failure as text. Confirm or deny with the actual measurement — do not assume the prediction is correct without checking.
- [ ] 3.4 For every failing pair: text uses `brand.greenText` (`#0f7a4a`, already added in 2.2) or another already-approved token; non-text uses can keep `#15ba75` if they clear 3:1. Do not introduce a third variant of the same hue without checking the first two first.
- [ ] 3.5 Do not touch `brand.blue` `#102f86` — already dark enough to pass as text (BL-04 precedent for the prior palette; re-confirm for this one) and it is the brand anchor.
- [ ] 3.6 `assets/css/styles.css` fix stays scoped to color values only. It is currently unlinked from any page (BL-10 finding: custom CSS lives inline in `partials/head.php` because Tailwind Play CDN only pre-processes `<style>` it can intercept, not external `<link>` sheets) — do not attempt to re-link it or restructure that arrangement here, it is out of scope.

## 4. Mobile-first responsive pass

- [ ] 4.1 Load all 9 pages at 375px, 768px, 1024px, 1440px widths. Note any horizontal scroll, overlapping text, or tap targets under 44×44px.
- [ ] 4.2 Fix what's found. Do not redesign layouts that already work at all four widths.
- [ ] 4.3 The GPS modal specifically: confirm it fits without scrolling on a 375px-tall viewport in landscape (a common real case: phone rotated to read the mini-guide's screenshots).

## 5. Chart.js pin

- [ ] 5.1 `candidato.php` loads `https://cdn.jsdelivr.net/npm/chart.js` with no version. Pin to the exact version currently resolving (`npm view chart.js version` or inspect the network response), e.g. `chart.js@4.4.x`.
- [ ] 5.2 Confirm the chart still renders identically after pinning — a version pin should be a no-op today, only preventing silent future breakage.

## 6. Verify
- [ ] 6.1 `scripts/check-refactor.php`: 9 of 9 pages, both partial checks, still green.
- [ ] 6.2 Every `validate-*.js` script in `scripts/` still passes (district-page rendering must not have introduced a data-shape violation).
- [ ] 6.3 Browser console clean across all 9 pages, including after triggering the GPS denial path.
- [ ] 6.4 `docs/wcag-contrast-audit.md` committed alongside the fixes it justifies — not as an afterthought.

## Notes carried forward (do not fix here)
- `data/distrito.json` has no fields beyond `id`/`nombre`/`provincia`/`region` — no population, no incumbent mayor, no historical results. Any stat card requiring those is BL-16+ scope once such data exists, not an omission here.
- Full data wiring (`sondeos.php` calling `card-sondeo.php` server-side instead of client-side `generateCardHTML()`, `index.php`'s ticker, etc.) stays BL-16. This item only wires `distrito.php`, which had no prior implementation to preserve.
