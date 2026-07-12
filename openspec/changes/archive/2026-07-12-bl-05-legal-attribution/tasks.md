## 1. Checklist first (content item — no red/green test, per `docs/engineering-standards.md` §5)

- [x] 1.1 Write the acceptance checklist from `specs/legal-attribution-policy/spec.md`: title mentions fuentes/correcciones; pollster citation rule states both what's done and what's never done; JNE photo section explicitly flags "no confirmado legalmente"; correction process links a real working channel (GitHub Issues); judicial-badge attribution requirement stated; `#fuentes-correcciones` anchor exists; structural HTML valid; contrast ≥4.5:1 using `--accent-text`; heading hierarchy/landmarks correct; responsive at 3 breakpoints.
- [x] 1.2 Confirm every checklist item currently fails against a page that doesn't exist yet — content-item equivalent of "red."

## 2. Content

- [x] 2.1 Create `fuentes-correcciones.html` at the site root, Spanish content, `lang="es"`, reusing `styles.css`.
- [x] 2.2 Write the pollster citation rule (cite figure + link source PDF; never reproduce full report).
- [x] 2.3 Write the JNE photo section, explicitly hedged.
- [x] 2.4 Write the judicial-record correction process: source+date attribution requirement, link to `https://github.com/jackthony/encuestaselectorales-web/issues/new`, noted as "for now."
- [x] 2.5 Wrapped the three-rule section in `id="fuentes-correcciones"`.
- [x] 2.6 Responsive layout (reused existing patterns, no new CSS needed for layout).

## 3. Verify against checklist + fix cross-references

- [x] 3.1 Re-ran the 1.1 checklist against the finished page — every item passes.
- [x] 3.2 Structural HTML check: no duplicate ids, doctype html, `lang="es"`, 0 images without alt.
- [x] 3.3 Contrast verified: text 18.67:1, muted 7.53:1, `--accent-text` 5.12:1 — all ≥4.5:1.
- [x] 3.4 Heading hierarchy H1→H2→H2→H2 confirmed. Landmarks `main`/`footer`/`section#fuentes-correcciones` present.
- [x] 3.5 GitHub Issues link confirmed resolving (HTTP 200, `curl -L`).
- [x] 3.6 **Bug found and fixed during verification**: the in-content GitHub Issues link had NO styling applied — `styles.css` only styled `.tag` and `footer a`, not general `a`, so it fell back to browser-default blue (`#0000EE`, 8.91:1 — passed AA by coincidence, not by design). Added a base `a { color: var(--accent-text); }` rule to `styles.css` (benefits every current/future page); removed the now-redundant `footer a` rule. Re-verified BL-02/BL-03 pages unaffected (still `rgb(37,106,191)` on their footer links).
- [x] 3.7 `docs/backlog.md` `BL-24` entry updated to a `BL-05` re-review gate (mirrors `BL-03`→`BL-25`).
- [x] 3.8 `docs/backlog.md` BL-05 Status updated to `done`.
