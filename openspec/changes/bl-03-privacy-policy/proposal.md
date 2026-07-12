## Why

Peru's Ley de Protección de Datos Personales (29733) applies once the site collects real personal data. Today the site collects **zero** data — fully static, no analytics, no forms, no backend. But `BL-18` (analytics) and `BL-25` (own-poll backend) will change that, and `CLAUDE.md` constraint 5 already blocks `BL-25` from activating without this policy in place. Publishing a baseline now — before any collection exists — means the commitment is public and dated ahead of the fact, not written retroactively to justify something already running. It also gives `CLAUDE.md` constraint 2 (never individual-level tracking) a citable, external anchor instead of just an internal rule.

## What Changes

- Add a public "Política de Privacidad" page stating exactly what the site collects and what it never collects, forward-looking (describes future `BL-18`/`BL-25` behavior, not current state — the page says plainly that no data is collected yet).
- List collected data (once active): own-poll responses with explicit consent, cookieless aggregate analytics, hashed IP for anti-abuse dedup only.
- List never-collected data: device/browser fingerprinting, cross-referencing with external sources (electoral roll, social media), raw IP storage, cross-session individual tracking.
- Establish a stable anchor (`#compromiso`) for `BL-06` (methodology page) and `BL-25` (own-poll backend spec) to link to.

## Capabilities

### New Capabilities
- `privacy-policy`: a published policy page (and its content rules) stating current (none) and future data-collection commitments, gating `BL-25`.

### Modified Capabilities
(none)

## Impact

- New static page (`/politica-privacidad.html`, mirrors `BL-02`'s `/politica-editorial.html` structure and reuses `styles.css`).
- No data model, backend, or build-tooling impact — content item, per `docs/engineering-standards.md` §5 (checklist, not TDD).
- Creates a dependency: `BL-06` and `BL-25` specs must link `#compromiso` once they ship. `BL-25` additionally cannot activate until this policy's "full version" is confirmed still accurate at that time (re-review gate, not just existence).
