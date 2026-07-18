# Production debt — shipped-with-known-gaps

> Deliberate shortcuts taken to ship the beta portal fast (owner decision, 2026-07-18). Each item names the gap + the upgrade path. Improve in-flight, don't let these rot.

## PD-01 — Portal loads external assets (breaks the no-external-request stance)
- **What**: `index.html` (the Canvas-designed portal) loads Tailwind, FontAwesome and Google Fonts from CDNs (`cdn.tailwindcss.com`, `cdnjs.cloudflare.com`, `fonts.googleapis.com`).
- **Why it's debt**: `politica-privacidad.html` (live) commits to "zero external requests, no Google Fonts, no third-party tracking" (Ley 29733 posture). Google Fonts leaks visitor IP to Google → the live privacy page is currently inconsistent with the homepage.
- **Interim mitigation**: none yet — shipped as-is for speed.
- **Upgrade path**: compile Tailwind classes to one static `styles.css` (one-time `npx tailwindcss`, no framework in the served site); self-host Inter + Noto Serif as local `woff2` subsets; replace FontAwesome with the ~handful of inline SVG icons actually used. Then drop all three CDN tags.
- **Until fixed**: either soften the privacy-page wording to match reality, OR prioritize this before any marketing push. Owner is aware.

## PD-02 — Portal is not data-driven yet
- **What**: candidate/result data is hardcoded in the portal HTML (Miraflores demo), not read from `data/*.json`.
- **Upgrade path**: wire the portal views to fetch/read `data/distrito.json`, `data/candidato.json`, `data/resultado.json` etc. — the shapes already exist and are validated.

## PD-03 — Inner pages not migrated to the new visual
- **What**: `index.html` uses the new Canvas look; the surviving legal pages (`politica-editorial`, `politica-privacidad`, `fuentes-correcciones`) still use the old style. Methodology + about pages were deleted and not yet rebuilt.
- **Why it matters**: "methodology always visible" is a product principle. The methodology + about content must be rebuilt in the new visual (content preserved in git history + `openspec/`).
- **Upgrade path**: reskin the 3 legal pages to the portal template; rebuild `metodologia` + `quienes-somos` in the new look; fix portal nav/footer links to resolve to them.
