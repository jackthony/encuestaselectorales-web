## Context

Second content page (precedent: `BL-02`/`politica-editorial.html`) — file-layout, language, and styling conventions are already decided (`docs/engineering-standards.md` §0, this change's `design.md` reuses them, doesn't re-derive them). The one genuinely new problem: this policy describes behavior that **doesn't exist yet** (`BL-18` analytics, `BL-25` backend are both `not-started`). A privacy policy written in present tense ("we collect X") would be false today; written purely in future tense it reads as legal filler nobody will re-check. Needs a structure that stays true right now AND stays load-bearing later without silently going stale.

## Goals / Non-Goals

**Goals:**
- Decide how to phrase current-vs-future collection so the page is accurate on publish day and doesn't need to be entirely rewritten when `BL-18`/`BL-25` ship (only reviewed).
- Define the re-review mechanism referenced in `CLAUDE.md` constraint 5 ("`BL-25` does not activate without `BL-03` already shipped") — shipped once isn't enough if the policy drifts from what `BL-25` actually implements.
- Fix the page path and anchor now, same as `BL-02`.

**Non-Goals:**
- Modeling a general "policy page" capability/template — same YAGNI call as `BL-02` design.md decision 4, still true with only 2 pages.
- Legal sign-off — baseline statement, not a reviewed legal document.

## Decisions

1. **Explicit "today / when active" framing, not pure future tense.** The page states plainly, near the top: "Hoy este sitio no recolecta ningún dato personal" (today collects zero data), then describes what happens "cuando" (when) `BL-18`/`BL-25` activate. This is true on publish day and doesn't need rewriting later — only the "hoy no recolecta nada" line needs updating once `BL-18` ships (small, obvious edit), not the whole page.
2. **Re-review gate lives in the backlog, not the page.** The page itself can't enforce "re-check me before `BL-25` ships" — that's a process constraint. `docs/backlog.md` BL-25's dependency on `BL-03` already exists; this change adds an explicit task in `BL-25`'s future tasks.md (when that spec gets written) to re-read this page and confirm it still matches what's being built, rather than assuming a 2026-07 policy is still accurate months later.
3. **File path: `/politica-privacidad.html`**, same flat root convention as `BL-02`. Anchor `#compromiso` (not `#firewall` — different page, different concept: this is a data-handling commitment, not the editorial firewall) for the section listing collect/never-collect.

## Risks / Trade-offs

- **[Risk]** Page ships, `BL-18`/`BL-25` land later, nobody remembers to flip "hoy no recolecta nada" to describe live behavior → page becomes actively misleading (worse than never having one). **Mitigation**: decision 2 — `BL-25`'s own tasks.md will carry the re-review task; `BL-18`'s spec (when written) should carry the same for its own claims.
- **[Risk]** Listing hashed-IP/device-token mechanics before they exist could over-promise a specific technical implementation that changes by the time `BL-25` is actually built. **Mitigation**: describe the commitment (never raw IP, never fingerprinting) rather than implementation specifics (SHA-256 exact algorithm) as the binding part — algorithm detail is illustrative, not a locked promise.
