## Context

First Phase 1 item — no external data needed, but it's the largest content surface shipped so far (2 new pages + footer/nav changes across every existing page + an off-site content plan). File-layout, language, and token conventions are settled (`BL-02`/`BL-03`/`BL-04`/`BL-05`); this design applies them. The genuinely new problems: (1) contact info needs a real, working channel on publish day — WhatsApp number confirmed by user (`+51 971 388 435`); a project email domain doesn't exist yet; (2) the election calendar is real public data (JNE-confirmed counts) that must be presented without implying any of it is poll/candidate data, which `CLAUDE.md` constraint 1 reserves for Phase 2+.

## Goals / Non-Goals

**Goals:**
- Ship methodology + about + contact + election calendar as one cohesive "authority shell" so the site reads as a real operation, not a placeholder, before any real poll data exists.
- Use the real WhatsApp channel today; make the missing email channel visibly a TODO, not a silent gap or a fabricated address.
- Keep the election calendar strictly structural (counts, dates) — no candidate names, no poll numbers, nothing that could be mistaken for Phase 2 content.
- Produce a social content plan as a doc artifact the user can execute against manually (account creation/posting is the user's action, not this change's).

**Non-Goals:**
- Real candidate or poll data of any kind (blocked by JNE calendar until Aug 5 per `CLAUDE.md` constraint 1).
- A contact form, ticketing system, or the WhatsApp Business API integration (`BL-27`, Phase 8) — this ships a static `wa.me` link only.
- Analytics/Search Console wiring (`BL-15`, separate item) — this item's "audience" lever is content, not measurement.

## Decisions

1. **Contact channel: WhatsApp via `wa.me/51971388435` link, real and clickable on publish day. Email: placeholder `contacto@encuestaselectorales.pe` shown but flagged inactive** (mailto link omitted, plain text only, with a `tasks.md` TODO to wire it once the domain's email is set up). Matches `BL-05`'s precedent of using a real channel today over a polished-but-fake one.
2. **Election calendar is a static content block**, not a data-driven component: no JSON schema, no `docs/data-model.md` entry — it's fixed counts (25/196/1696) and fixed JNE dates that don't change until the next election cycle. Avoids over-building a data pipeline for numbers that don't update.
3. **Two new pages, same flat-root convention**: `/metodologia.html`, `/quienes-somos.html`, sharing `styles.css` and `BL-04` tokens. Footer/contact block is added once to a shared partial pattern already used by `BL-02`/`BL-03`/`BL-05` pages (each page's own footer markup — no templating engine exists, so it's a manual edit to each existing HTML file, same as `BL-04`'s retrofit).
4. **Social content plan ships as a docs artifact**, not a page: `docs/social-content-plan.md` (or similar), covering today → Aug 5 (countdown to admitted candidate lists). Not part of the deployed site — internal reference the user executes from.
5. **Methodology page cross-links `#firewall` (`BL-02`) and `#fuentes-correcciones` (`BL-05`)** per those specs' stated dependency, and discloses the opt-in own-poll mechanic honestly per `BL-03`'s privacy baseline (no real own-poll exists yet — this is a forward disclosure of how it will work).

## Risks / Trade-offs

- **[Risk]** A visibly "inactive" placeholder email could read as unfinished/unprofessional. **Mitigation**: still more honest than a fabricated address that bounces mail silently; matches constraint 8's "when in doubt, publish less."
- **[Risk]** Editing footer markup across every existing page by hand (no shared partial/include mechanism in a no-build static site) risks drift between pages. **Mitigation**: acceptable given `CLAUDE.md`'s closed decision against introducing a build step; `tasks.md` lists each file to touch explicitly so nothing is missed.
- **[Risk]** Publishing a real personal WhatsApp number publicly has exposure (spam, unsolicited contact). **Mitigation**: user's explicit choice/number; no mitigation needed beyond what `BL-30`'s B2B page will later formalize.
