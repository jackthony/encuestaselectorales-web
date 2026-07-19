# BL-11b — National Home Portal (`index.php`)

## Why

Owner decision, 2026-07-19: the platform launches nationally on 2026-07-20, ahead of the JNE's official candidate lists (2026-08-05), to capture WhatsApp leads from paid traffic. `index.php` today is the BL-10 rebuild of `portal_de_sondeos_ciudadanos.html`, branded "Sondeo ciudadano · Lima 2026" throughout — hero copy, nav, and the district hub are all Lima-scoped. Anyone arriving from a national ad, or navigating back to the home from a non-Lima `distrito.php` page (`bl-11-responsive-wcag`'s hybrid rebuild), lands on a page that contradicts the URL they just left.

A Canvas prototype for the national home exists: `canvas-gemini/portal_nacional_home.html`. It carries the global UBIGEO search (region/provincia/distrito), a two-column hub (own-poll "Encuestas Web Activas" vs. read-only "Últimos Estudios de Campo"), and links directly to `distrito.php?slug=miraflores`-style URLs — the same page this item's sibling change (`bl-11-responsive-wcag`) rebuilds. No prior BL item scopes `index.php`'s own rebuild against this prototype; BL-10 built it against the old, Lima-only one.

## What changes

1. **`index.php` rebuild** from `canvas-gemini/portal_nacional_home.html`, same structural-diff discipline BL-10 used for the original 8 pages.
2. **Global UBIGEO search** (header input + hero input) — wired to `data/distrito.json` client-side for now (national UBIGEO catalog expansion is separate, tracked below as out of scope). Matches on district/province/region name; does not require the full national catalog to exist yet to be useful for Lima's 43 districts today.
3. **"Encuestas Web Activas" hub column** — lists districts with an open online round. Today, with no rounds actually open (BL-13b/BL-14 not shipped), this renders its own empty state ("¿Quieres medir a tu distrito? Usa el buscador global"), not fabricated example cards.
4. **"Últimos Estudios de Campo" hub column** — lists real field studies only. Empty (no cards) until real `resultado.json`-backed campo entries exist; never filled with the `ejemplo` placeholder (see `bl-11c-purge-datos-ficticios`, sequenced to land alongside this item since both touch `index.php`).
5. Nav/hero copy: "Perú 2026" / "Elecciones Regionales y Municipales 2026", not "Lima 2026".

## Explicitly out of scope

- The national UBIGEO catalog itself (`data/distrito.json` expansion beyond Lima's 43 districts to all of Peru's ~1,874) — a data-acquisition task, not a template rebuild. Schema-level UBIGEO support is `bl-13b-encuestas-rondas-schema`'s concern; populating the catalog is neither item's job and has no owner yet. This item's search works correctly against whatever `distrito.json` contains, Lima-only or national, without code changes either way.
- Any backend search API — the search is a client-side filter against the JSON already loaded, same pattern as the rest of the site pre-BL-16.
- The `distrito.php` hybrid rebuild itself — `bl-11-responsive-wcag`.
- Rondas semanales / `tipo` schema — `bl-13b-encuestas-rondas-schema`.

## Success criterion

`index.php` passes `scripts/check-refactor.php` against `portal_nacional_home.html`. The global search returns a working link to `distrito.php?slug=<slug>` for every entry in `data/distrito.json`. Neither hub column renders any card without a corresponding real JSON entry — verified by loading the page with today's actual data (zero open rounds, whatever campo studies exist) and confirming both empty states show correctly, not a fabricated example.
