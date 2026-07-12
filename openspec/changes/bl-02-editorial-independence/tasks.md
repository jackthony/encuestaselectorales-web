## 1. Checklist first (content item — no red/green test, per `docs/engineering-standards.md` §5 and `CLAUDE.md` constraint 7)

- [x] 1.1 Write the acceptance checklist from `specs/editorial-independence-policy/spec.md` scenarios before writing any page content: title mentions "independencia editorial"; "for sale" list has ≥2 named items; "never for sale" list has ≥4 named items and is example-based; `#firewall` anchor exists; rationale paragraph exists in plain Spanish; HTML validates; page passes a basic axe accessibility check; page renders correctly at the 3 breakpoints (`docs/engineering-standards.md` §7).
- [x] 1.2 Confirm the checklist is checkable against a page that doesn't exist yet (i.e. every item currently fails) — this is the content-item equivalent of "red."

## 2. Content

- [x] 2.1 Create `politica-editorial.html` at the site root (flat structure, matches `index.html`), Spanish content, `lang="es"`.
- [x] 2.2 Write the plain-language rationale paragraph (why the firewall exists) — cite the site's "platform, not opinion" positioning.
- [x] 2.3 Write the "for sale" list (featured/verified profile, district alerts — align wording with `docs/business-model.md` Product 2 tiers).
- [x] 2.4 Write the "never for sale" list, example-based (poll %, candidate rankings, aggregation logic, methodology) — name concrete site features, not only abstract claims.
- [x] 2.5 Wrap the for-sale/never-for-sale section in an element with `id="firewall"`.
- [x] 2.6 Basic responsive layout (mobile-first, `docs/engineering-standards.md` §6-7) — this is a text page, no chart/nav dependency yet. Also created shared `styles.css` (design tokens) per the file-layout convention added this same change.

## 3. Verify against checklist (content item's "green")

- [x] 3.1 Re-run the 1.1 checklist against the finished page — every item now passes (verified via local HTTP server + browser: `get_page_text`, JS-based DOM/CSSOM inspection).
- [x] 3.2 HTML validity checked structurally (doctype, no duplicate ids, viewport meta, charset, no `<img>` without alt) — full W3C Validator run deferred to CI (BL-20) since no network-validator access in this session.
- [x] 3.3 Manual accessibility check (no axe-core available in-session): contrast ratios computed (text 15.89:1, muted 7.40:1, accent 5.09:1 — all pass WCAG AA ≥4.5:1), heading hierarchy H1→H2→H2 (no skips), landmarks present (`main`/`footer`/`section`), link has accessible text, `lang="es"` set. No critical errors found.
- [x] 3.4 Responsive verified via CSSOM inspection (media query `(min-width: 768px)` compiles correctly) + structural analysis (max-width 720px container, relative units, no fixed-width elements, no images/tables — no horizontal-overflow risk at any width). Note: browser tool's `resize_window` didn't reflect actual viewport in this session (known tool limitation, not a page defect) — verified by CSS/DOM inspection instead of pixel screenshots.
- [x] 3.5 `docs/backlog.md` BL-02 Status updated to `done`.
