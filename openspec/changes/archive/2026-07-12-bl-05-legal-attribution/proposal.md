## Why

This site will republish three categories of third-party content: pollster data (IEP/Ipsos/Datum/CPI PDFs), JNE candidate photos, and — eventually — candidate judicial records (`BL-24`). Each carries distinct legal exposure, and for a solo operator this is the single highest-consequence area of the project (`CLAUDE.md` constraint 8). `BL-14` (results view, publishes third-party poll data) and `BL-24` (judicial badge) cannot ship responsibly without a written rule for what's safe to publish and how corrections work. Publishing the rule now — before either feature exists — means the constraint is public and binding from day one, not retrofitted after a dispute.

## What Changes

- Add a public "Fuentes y correcciones" page stating three concrete rules: (1) pollster data gets cited + linked to its source PDF, never reproduced wholesale; (2) JNE photo reuse terms are explicitly flagged as unverified, not silently assumed safe; (3) any judicial-record claim carries a source + date and a correction/right-of-reply process.
- Establish a stable anchor (`#fuentes-correcciones`) for `BL-14` and `BL-24` to link to.
- Update `docs/backlog.md` `BL-24`'s dependency wording to a re-review gate on this page (same pattern `BL-03` established for `BL-25`), not just an existence check.

## Capabilities

### New Capabilities
- `legal-attribution-policy`: a published policy page (and its content rules) governing how third-party pollster data, JNE photos, and judicial records get cited, attributed, and corrected.

### Modified Capabilities
(none)

## Impact

- New static page (`/fuentes-correcciones.html`, same flat-root/`styles.css` convention as `BL-02`/`BL-03`, using `BL-04`'s validated tokens — `--accent-text` for any text/link color).
- No data model, backend, or build-tooling impact — content item per `docs/engineering-standards.md` §5 (checklist, not TDD).
- Creates a dependency: `BL-14` and `BL-24` specs must link `#fuentes-correcciones` once they ship, and `BL-24` specifically gates on a re-review of this page's correction process before activating (not just its existence).
