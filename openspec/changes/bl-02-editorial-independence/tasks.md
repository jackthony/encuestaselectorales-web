## 1. Checklist first (content item — no red/green test, per `docs/engineering-standards.md` §5 and `CLAUDE.md` constraint 7)

- [ ] 1.1 Write the acceptance checklist from `specs/editorial-independence-policy/spec.md` scenarios before writing any page content: title mentions "independencia editorial"; "for sale" list has ≥2 named items; "never for sale" list has ≥4 named items and is example-based; `#firewall` anchor exists; rationale paragraph exists in plain Spanish; HTML validates; page passes a basic axe accessibility check; page renders correctly at the 3 breakpoints (`docs/engineering-standards.md` §7).
- [ ] 1.2 Confirm the checklist is checkable against a page that doesn't exist yet (i.e. every item currently fails) — this is the content-item equivalent of "red."

## 2. Content

- [ ] 2.1 Create `politica-editorial.html` at the site root (flat structure, matches `index.html`), Spanish content, `lang="es"`.
- [ ] 2.2 Write the plain-language rationale paragraph (why the firewall exists) — cite the site's "platform, not opinion" positioning.
- [ ] 2.3 Write the "for sale" list (featured/verified profile, district alerts — align wording with `docs/business-model.md` Product 2 tiers).
- [ ] 2.4 Write the "never for sale" list, example-based (poll %, candidate rankings, aggregation logic, methodology) — name concrete site features, not only abstract claims.
- [ ] 2.5 Wrap the for-sale/never-for-sale section in an element with `id="firewall"`.
- [ ] 2.6 Basic responsive layout (mobile-first, `docs/engineering-standards.md` §6-7) — this is a text page, no chart/nav dependency yet.

## 3. Verify against checklist (content item's "green")

- [ ] 3.1 Re-run the 1.1 checklist against the finished page — every item now passes.
- [ ] 3.2 Run HTML validator (W3C or `npx html-validate`).
- [ ] 3.3 Run axe DevTools check, fix any critical errors.
- [ ] 3.4 Manually check 3 breakpoints (< 768px, 768-1199px, ≥ 1200px).
- [ ] 3.5 Update `docs/backlog.md` BL-02 Status to `done`, noting the page path and that `BL-05`/`BL-29` must link `#firewall` when they ship.
