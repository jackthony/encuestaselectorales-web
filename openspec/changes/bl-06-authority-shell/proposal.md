## Why

Confirmed JNE calendar: election Oct 4, 2026; admitted candidate lists Aug 5; final candidacies Sept 5. Today (Jul 12) no official candidate or municipal-poll data exists — Phase 0 (governance: `BL-01`-`BL-05`) is done, but there is nothing left to build that depends on real data. The only controllable lever until Aug 5 is authority + audience: a site that reads as a credible, named, transparent operation before it has a single real poll to show.

## What Changes

- Add a public "Metodología" page: how sources will be aggregated/labeled once real data exists, honest disclosure of the opt-in own-poll mechanic (per `BL-03`'s privacy baseline), links to `#firewall` (`BL-02`) and `#fuentes-correcciones` (`BL-05`) as required by their specs.
- Add a public "Quiénes somos" (about) page: named team, declared neutrality statement.
- Add contact info (email + WhatsApp button) to the site footer/about — needed before `BL-30` (B2B) can convert a lead.
- Add election calendar content: 25 regional governments, 196 provincial mayoralties, 1696 district mayoralties, JNE key dates (admitted lists Aug 5, final candidacies Sept 5, election Oct 4) — static content, no external data feed.
- Produce a social media content calendar/plan (countdown, key dates) for the next 3-4 weeks (today → Aug 5). Content plan only — account creation is the user's own action, not part of this change.

## Capabilities

### New Capabilities
- `authority-shell`: the site's authority-building content surface — methodology, about, contact, election calendar — and the social content plan that drives audience to it.

### Modified Capabilities
(none)

## Impact

- New static pages (`/metodologia.html`, `/quienes-somos.html`), same flat-root/`styles.css` convention as `BL-02`/`BL-03`/`BL-05`, using `BL-04`'s validated tokens.
- Footer/nav update across existing pages to add contact info and links to the new pages (touches `BL-02`/`BL-03`/`BL-05` pages' shared footer markup).
- Social content calendar is a content deliverable (e.g. a doc or `docs/` artifact), not a shipped page — no runtime impact.
- Content-only item per `docs/engineering-standards.md` §5 (checklist, not TDD) — no data validation, parsing, or % math logic involved.
- No backend, no build-tooling impact.
