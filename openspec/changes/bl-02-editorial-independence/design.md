## Context

Static site, no build, no CMS (`CLAUDE.md` Stack). This is the first public-facing content page in the project — no precedent yet for where policy pages live or what language they're written in. `CLAUDE.md` constraint 6 mandates English for `docs/*.md` and `CLAUDE.md` itself (LLM determinism for planning artifacts), but says nothing about the *site's own content*, which serves Peruvian voters and must be Spanish (consistent with `index.html`'s `lang="es"` and the site's Spanish-market audience). This distinction hasn't been made explicit anywhere yet and needs to be, since every future content `BL-xx` (BL-03 privacy, BL-04 legal, BL-05 methodology/about) will hit the same question.

## Goals / Non-Goals

**Goals:**
- Decide and document: public site content is Spanish; internal planning docs (`docs/`, `openspec/`) stay English.
- Fix the page's URL/filename now so `BL-05` and `BL-29` can link to a stable target without guessing.
- Define what "done" verifiably means for a content-only page (no red/green test exists for prose).

**Non-Goals:**
- Building a reusable page template/layout system — this is 1 static HTML page, not a component library. That's premature for a 1-page site.
- Legal review/sign-off — this is a policy statement, not a contract; `BL-04` (legal & attribution policy) is the separate item that covers deeper legal exposure (JNE photos, defamation, republication rights).

## Decisions

1. **Language: content in Spanish, artifacts in English.** The policy page text is Spanish (site audience). This proposal/design/tasks stay English (planning-doc convention, `CLAUDE.md` constraint 6). Recorded here so it's not re-litigated on every future content item.
2. **File path: `/politica-editorial.html`** at the site root, matching the flat, no-build structure already in place (`index.html` at root). Kebab-case, Spanish slug, consistent with how a Spanish-speaking visitor would expect a URL to read.
3. **Verification method for "linked from BL-05 and BL-29"**: since those specs don't exist yet, this change can't create the actual links. Instead, this change documents the required anchor (`/politica-editorial.html#firewall`, a named section) in its own spec, and `BL-05`/`BL-29`'s specs will each carry a scenario requiring that link — checked by grep/manual review at their own spec time, not by this change reaching into future code.
4. **No new capability abstraction.** Considered modeling "policy pages" as a general capability (privacy, legal, editorial all under one `policy-pages` capability) vs. one capability per page. Chose one capability per page (`editorial-independence-policy`) — YAGNI, only 1 page exists today; a shared pattern can be extracted later if `BL-03`/`BL-04` reveal real duplication, not before.

## Risks / Trade-offs

- **[Risk]** Page ships with no nav link yet (nav doesn't exist until `BL-09`) → orphan page, unreachable except by direct URL. **Mitigation**: acceptable for now, same as any pre-nav content page; add to footer once `BL-09`/`BL-05` ship footer links. Noted as a task, not silently dropped.
- **[Risk]** "Never for sale" list (poll results, rankings, aggregation logic) could drift out of sync with what `BL-29` actually sells if written too vague. **Mitigation**: spec requires the list to be exhaustive and example-based, not just abstract principle.
