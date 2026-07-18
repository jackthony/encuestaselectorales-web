# BL-10 — Design

## Target structure

```
/
├── index.php                    <- portal_de_encuestas.html
├── sondeos.php                  <- portal_de_sondeos_ciudadanos.html
├── distrito.php                 <- ?slug=miraflores
├── encuesta.php                 <- detalle_de_encuesta.html      (?id=)
├── candidato.php                <- perfil_de_candidato.html      (?dni=)
├── encuestadoras.php            <- directorio_de_encuestadoras.html
├── metodologia.php              <- metodolog_a.html
├── quienes-somos.php            <- qui_nes_somos_autoridad.html
│
├── partials/
│   ├── head.php                 meta, CDN tags, THE single tailwind.config
│   ├── header.php               nav + "Distritos de Lima" dropdown
│   ├── footer.php
│   ├── widget-gps.php           <- flujo_de_votaci_n_gps.html (modal + 4 steps)
│   └── card-sondeo.php          repeated result card
│
├── includes/
│   ├── data.php                 read + decode data/*.json
│   └── helpers.php              esc(), pct(), partyColor()
│
├── assets/
│   ├── css/styles.css           the only stylesheet
│   ├── js/app.js                clock, mobile menu, scroll reveal
│   ├── js/voto-gps.js           GPS state machine
│   └── img/candidatos/          <dni>.webp
│
├── data/*.json                  unchanged (already validated)
└── config/                      gitignored, created in BL-13
```

Root `styles.css` moves to `assets/css/styles.css`. Its current tokens are **superseded** by the Canvas palette (owner decision) — it becomes the file the Tailwind-adjacent custom CSS lives in, not a competing token set.

## Decision 1 — Reconciling the three divergent palettes

The prototypes disagree. Resolution, by frequency of use across the 8 files:

| Token | Value | Source |
|---|---|---|
| `brand.blue` | `#102f86` | unanimous across all 8 |
| `brand.green` | `#15ba75` | lowercase form, 5 files vs 1 |
| `brand.bg` | `#f4f5f3` | the GPS widget + sondeos value; warmest, closest to the existing site |
| `brand.card` | `#ffffff` | unanimous |

`#fcfcfc` and `#f8fafc` are dropped. They differ from `#f4f5f3` by under 2% luminance — the change is invisible in isolation and the consistency is worth more than the delta.

**This is the one place BL-10 is allowed to alter a rendered pixel**, and only because the prototypes contradict each other, so *some* value must lose. Everything else is byte-identical.

## Decision 2 — Naming

- **Files**: `kebab-case.php`, Spanish slugs (they become public URLs — `metodologia.php`, `quienes-somos.php`).
- Fixes the broken encoding in `metodolog_a.html`, `flujo_de_votaci_n_gps.html`, `qui_nes_somos_autoridad.html` (each lost an accented character to `_`).
- **PHP**: `camelCase` functions/vars, `PascalCase` classes (per `docs/engineering-standards.md` §4).
- **DOM ids / CSS**: `kebab-case`. Existing Tailwind utility classes are **never renamed** — they are the design.

## Decision 3 — Why no router

A front controller (`index.php` + rewrite rules) is the "correct" MVC answer. Rejected: 8 flat `.php` files on Apache shared hosting need zero `.htaccess`, zero rewrite debugging, and are readable by anyone. A router earns its place when routes outnumber files or need middleware. Neither is true. Revisit at BL-23 (scale to 42 districts) if `distrito.php?slug=` becomes a real SEO problem.

## Decision 4 — The regression check

The stated fear is breaking the validated design. Eyeballing 8 pages is not a check. `scripts/check-refactor.php` extracts, for each page, the multiset of CSS classes and element tags emitted, and diffs the PHP render against the canvas original.

- **Ignores**: whitespace, attribute order, comments, and the palette values reconciled in Decision 1.
- **Fails on**: a missing element, a dropped class, a renamed utility.

This runs before the refactor is called done, and it is written **first** — it must fail against an empty `partials/` before any extraction begins.

## Risks

| Risk | Mitigation |
|---|---|
| Refactor silently drops a class → subtle visual break | Decision 4's check; `canvas-gemini/` committed at `2a6e18f` as the reference |
| Inline JS in prototypes has per-page differences hidden by copy-paste drift | Diff the 5 copies of each function before consolidating; if they differ, the newest prototype wins and the difference is recorded in `tasks.md` |
| `data/*.json` shapes don't cover what the prototypes hardcode | Out of scope here — BL-16 wires data. BL-10 only relocates literals. Any gap gets logged, not fixed |
| Deleting `canvas-gemini/` too early | Deletion is the last task, after the check passes and the refactor is committed |
