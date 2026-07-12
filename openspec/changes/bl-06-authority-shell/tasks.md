## 1. Methodology page

- [x] 1.1 Create `/metodologia.html` (flat root, `styles.css`, `BL-04` tokens) with sections: how sources get aggregated/labeled, own-poll disclosure (opt-in, none exists yet)
- [x] 1.2 Link `/politica-editorial.html#firewall` and `/fuentes-correcciones.html#fuentes-correcciones` from the relevant sections
- [x] 1.3 Add election calendar content block: 25 regional governments, 196 provincial mayoralties, 1696 district mayoralties; JNE dates (admitted lists Aug 5 2026, final candidacies Sept 5 2026, election Oct 4 2026) — no candidate names, no poll figures

## 2. About page

- [x] 2.1 Create `/quienes-somos.html` with named team + explicit neutrality statement consistent with `/politica-editorial.html`

## 3. Contact + footer

- [x] 3.1 Add WhatsApp link (`https://wa.me/51971388435`) to a shared footer block
- [x] 3.2 Show email as visibly pending (no `mailto:` link) with a code comment/TODO noting the domain email is not set up yet
- [x] 3.3 Apply the footer block (WhatsApp link + links to `/metodologia.html` and `/quienes-somos.html`) to `/politica-editorial.html`, `/politica-privacidad.html`, `/fuentes-correcciones.html`, and the two new pages

## 4. Social content plan

- [x] 4.1 Write `docs/social-content-plan.md`: dated content calendar from today through Aug 5 2026, using the election-calendar facts and JNE countdown dates

## 5. Checklist verification (content-only item — no TDD, per `CLAUDE.md` constraint 7)

- [x] 5.1 Both new pages: valid HTML (doctype, no duplicate ids, viewport meta, charset, no `<img>` without alt)
- [x] 5.2 Both new pages: responsive check (mobile width) and keyboard/contrast accessibility check against `BL-04` tokens
- [x] 5.3 All six pages: footer contact block present and consistent
- [x] 5.4 `docs/backlog.md` `BL-06` marked done with a dated status line (per `BL-02`-`BL-05` convention)

## 6. PR

- [x] 6.1 Open PR from `feat/bl-06-authority-shell` into `main`
