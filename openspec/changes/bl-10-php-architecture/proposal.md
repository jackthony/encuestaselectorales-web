# BL-10 — PHP Architecture, Naming & Cleanup

## Why

`canvas-gemini/` holds 8 validated HTML prototypes. They are the approved design — palette, animations, layout are final and must not change. But as files they are unshippable:

- **Every page redefines the whole design system.** Each file carries its own inline `tailwind.config`. They already disagree: three different page backgrounds (`#fcfcfc`, `#f8fafc`, `#f4f5f3`), and the same green written both `#15ba75` and `#15BA75`. There is no single place to fix a color.
- **The same JS is copy-pasted 5-6 times.** Clock (`setInterval`, `es-ES`), mobile menu, IntersectionObserver scroll-reveal — duplicated per file, drifting per file.
- **Party colors are hardcoded inline in 3 files** (`#B22222`, `#00A99D`, `#F58220`) even though `data/partido.json` already exists and is validated.
- **All content is hardcoded.** Candidate names, districts, percentages, sample sizes and dates are literals in HTML or JS (`mockData`, `datosMeses`), duplicating the shapes already in `data/*.json`.
- **Nothing is reusable.** Header, footer and the GPS vote widget exist as N independent copies.

Every item after this one (BL-11 responsive/WCAG, BL-13 schema, BL-14 vote endpoint) writes into these files. Refactoring after they land means refactoring N times instead of once.

## What changes

Convert the 8 prototypes into an MVC-lite PHP structure with single-responsibility partials, one stylesheet, one JS bundle, and one Tailwind config — **with zero visual change**.

This item is a pure structural refactor. It adds no feature, changes no copy, moves no pixel. The DB, the vote endpoint, and the data hookups are BL-13, BL-14 and BL-16+.

## Explicitly out of scope

- Wiring views to `data/*.json` (BL-16+). Hardcoded content stays hardcoded, just relocated to partials.
- Removing the CDN dependencies (Tailwind, FontAwesome, Google Fonts). Owner decision 2026-07-18: keep as-is, the visual is working.
- Any `/api/` endpoint or DB connection (BL-13, BL-14).
- Responsive fixes and WCAG remediation (BL-11) — this item preserves current behavior, including its current defects.

## Success criterion

Every refactored PHP page emits the same set of DOM elements and CSS classes as its canvas original. Verified by an automated check, not by eyeballing.
