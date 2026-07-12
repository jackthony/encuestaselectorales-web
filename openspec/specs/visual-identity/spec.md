## Purpose

Site-wide color tokens and typography — validated `dataviz` reference palette, no
invented hex, no webfont dependency — retrofitted onto existing pages (`BL-04`).

## Requirements

### Requirement: Validated color tokens, not invented hex
The system SHALL define its site-chrome color tokens (surfaces, ink, accent) as the exact values from the harness `dataviz` skill's reference palette (`references/palette.md`), not custom-chosen hex.

#### Scenario: Token values match the reference palette
- **WHEN** `styles.css` `:root` custom properties are inspected
- **THEN** `--bg`/page-plane equals `#f9f9f7`, `--surface`/card equals `#fcfcfb`, `--text` equals `#0b0b0b`, `--muted` equals `#52514e` or `#898781` (secondary/muted ink), `--accent` equals `#2a78d6`, and `--border` equals `rgba(11,11,11,0.10)`

### Requirement: Accent continuity with future chart palette
The site accent color SHALL be identical to categorical slot 1 (blue, `#2a78d6`) of the `dataviz` reference palette, so UI chrome and future chart series share one hue rather than clashing.

#### Scenario: Accent hex matches categorical slot 1
- **WHEN** the `--accent` token and the `dataviz` skill's documented categorical slot-1 hex are compared
- **THEN** they are the same value (`#2a78d6`)

### Requirement: Editorial typography without external dependency
The system SHALL use a serif system-font stack for headings and a sans system-font stack for body text, with no `@font-face`, no `<link>` to an external font host, and no webfont network request of any kind.

#### Scenario: No external font requests
- **WHEN** any site page (with the new tokens) is loaded and network requests are inspected
- **THEN** no request is made to a font-hosting domain (e.g. fonts.googleapis.com, fonts.gstatic.com, or any third-party origin serving a font file)

#### Scenario: Headings render in a serif stack
- **WHEN** the computed `font-family` of an `h1`/`h2` element is inspected
- **THEN** it resolves to a stack starting with `ui-serif` (or `Georgia` as first concrete fallback), never the body sans stack

### Requirement: Retrofitted pages pass accessibility under new tokens
Existing pages SHALL be re-verified against the accessibility checklist under the new color tokens — not assumed to still pass unchanged after a token swap.

#### Scenario: Contrast still meets WCAG AA after retrofit
- **WHEN** text/background contrast ratios are computed for primary text, muted text, and accent-colored text against their respective surfaces on a retrofitted page
- **THEN** every ratio is ≥ 4.5:1 (WCAG AA for normal text)

#### Scenario: No regression in heading hierarchy or landmarks
- **WHEN** a retrofitted page's DOM is inspected
- **THEN** heading order has no skips, and `main`/`footer`/section landmarks are still present, unchanged by the CSS swap

### Requirement: Status colors reserved, not yet applied
The system SHALL document the `dataviz` reference palette's status colors (good/warning/serious/critical) as reserved tokens for future use, without applying them to any current page (no current page has an error/status state).

#### Scenario: Status tokens exist but are unused today
- **WHEN** `docs/engineering-standards.md` §6 is inspected
- **THEN** it lists the 4 status hex values with their role names, and no current page's HTML references them
