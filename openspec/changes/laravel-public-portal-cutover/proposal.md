## Why

The public site has outgrown the legacy PHP root: survey pages, candidate rendering, sharing metadata, and security logic are split across duplicate files, while the new Laravel scaffold already exists and should become the single production surface. We need one framework-backed portal that can show every active survey through the final cutoff date, keep the content real, and remain maintainable under Hostinger with clean architecture and server-side security.

## What Changes

- Move the public portal to Laravel as the primary production application.
- Serve the national home, district, province, and region survey pages from the framework instead of the legacy root.
- Render every live survey with explicit territorial level labels so same-named places stay distinguishable.
- Show candidate names together with party names, party logos, and photo fallbacks when a candidate image is missing.
- Keep empty-state behavior explicit when a district, province, or region has no candidates yet, so the vote flow does not start prematurely.
- Add share-friendly metadata and UI for Facebook, WhatsApp states, and story-sized sharing assets.
- Preserve the anti-abuse and privacy controls already defined for votes and request handling.
- Remove or archive duplicate legacy files and keep the root close to empty after the framework cutover.
- Keep Hostinger deployment framework-first, with secrets and runtime config outside the web root.

## Capabilities

### New Capabilities
- `public-survey-portal`: Framework-backed public survey pages for the home feed and territorial pages, including live survey cards, candidate display, party branding, empty states, and shareable presentation.

### Modified Capabilities
- `national-home-portal`: The home page now becomes the Laravel-backed entry point for all public survey rounds, with territorial labels and live survey cards sourced from the framework.
- `no-fictitious-production-data`: The framework portal must continue to show only real survey and candidate data, with empty states instead of fabricated or placeholder content.

## Impact

- The Laravel application at the repository root becomes the production source of truth.
- Public PHP pages in the repo root are reduced or removed after cutover.
- Existing seed/import scripts and normalized catalog data continue to feed the public portal.
- Image storage on Hostinger remains part of the delivery model for candidate photos, party logos, and social share assets.
- Security config, DB config, and vote protections remain outside the web root and continue to be enforced server-side.
- The CI workflow should validate both the PHP refactor and the new portal behavior after the cutover.
