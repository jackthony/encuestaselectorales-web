# BL-10 — Tasks

Sequence is binding. Task 1 writes the failing check before any refactor exists.

## 1. The failing check (before any refactor)
- [ ] 1.1 Write `scripts/check-refactor.php`: for each page, parse HTML via `DOMDocument`, emit a sorted multiset of `tag + class-list`, compare PHP render vs its `canvas-gemini/` original. Exit 1 on any diff.
- [ ] 1.2 Feed it the palette exceptions from design.md Decision 1 (`#fcfcfc`/`#f8fafc` → `#f4f5f3`) as the only allowed deltas.
- [ ] 1.3 **Run it. Watch it fail** — no `.php` pages exist yet, so all 8 comparisons must error. A passing check at this stage means the check is broken.
- [ ] 1.4 Commit the failing check.

## 2. Skeleton
- [ ] 2.1 Create `partials/`, `includes/`, `assets/css/`, `assets/js/`, `assets/img/candidatos/`.
- [ ] 2.2 `git mv styles.css assets/css/styles.css`.
- [ ] 2.3 `partials/head.php` — the single `tailwind.config` block, reconciled per Decision 1, plus the 3 CDN tags verbatim from the prototypes.

## 3. Extract shared UI
- [ ] 3.1 Diff the header markup across all 8 prototypes. Record any differences found. Newest prototype wins. → `partials/header.php`.
- [ ] 3.2 Same for footer → `partials/footer.php`.
- [ ] 3.3 `flujo_de_votaci_n_gps.html` modal (4 states: soft-ask, loading, smart-match, success) → `partials/widget-gps.php`. Markup only — its JS goes to 4.2.
- [ ] 3.4 Result card from `portal_de_sondeos_ciudadanos.html` → `partials/card-sondeo.php`.

## 4. Consolidate JS
- [ ] 4.1 Diff the 5-6 copies of clock / mobile menu / IntersectionObserver. Record differences. → `assets/js/app.js`.
- [ ] 4.2 GPS state machine from the vote flow → `assets/js/voto-gps.js`. Behavior unchanged, including the current `alert()` on permission denial — BL-11 owns UX fixes, not this item.
- [ ] 4.3 Every DOM handler guards its target (`if (!el) return;`) per engineering-standards §3 — partials now load on pages that lack those elements.
- [ ] 4.4 Party color literals (`#B22222`, `#00A99D`, `#F58220`) removed from the 3 files; `includes/helpers.php` `partyColor()` reads `data/partido.json`. **Verify every party in the prototypes exists in that JSON before deleting a literal** — log any miss, do not invent a color.

## 5. Build the 8 pages
- [ ] 5.1 `index.php`, `sondeos.php`, `distrito.php`, `encuesta.php`, `candidato.php`, `encuestadoras.php`, `metodologia.php`, `quienes-somos.php` — each includes the partials, keeps its own body verbatim.
- [ ] 5.2 Hardcoded content stays hardcoded (BL-16 wires data). Relocate, don't rewrite.
- [ ] 5.3 Fix internal links to the new `.php` names.

## 6. Green
- [ ] 6.1 Run `scripts/check-refactor.php`. All 8 pass.
- [ ] 6.2 `php -S localhost:8000`, load all 8, browser console clean (zero errors).
- [ ] 6.3 Commit the refactor.

## 7. Cleanup (last, only after 6 is committed)
- [ ] 7.1 Delete `canvas-gemini/`. Recoverable at commit `2a6e18f`.
- [ ] 7.2 Point `scripts/check-refactor.php` at that commit's blobs (`git show 2a6e18f:canvas-gemini/...`) so the check survives the deletion.
- [ ] 7.3 Update `docs/backlog.md`: BL-10 → `done`.

## Notes carried forward (do not fix here)
- Prototypes contradict `canvas-gemini/PROMPT-portal.md`: `encuesta.php` is titled "Estudio Ipsos Agosto 2026" with invented numbers, `encuestadoras.php` lists real JNE pollsters, `index.php` includes GORE Ucayali (a region, out of the Lima-districts scope lock). Publishing invented figures under a real pollster's name is the legal exposure CLAUDE.md flags. **Blocks going live, not this refactor** — file as BL-11 or earlier.
- `perfil_de_candidato.html` loads Chart.js from jsDelivr with no version pin — a breaking upstream release breaks the page silently. Pin it in BL-11.
