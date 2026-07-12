## Context

`BL-02` created `styles.css` with placeholder tokens (dark blue, generic) copied forward from `index.html`'s "coming soon" page. Nobody chose them deliberately — they existed because a placeholder needed *some* color. User flagged this directly: light theme preferred, editorial authority (538/Pew Research reference), current theme "falta identidad/carácter." This is the site's second content page and last chance to fix the system before it's baked into 3+ pages (`BL-05`, `BL-06`, `BL-10`/`BL-11` all land soon).

The harness ships a `dataviz` skill specifically for this kind of decision — its `references/palette.md` is a pre-validated (contrast-checked, CVD-safe) palette, not something to hand-pick hex codes for from scratch.

## Goals / Non-Goals

**Goals:**
- Adopt validated, not guessed, color tokens — reuse the `dataviz` skill's reference palette rather than inventing new hex values.
- Make the site accent and the future chart palette's first categorical slot the same hue, so UI chrome and data visualization don't compete once `BL-13` ships a chart.
- Get editorial character from typography without adding a dependency (no webfonts) — consistent with the "no build, no external request" stack decision and the privacy stance in `BL-03`.

**Non-Goals:**
- Deciding the full chart categorical/sequential/diverging palette — the `dataviz` skill is explicit that "color comes last" and should be validated against an actual chart via its script, not guessed here with no chart to check it against. That's `BL-13`'s job.
- A dark-mode theme — user asked for light theme now; add dark mode if a real request for it appears later, not preemptively.
- A component/design-system library — 2 pages exist, `styles.css` as a flat token sheet is still the right size (per `docs/engineering-standards.md` §0's "extend only when demonstrably needed" rule).

## Decisions

1. **Adopt the `dataviz` skill's reference palette values directly, not custom brand hex.** Light theme: page plane `#f9f9f7`, card/surface `#fcfcfb`, primary ink `#0b0b0b`, secondary ink `#52514e`, muted `#898781`, hairline border `rgba(11,11,11,0.10)`. These are already contrast-validated against each other in the skill's own reference (see `palette.md` "Chart chrome & ink" table) — adopting them wholesale means no separate validation pass is needed for the base tokens, only for the retrofitted page's actual rendered contrast (still checked in tasks, since real text isn't guaranteed to match the reference table's assumptions exactly).
2. **Accent = `#2a78d6`, the reference palette's categorical slot 1 (blue).** Chosen specifically so that when `BL-13` builds its first chart and picks a categorical order from the same reference palette, slot 1 is visually the same blue already established as the site's UI accent — continuity between "the site" and "the data" instead of two unrelated brand colors.
3. **Typography: serif headline stack, sans body, no webfonts.** Headings: `ui-serif, Georgia, "Times New Roman", serif`. Body: keep the existing `system-ui, -apple-system, "Segoe UI", Roboto, sans-serif` stack. This is the standard editorial pattern (serif display + sans body reads as "publication," not "app") achievable with zero dependency — no `<link>` to a font host, no render-blocking request, no third-party origin (a font CDN is itself a tracker, which would sit oddly next to `BL-03`'s privacy stance).
4. **Reserve, don't yet use, the status palette** (`good`/`warning`/`serious`/`critical` from the reference). No current page needs them (no error states, no data-quality flags yet), but documenting the values now in `docs/engineering-standards.md` §6 means `BL-14` (results view) and `BL-24` (judicial badge) don't have to re-derive or guess status colors later — they pull from an already-validated set.
5. **No new capability abstraction beyond `visual-identity`.** Considered folding this into `BL-02`'s `editorial-independence-policy` capability (since it touches the same file) — rejected: this capability applies to every future page, not just the editorial-independence policy; keeping it separate means `BL-05`/`BL-06` specs can reference `visual-identity` directly without pulling in unrelated policy-page requirements.

## Risks / Trade-offs

- **[Risk]** Retrofitting `styles.css` changes `politica-editorial.html`'s rendered contrast ratios — the `BL-02` checklist passed against the OLD tokens, not these. **Mitigation**: re-run the same contrast/accessibility checklist from `BL-02`'s `tasks.md` against the retrofitted page as part of this change's own tasks, don't assume it still passes.
- **[Risk]** Serif headline stack renders inconsistently across OS (Georgia isn't universal on Linux, `ui-serif` fallback behavior varies). **Mitigation**: acceptable — the fallback chain still resolves to *some* serif on every major platform (Windows/macOS/Android all ship a Georgia-like serif), and a slightly different serif substitution is a much smaller risk than a webfont request; not worth a dependency to fully normalize.
