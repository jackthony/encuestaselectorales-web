## 1. Checklist first (content item — no red/green test, per `docs/engineering-standards.md` §5 and `CLAUDE.md` constraint 7)

- [x] 1.1 Write the acceptance checklist from `specs/privacy-policy/spec.md`: title mentions "política de privacidad"; "today collects zero data" statement present; "cuando activo" list has ≥3 items; "nunca recolectamos" list has ≥4 items, commitment-framed not implementation-locked; `#compromiso` anchor exists; HTML structurally valid; contrast ≥4.5:1 (new BL-04 tokens); heading hierarchy correct; landmarks present; responsive at 3 breakpoints.
- [x] 1.2 Confirm the checklist is checkable against a page that doesn't exist yet (every item currently fails) — content-item equivalent of "red."

## 2. Content

- [x] 2.1 Create `politica-privacidad.html` at the site root, Spanish content, `lang="es"`, reusing `styles.css` (BL-04's validated tokens).
- [x] 2.2 Write the "hoy no recolectamos nada" statement.
- [x] 2.3 Write the "cuando esté activo, recolectamos" list (3 items: consented poll responses, cookieless analytics, hashed IP anti-abuse only).
- [x] 2.4 Write the "nunca recolectamos" list, commitment-framed (4 items: fingerprinting, cross-referencing, raw IP, cross-session tracking).
- [x] 2.5 Wrap the collect/never-collect section in `id="compromiso"`.
- [x] 2.6 Responsive layout (reused `BL-02`/`BL-04` patterns, no new CSS).

## 3. Verify against checklist + fix cross-references

- [x] 3.1 Re-ran the 1.1 checklist against the finished page — every item passes.
- [x] 3.2 Structural HTML check: no duplicate ids, doctype html, viewport correct, 0 images without alt.
- [x] 3.3 Contrast verified using BL-04's exact tokens (not re-derived): text 18.67:1, muted 7.53:1, `--accent-text` 5.12:1 — all ≥4.5:1. Confirmed via computed style that `.tag`/`footer a` render `rgb(37,106,191)` (`--accent-text`), never raw `--accent` — the BL-04 mistake did not repeat.
- [x] 3.4 Heading hierarchy H1→H2→H2 confirmed. Landmarks `main`/`footer`/`section#compromiso` all present.
- [x] 3.5 `docs/backlog.md` `BL-25` entry updated: now explicitly says "re-review gate — not just 'BL-03 exists,' this item's own tasks must re-read `politica-privacidad.html`..." per spec requirement.
- [x] 3.6 `docs/backlog.md` BL-03 Status updated to `done`.
