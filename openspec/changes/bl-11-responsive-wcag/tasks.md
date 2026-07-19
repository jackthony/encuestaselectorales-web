# BL-11 — Tasks

Two independent workstreams (`distrito.php` rebuild, GPS recovery modal) plus
one audit (contrast) that touches everything already shipped. Sequence
matters within each section; sections 1 and 2 can interleave, section 3
needs both finished first since it audits the union of all 9 pages.

## 1. Rebuild `distrito.php` from the Canvas prototype

`canvas-gemini/distrito.html` exists (brief: `openspec/changes/bl-10-php-architecture/BRIEF-distrito-canvas.md`). It shows all 3 states stacked with labeled dividers for comparison — the live page shows exactly one, chosen at render time by what data exists for the requested district.

- [ ] 1.1 Extend `scripts/check-refactor.php`'s comparison to `distrito.php` against `canvas-gemini/distrito.html`, same tag+class multiset diff BL-10 used for the other 8. **Run it, confirm it fails** against the current stub before touching the page — same discipline as BL-10 task 1.
- [ ] 1.2 Normalize `#15BA75` → `#15ba75` in the prototype before diffing (BL-10 already fixed this casing everywhere else; the new file reintroduces it in a couple of spots per the 2026-07-18 audit).
- [ ] 1.3 Split the prototype's 3 stacked states into 3 conditionally-rendered branches in `distrito.php`, driven by what `includes/data.php` returns for the requested `?id=`:
  - No candidates for this district → empty state (JNE calendar, WhatsApp notify CTA)
  - Candidates exist, no `resultado.json` entry for their poll → candidate directory state
  - Both exist → results state with methodology attached to every number
- [ ] 1.4 The empty state is the one nearly every district hits today (42 of 43). Verify it renders correctly for a district with zero `data/candidato.json` entries — not just for Miraflores, which is the one exception.
- [ ] 1.5 The 2022 Miraflores candidates carry `"activo": false`. Render an explicit "candidatura 2022, no vigente para 2026" label — do not let a false-labeled historical candidate look current. This is a correctness requirement, not styling.
- [ ] 1.6 Header/footer: confirm the prototype uses cluster B chrome (`portal_de_sondeos_ciudadanos.html`'s header — "Distritos de Lima ▾", search button). It does, per the 2026-07-18 audit. Reuse `partials/header.php`/`footer.php` verbatim; do not hand-copy markup from the prototype for these two regions.
- [ ] 1.7 Include `partials/widget-gps.php` in the state where candidates exist (states 2 and 3). Do not include it in the empty state — there is nothing to vote for yet.
- [ ] 1.8 Party colors via `includes/helpers.php`'s `partyColor()`, same as the rest of the site — no hardcoded hex.
- [ ] 1.9 Legal scrub pass identical to BL-10 section 6: grep the new page for `Ipsos|Datum|CPI|IEP`; any hit outside `encuestadoras.php`-style factual listing gets attributed to the `ejemplo` entry.
- [ ] 1.10 Run the check. Green.

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
