## 1. Laravel foundation

- [x] 1.1 Define the public Laravel controllers, services, and Blade views for `index`, `sondeos`, `distrito`, `encuesta`, `candidato`, and `encuestadoras`.
- [x] 1.2 Wire the Laravel routes so the public portal is served from the framework while the legacy bridge stays available as rollback.
- [x] 1.3 Extract the shared layout, header, footer, and reusable card partials into Laravel Blade components or partials.
- [x] 1.4 Move the public config and helper logic needed by the portal into Laravel-friendly services without hardcoding secrets or media paths.

## 2. Public survey portal parity

- [x] 2.1 Render active survey rounds for district, province, and region scopes with clear territorial labels and canonical URLs for same-named places.
- [x] 2.2 Show candidate name, party name, party logo, and the shared default face fallback on every public candidate card.
- [x] 2.3 Show explicit blocked or empty states when a survey scope has no candidates loaded yet or cannot start yet.
- [x] 2.4 Connect the public survey flow to the existing BL-14 vote endpoint so the vote CTA and duplicate-prevention UX still work on mobile.
- [x] 2.5 Populate the home hub with real active rounds through the 5 August 2026 cutoff and remove any demo or fictitious cards from production rendering.

## 3. Share surfaces and preview metadata

- [x] 3.1 Add Open Graph and Twitter metadata to the home, territory, survey, and candidate pages using real published content only.
- [x] 3.2 Add share actions for Facebook, WhatsApp, and story-sized copy flows on the public survey pages.
- [x] 3.3 Generate or cache story-sized preview assets for surveys and candidates using stored media and neutral fallbacks when assets are missing.

## 4. Data and media pipeline

- [x] 4.1 Build or finish the import path that loads the normalized CSV/JSON catalog into MySQL tables for territories, parties, candidates, and survey rounds.
- [x] 4.2 Seed the first live rounds for the already-approved districts, provinces, and regions so the public portal can show production data immediately.
- [x] 4.3 Resolve missing candidate photos and party logos from Hostinger-backed storage with deterministic fallbacks instead of synthetic placeholders.
- [x] 4.4 Verify that no demo, example, or fictitious markers reach the production database or generated previews.

## 5. Cutover, cleanup, and release

- [ ] 5.1 Route Hostinger production to the root Laravel application's `public/` entrypoint and verify the public routes load correctly.
- [ ] 5.2 Remove or archive the duplicate legacy root pages and bridge files once Laravel reaches parity for the public portal.
- [x] 5.3 Update CI checks to cover Laravel public rendering, mobile vote flow, and the no-fictitious-production-data rule.
- [ ] 5.4 Run final smoke tests on desktop and mobile, then publish the production release.
