# BL-11 — Responsive UI, JS Animations & WCAG Validation

## Why

BL-10 refactored 8 Canvas prototypes into PHP with zero visual change — including their zero-visual-change defects. Three of those are no longer cosmetic once GPS is mandatory (BL-13) and once `distrito.php` stops being a placeholder:

- **The GPS permission-denied path is `alert("Permiso denegado")`.** With GPS mandatory, this is where most visitors stop. It is the single highest-leverage conversion surface on the site, and it currently reads as a dead end, not an invitation to fix the problem.
- **`brand.green` `#15ba75` was adopted wholesale from Canvas, unmeasured.** As text on white it is a WCAG AA suspect. Nothing has confirmed or denied this since BL-04 measured the *previous* palette — a measurement that no longer applies to the current one.
- **Chart.js loads unpinned from jsDelivr** in `candidato.php`. A breaking upstream release silently breaks a live page with no local warning.

`distrito.php` today is a stub (no Canvas source existed at BL-10 time). The owner has since had a district-page prototype built in Canvas (`canvas-gemini/distrito.html`, brief in `openspec/changes/bl-10-php-architecture/BRIEF-distrito-canvas.md`) that is deliberately empty-state-first: 42 of 43 districts have zero registered candidates until the JNE admits lists on 2026-08-05. Wiring that prototype into `distrito.php` is this item's second half.

## What changes

1. **GPS recovery modal** — replace the `alert()` with the reference implementation in `openspec/changes/bl-11-responsive-wcag/reference-modal-rescate.html`, wired into `assets/js/voto-gps.js`'s existing state machine via the `gps:reintentar` / `gps:cancelar` events it already emits.
2. **`distrito.php` rebuild** from `canvas-gemini/distrito.html` — same structural-diff discipline BL-10 used, not a freehand rewrite.
3. **WCAG AA contrast pass** — measure every foreground/background pair now in use across all 9 pages (the 8 from BL-10 plus the rebuilt `distrito.php`), fix what fails.
4. **Mobile-first responsive audit** — the prototypes were designed at desktop width first; verify and fix breakpoints.
5. **Chart.js version pin.**
6. **Focus trap + keyboard nav** for the GPS modal (already implemented in the reference file — port it, don't re-derive it).

## Explicitly out of scope

- Wiring `data/*.json` into any page beyond `distrito.php`'s own data (that is BL-16 for the rest of the site).
- Any change to `/api/`, the DB, or GPS *mandatoriness* — BL-13/BL-14 own the backend; this item only fixes the client-side UX around an existing constraint.
- Removing CDN dependencies — owner decision 2026-07-18, stays.
- A `vote_tier` concept of any kind — GPS is mandatory, decided, not reopened here.

## Success criterion

Every page passes `scripts/check-refactor.php` (including the new `distrito.php` comparison), every measured text/background pair in `docs/wcag-contrast-audit.md` is ≥4.5:1 (≥3:1 for non-text UI), and the GPS denial path is reachable and operable by keyboard alone, verified in a real browser — not asserted from reading the code.
