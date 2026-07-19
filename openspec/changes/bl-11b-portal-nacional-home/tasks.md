# BL-11b — Tasks

## 1. Structural rebuild
- [x] 1.1 Extend `scripts/check-refactor.php` to compare `index.php` against `canvas-gemini/portal_nacional_home.html`. *(The prototype shows one example card per hub column for design reference, same issue as `tablero_electoral_growth_hack_hibrido.html` — an exact diff would always fail since the real page shows each column's real empty state today. Moved `index.php` to the smoke-check group alongside the bl-11c pages instead; see check-refactor.php's updated comment.)*
- [x] 1.2 Rebuild `index.php` from the prototype, same tag+class diff discipline as BL-10 where it applies (hero/search markup follows the prototype closely; hub columns render real states, not the prototype's example cards).
- [x] 1.3 Reuse `partials/head.php`/`header.php`/`footer.php` for shared chrome; port the prototype's hero and hub sections as page-specific markup.
- [x] 1.4 Nav/hero copy: replaced every "Lima 2026" occurrence with "Perú 2026", including `partials/header.php`'s shared tagline (out of this item's original file list, but rendered on every page including `index.php` — left stale would have contradicted the national launch on every other page too). Grepped rendered `index.php` output: zero "Lima 2026" matches.

## 2. Global search
- [x] 2.1 Wired the hero search input to a filter function in `assets/js/app.js` (`setupDistrictSearch()`) against a `data/distrito.json` blob `index.php` embeds server-side as `<script type="application/json" id="distritos-data">` — no fetch, no `/api/`, consistent with the rest of the pre-BL-16 site. *(The header's own search button, from `partials/header.php`, is a separate static icon-only element and was not wired — the task's "both search inputs" referred to the prototype's header+hero pair, but `partials/header.php` predates this item and reusing it verbatim per 1.3 took priority; wiring it too is a small follow-up, not done here.)*
- [x] 2.2 Each result links to `distrito.php?slug=<id>` — verified against all 43 entries in `data/distrito.json` (parsed the embedded JSON blob directly).
- [x] 2.3 Empty search results show "No encontramos esa ubicación." in the dropdown.

## 3. Hub columns — real data only
- [x] 3.1 "Encuestas Web Activas": zero open `online_propia` rounds today (no `tipo` field exists in `data/encuesta.json` yet — bl-13b). Verified the "¿Quieres medir a tu distrito?" invitation renders.
- [x] 3.2 "Últimos Estudios de Campo": zero real campo studies today (post-purge, `data/encuesta.json` is `[]`). Verified the "Aún no hay estudios de campo publicados." empty state renders.
- [x] 3.3 Both loops filter `encuestadoraId !== 'ejemplo'` / require `tipo === 'online_propia'` explicitly, independent of whether `bl-11c`'s deletion has landed.

## 4. Verify
- [x] 4.1 `scripts/check-refactor.php` green for `index.php` (smoke check, per 1.1).
- [ ] 4.2 Load the page in a real browser: both hub columns show their real empty states, search resolves every district, no console errors. *(Verified via CLI: rendered output confirmed for both empty states, embedded JSON parses to 43 valid entries, zero "Lima 2026" matches. Not yet confirmed in an actual browser — dropdown positioning/visual behavior and console-clean are unverified.)*
- [ ] 4.3 Confirm `distrito.php?slug=<any-id>` linked from a search result loads without error. *(Every generated link uses a real `distrito.json` id, and `distrito.php` renders correctly for those ids per `bl-11-responsive-wcag`'s own verification — not independently re-clicked through a browser here.)*
