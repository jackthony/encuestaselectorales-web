## ADDED Requirements

### Requirement: Public sources & corrections page
The system SHALL publish a static page at `/fuentes-correcciones.html` (Spanish content, root-level flat file, matching the `BL-02`/`BL-03` file-layout convention) stating the site's rules for citing third-party content and correcting errors.

#### Scenario: Page is reachable and well-formed
- **WHEN** a visitor requests `/fuentes-correcciones.html`
- **THEN** the page loads with a `<title>` mentioning "fuentes" or "correcciones" and passes structural HTML validation (doctype, no duplicate ids, viewport meta, charset, no `<img>` without alt)

### Requirement: Pollster citation rule
The page SHALL state, as a concrete mechanism (not just a principle), that pollster figures are cited with a link to the source PDF and the full report is never reproduced.

#### Scenario: Citation rule is concrete and checkable
- **WHEN** the pollster-data section is read
- **THEN** it states both halves explicitly: what IS done (cite the figure + link the source) and what is NEVER done (reproduce the full report/informe)

### Requirement: JNE photo reuse honestly flagged as unverified
The page SHALL state the site's assumption about JNE candidate photo reuse and explicitly flag that this assumption has not been legally confirmed — not present it as settled fact.

#### Scenario: Uncertainty is stated, not hidden
- **WHEN** the JNE-photo section is read
- **THEN** it contains language equivalent to "esto no ha sido confirmado legalmente" (or "asumimos," "no verificado") — not a bare claim of reuse rights with no caveat

### Requirement: Judicial-record correction process
The page SHALL describe a correction/right-of-reply process for any judicial-record claim (`BL-24`), including the required source+date attribution on every such claim and how someone can request a correction.

#### Scenario: Correction process names a real, working channel
- **WHEN** the correction-process section is read
- **THEN** it links to a channel that resolves today (e.g. the repo's GitHub Issues "new issue" URL) — not a placeholder email or an unlinked mention of "contact us"

#### Scenario: Attribution requirement stated for future judicial badges
- **WHEN** the judicial-record section is read
- **THEN** it states that every judicial-record badge (once `BL-24` ships) must carry its JNE source and a date

### Requirement: Stable anchor for future cross-links
The page SHALL expose a named HTML anchor (`#fuentes-correcciones`) on the section containing the three rules, so `BL-14` (results view) and `BL-24` (judicial badge) can link directly to it.

#### Scenario: Anchor resolves to the rules section
- **WHEN** `/fuentes-correcciones.html#fuentes-correcciones` is requested
- **THEN** the browser scrolls to the section containing the pollster/photo/judicial-record rules

### Requirement: BL-24 gated by re-review, not existence
`docs/backlog.md` `BL-24`'s entry SHALL state that it depends on a re-review of this page's correction process before shipping, not merely on this page existing.

#### Scenario: BL-24 dependency wording updated
- **WHEN** `docs/backlog.md` `BL-24`'s entry is inspected
- **THEN** it references `BL-05` as a re-review gate, consistent with the pattern `BL-03` established for `BL-25`
