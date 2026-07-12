## Purpose

Public policy stating the site's current (zero) and future data-handling commitments,
gating `BL-18` (analytics) and `BL-25` (own-poll backend) activation (`BL-03`).

## Requirements

### Requirement: Public privacy policy page
The system SHALL publish a static page at `/politica-privacidad.html` (Spanish content, root-level flat file, matching `BL-02`'s file-layout convention) stating the site's current and future data-handling commitments.

#### Scenario: Page is reachable and well-formed
- **WHEN** a visitor requests `/politica-privacidad.html`
- **THEN** the page loads with a `<title>` mentioning "política de privacidad" (or equivalent) and passes structural HTML validation (doctype, no duplicate ids, viewport meta, charset, no `<img>` without alt), per `docs/engineering-standards.md` §5 content checklist

### Requirement: Accurate "today" statement
The page SHALL state plainly, near the top, that the site collects zero personal data today (fully static, no analytics, no forms, no backend) — not a future-tense-only policy that reads as already in effect.

#### Scenario: "Today" statement is present and accurate
- **WHEN** the page content is inspected
- **THEN** it contains an explicit statement that no personal data is collected as of publish date, phrased so it can be updated later without rewriting the rest of the page

### Requirement: Explicit collect / never-collect lists
The page SHALL enumerate, with concrete items, what the site will collect once `BL-18` (analytics) and `BL-25` (own-poll backend) activate, and what it never collects regardless.

#### Scenario: Both lists are present and non-empty
- **WHEN** the page content is inspected
- **THEN** it contains a "cuando esté activo, recolectamos" list with at least {respuestas de encuesta propia con consentimiento, analytics agregado sin cookies, IP hasheada solo para anti-abuso} and a "nunca recolectamos" list with at least {huella digital de dispositivo, cruce con fuentes externas, IP cruda, seguimiento entre sesiones}

#### Scenario: Commitment framed over implementation detail
- **WHEN** the "never collect" section is read
- **THEN** each item states the binding commitment (e.g. "nunca guardamos tu IP sin hashear") rather than locking in an exact algorithm as the promise itself

### Requirement: Stable anchor for future cross-links
The page SHALL expose a named HTML anchor (`#compromiso`) on the section containing the collect/never-collect lists, so `BL-06` (methodology page) and `BL-25` (own-poll backend spec) can link directly to the commitment.

#### Scenario: Anchor resolves to the commitment section
- **WHEN** `/politica-privacidad.html#compromiso` is requested
- **THEN** the browser scrolls to the section containing both the collect and never-collect lists

### Requirement: Re-review gate documented for BL-25
The existence of this policy SHALL NOT be treated as sufficient on its own to activate `BL-25` — `BL-25`'s own tasks must include re-reading this page to confirm it still matches what's being built.

#### Scenario: Re-review dependency is recorded
- **WHEN** `docs/backlog.md` `BL-25`'s entry is inspected
- **THEN** it references `BL-03` as a gate that includes re-review, not just prior existence
