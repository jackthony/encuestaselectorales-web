## Context

The repository already contains two parallel worlds: a legacy PHP portal in the repo root and a Laravel 12 scaffold in `laravel-app/` that currently acts mostly as a bridge. The public product still needs to show the real survey feed, territorial survey pages, candidate lists, party branding, empty states when data is missing, and social sharing surfaces, while keeping the anti-abuse and secret-isolation rules already established in prior changes.

Hostinger can host the PHP stack and has enough storage for media, so the migration does not need an external platform change. The main problem is architectural: the same public experience is still split across duplicate root pages, helper files, and bridge routes, which makes growth and cleanup harder every week.

## Goals / Non-Goals

**Goals:**
- Make Laravel the production-facing application for the public portal.
- Render every active survey through the final cutoff date with territorial disambiguation.
- Show candidate name, party name, party logo, and photo fallback everywhere a candidate appears.
- Surface explicit empty states and blocked-start states when a survey exists but cannot yet run.
- Add reusable share UI and share metadata for Facebook, WhatsApp, and story-sized social assets.
- Keep the anti-hack controls from BL-13/BL-14 intact during and after the cutover.
- Remove or archive legacy root files once the Laravel pages reach parity.

**Non-Goals:**
- Building the BL-15 admin dashboard in this change.
- Reworking the vote security model again.
- Creating a brand-new scraping pipeline for JNE/ONPE if the current import path already covers the required batch.
- Introducing Node.js build tooling in production.

## Decisions

### Laravel becomes the single public entrypoint
Laravel will own the public portal routes, controllers, services, and Blade views. The root PHP pages will remain only as a temporary compatibility surface until parity is reached, then be removed or archived.

Alternatives considered:
- Keep the legacy root as the production app and embed Laravel only for helpers. Rejected because it preserves the current duplication.
- Rewrite everything at once and delete legacy immediately. Rejected because it increases cutover risk and makes verification harder.

### Public data comes from a small set of domain services
The public portal should not query raw arrays from every view. Instead, the framework will expose survey rounds, candidate data, party branding, empty-state rules, and share metadata through dedicated services or repositories, with controllers staying thin.

Alternatives considered:
- Let views read `includes/data.php` and `includes/encuestas.php` directly. Rejected because the framework would just repeat the legacy architecture.
- Put the logic directly in controllers. Rejected because it grows controller fatness and makes reuse harder.

### Candidate photos and party logos use stored media with fallbacks
Candidate images will keep using a default face fallback when a photo is missing. Party logos should come from the normalized catalog or stored media path, with a neutral fallback if a logo is unavailable.

Alternatives considered:
- Require every image to exist before publishing. Rejected because the site needs to publish as data arrives in batches.
- Generate initials-only avatars for candidates. Rejected because the user wants photos when available and a clear default face when not.

### Share assets are first-class media, not ad-hoc markup
The portal will expose reusable share metadata and share imagery for each survey and candidate view. The implementation should support static or cached generated images under Hostinger storage so share cards do not depend on client rendering.

Alternatives considered:
- Rely on browser screenshots or client-side share cards. Rejected because social crawlers need server-side metadata.
- Hardcode one generic share image for the whole site. Rejected because the user needs per-survey miniatures.

### Legacy cleanup happens after parity, not before
The root can only be cleaned once the Laravel routes render the same public experiences and the tests prove parity. Until then, the bridge remains a rollback path.

Alternatives considered:
- Delete legacy early. Rejected because it removes a safe rollback surface.
- Keep legacy permanently. Rejected because it preserves the clutter the user explicitly wants removed.

### Security stays server-side and outside the web root
Secrets, DB credentials, and vote-security settings remain outside `public_html`, and the existing anti-abuse controls stay server-side. The framework migration must not weaken the current rate limit, encryption, or validation rules.

Alternatives considered:
- Move secrets into `.env` under the web root. Rejected because it violates the current secret-isolation rule.
- Trust client-supplied fingerprints or sharing parameters for security. Rejected because the current design already proved those are forgeable.

## Risks / Trade-offs

- **[Risk]** The framework cutover can regress a public page while the bridge is still active. → **Mitigation:** migrate page-by-page with parity checks and keep the bridge until the route is proven.
- **[Risk]** Missing party logos or candidate photos can create uneven visual quality. → **Mitigation:** use deterministic fallbacks and keep the portal publishable while uploads arrive in batches.
- **[Risk]** Social share assets can increase storage usage and deployment complexity. → **Mitigation:** store generated assets in Hostinger-backed media storage and cache them by survey/candidate id.
- **[Risk]** The data import can lag behind the public portal. → **Mitigation:** keep the normalized import pipeline separate from the UI and seed only the verified public rounds.
- **[Risk]** Root cleanup can break Hostinger routing if done too early. → **Mitigation:** switch the docroot only after the Laravel routes are verified in production-like checks.

## Migration Plan

1. Build Laravel controllers, services, and Blade views for the public portal while legacy pages remain intact.
2. Connect the new views to the normalized catalog and active-round data already in MySQL.
3. Add shared candidate/party rendering, empty states, and share UI in reusable partials/components.
4. Generate or cache share images and social metadata for the public pages.
5. Validate route-by-route parity against the legacy pages.
6. Point Hostinger to `laravel-app/public` as the production entrypoint.
7. Archive or delete the legacy root pages and duplicate bridge paths once the framework is fully serving production.

Rollback strategy:
- Keep the legacy root and bridge untouched until the new Laravel routes are verified.
- If a public page regresses, restore the bridge route or docroot mapping first, then fix the Laravel view.

## Open Questions

- Which share-image strategy should be the default for production: cached generated files under Laravel storage, or a dedicated media bucket on Hostinger?
- Should the missing-logo fallback remain a neutral placeholder, or should the public portal show a branded temporary badge until uploads arrive?
- Should the final cutover archive every legacy public PHP page, or keep a minimal compatibility shim for a short safety window?
