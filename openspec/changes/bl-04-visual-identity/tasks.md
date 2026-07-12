## 1. Checklist first (design-token item — no red/green test, per `docs/engineering-standards.md` §5)

- [x] 1.1 Write the acceptance checklist from `specs/visual-identity/spec.md`: tokens match reference palette exactly; accent == chart categorical slot 1; no external font requests; headings use serif stack; retrofitted `politica-editorial.html` contrast ≥4.5:1 for all text roles; heading hierarchy/landmarks unchanged; status colors documented but unused.
- [x] 1.2 Confirm every checklist item currently fails against the OLD (dark-blue placeholder) tokens — content-item equivalent of "red."

## 2. Tokens

- [x] 2.1 Replace `styles.css` `:root` tokens: `--bg: #f9f9f7`, `--surface: #fcfcfb`, `--text: #0b0b0b`, `--muted: #52514e`, `--muted-2: #898781` (axis/label-weight muted, reserved), `--accent: #2a78d6`, `--border: rgba(11,11,11,0.10)`.
- [x] 2.2 Add reserved status tokens (documented, unused): `--status-good: #0ca30c`, `--status-warning: #fab219`, `--status-serious: #ec835a`, `--status-critical: #d03b3b`.
- [x] 2.3 Add heading font stack: `ui-serif, Georgia, "Times New Roman", serif` on `h1`/`h2` (and future heading levels). Keep body `system-ui` sans stack unchanged.
- [x] 2.4 Remove/replace any hardcoded dark-theme values still present in `styles.css`.
- [x] 2.5 **(added mid-implementation)** Measured `--accent` (#2a78d6) as text: 4.19:1 — fails the 4.5:1 AA bar from spec scenario "Contrast still meets WCAG AA after retrofit." Added `--accent-text: #256abf` (same hue, reference ramp step 500, 5.12:1) for any text/link/bullet use; kept `--accent` for non-text (borders/tints, 3:1 bar) and chart-slot-1 continuity. Applied to `.tag`, `li::before`, `footer a`.

## 3. Retrofit + document

- [x] 3.1 Loaded `politica-editorial.html` with the new `styles.css` via local HTTP server + browser — visually confirmed, no broken contrast.
- [x] 3.2 Updated `docs/engineering-standards.md` §6 with concrete hex values (including the `--accent`/`--accent-text` split and why) and the typography stack.

## 4. Verify against checklist (this item's "green")

- [x] 4.1 Re-ran 1.1's checklist against the retrofitted page — every item now passes (including the accent-text fix).
- [x] 4.2 Computed contrast: text 18.67:1, muted 7.53:1, accent-text 5.12:1 — all ≥4.5:1. (`--accent` itself, 4.19:1, is intentionally non-text-only per 2.5.)
- [x] 4.3 Heading hierarchy H1→H2→H2 and landmarks (`main`/`footer`/`section#firewall`) confirmed unchanged via DOM inspection.
- [x] 4.4 No `<link>`/`@font-face` to any font host in `styles.css` or `politica-editorial.html` — headings resolve to `ui-serif, Georgia, "Times New Roman", serif` (system stack only, confirmed via computed style).
- [x] 4.5 `docs/backlog.md` BL-04 Status updated to `done`.
