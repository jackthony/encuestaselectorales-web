# BL-11 — Responsive UI, JS Animations & WCAG Validation

## Why

BL-10 refactored 8 Canvas prototypes into PHP with zero visual change — including their zero-visual-change defects. Three of those are no longer cosmetic once GPS is mandatory (BL-13) and once `distrito.php` stops being a placeholder:

- **The GPS permission-denied path is `alert("Permiso denegado")`.** With GPS mandatory, this is where most visitors stop. It is the single highest-leverage conversion surface on the site, and it currently reads as a dead end, not an invitation to fix the problem.
- **`brand.green` `#15ba75` was adopted wholesale from Canvas, unmeasured.** As text on white it is a WCAG AA suspect. Nothing has confirmed or denied this since BL-04 measured the *previous* palette — a measurement that no longer applies to the current one.
- **Chart.js loads unpinned from jsDelivr** in `candidato.php`. A breaking upstream release silently breaks a live page with no local warning.

`distrito.php` today is a stub (no Canvas source existed at BL-10 time). The owner has since had a district-page prototype built in Canvas (`canvas-gemini/distrito.html`, brief in `openspec/changes/bl-10-php-architecture/BRIEF-distrito-canvas.md`) that is deliberately empty-state-first: 42 of 43 districts have zero registered candidates until the JNE admits lists on 2026-08-05. Wiring that prototype into `distrito.php` is this item's second half.

**Superseding update, 2026-07-19.** Owner decision: scale is now national (all of Peru, not Lima-only) launching 2026-07-20, and the empty-state CTA changes from passive notification to active lead capture — visitors are asked to propose their own district's candidates via WhatsApp, ahead of the JNE's official list, because paid traffic starts arriving before 2026-08-05. A second, superseding Canvas prototype exists for this: `canvas-gemini/tablero_electoral_growth_hack_hibrido.html`. It is one hybrid template (growth-hack CTA, gated vote widget, campo-studies sidebar as independently toggling blocks) rather than `distrito.html`'s three mutually-exclusive stacked states. `distrito.html`'s candidate-roster cards and result-bar layout remain valid as reusable sub-components inside the hybrid template, not as the page structure itself. This item's `distrito.php` rebuild targets the hybrid file. A third prototype, `canvas-gemini/portal_nacional_home.html`, covers the equivalent national rebuild of `index.php` — tracked as its own item, `bl-11b-portal-nacional-home`, not folded in here, since it's a different page with its own diff surface.

This is now the highest-priority piece of this item, ahead of the GPS modal and WCAG audit below — it ships first because paid traffic hits it starting tomorrow. Sections below are unchanged and still ship, just after.

## What changes

1. **GPS recovery modal** — replace the `alert()` with the reference implementation in `openspec/changes/bl-11-responsive-wcag/reference-modal-rescate.html`, wired into `assets/js/voto-gps.js`'s existing state machine via the `gps:reintentar` / `gps:cancelar` events it already emits.
2. **`distrito.php` rebuild** from `canvas-gemini/tablero_electoral_growth_hack_hibrido.html` (superseding `distrito.html`, see above) — same structural-diff discipline BL-10 used, not a freehand rewrite. Ships with the vote widget block present in markup but never rendered in production (see design.md) — `/api/votar.php` doesn't exist yet (BL-14), and a form with nowhere safe to submit is exactly the kind of fake functionality the owner has ruled out.
3. **WCAG AA contrast pass** — measure every foreground/background pair now in use across all 9 pages (the 8 from BL-10 plus the rebuilt `distrito.php`), fix what fails.
4. **Mobile-first responsive audit** — the prototypes were designed at desktop width first; verify and fix breakpoints.
5. **Chart.js version pin.**
6. **Focus trap + keyboard nav** for the GPS modal (already implemented in the reference file — port it, don't re-derive it).

## Explicitly out of scope

- Wiring `data/*.json` into any page beyond `distrito.php`'s own data (that is BL-16 for the rest of the site).
- Any change to `/api/`, the DB, or GPS *mandatoriness* — BL-13/BL-14 own the backend; this item only fixes the client-side UX around an existing constraint.
- Removing CDN dependencies — owner decision 2026-07-18, stays.
- A `vote_tier` concept of any kind — GPS is mandatory, decided, not reopened here.
- Any fictitious/example poll, pollster, or result content anywhere on the site — that purge is `bl-11c-purge-datos-ficticios`, a separate item, since it touches pages this item doesn't otherwise open (`index.php`, `sondeos.php`, `encuesta.php`, `candidato.php`).
- The `encuestas`/rondas-semanales/`tipo` (online_propia vs campo_externa) database schema — tracked as `bl-13b-encuestas-rondas-schema`, sequenced after this item and before BL-14, since `distrito.php` here reads static JSON, not a live DB.

## Success criterion

Every page passes `scripts/check-refactor.php` (including the new `distrito.php` comparison), every measured text/background pair in `docs/wcag-contrast-audit.md` is ≥4.5:1 (≥3:1 for non-text UI), and the GPS denial path is reachable and operable by keyboard alone, verified in a real browser — not asserted from reading the code.
