## Purpose

Public policy governing the firewall between paid B2B features (featured candidate
profiles) and editorial/aggregation output — what's for sale vs. never for sale (`BL-02`).

## Requirements

### Requirement: Public editorial independence policy page
The system SHALL publish a static page at `/politica-editorial.html` (Spanish content, per `design.md` decision 1) stating the firewall between paid B2B features and editorial/aggregation output.

#### Scenario: Page is reachable and well-formed
- **WHEN** a visitor requests `/politica-editorial.html`
- **THEN** the page loads with a `<title>` mentioning "independencia editorial" (or equivalent Spanish phrasing) and passes HTML validation (W3C Validator, per `docs/engineering-standards.md` §5 content checklist)

### Requirement: Explicit "for sale" vs "never for sale" list
The page SHALL enumerate, with concrete examples (not just abstract principle), what the site sells (visibility, verified badge, alerts) and what it never sells (poll results, rankings, aggregation logic, methodology).

#### Scenario: Both lists are present and non-empty
- **WHEN** the page content is inspected
- **THEN** it contains a "for sale" list with at least the items {featured/verified profile, district alerts} and a "never for sale" list with at least the items {poll results, candidate rankings, aggregation logic, methodology}

#### Scenario: List is example-based, not purely abstract
- **WHEN** the "never for sale" section is read
- **THEN** each item names a concrete site feature (e.g. "el % de intención de voto mostrado para un candidato") rather than only an abstract claim (e.g. "somos neutrales")

### Requirement: Stable anchor for future cross-links
The page SHALL expose a named HTML anchor (`#firewall`) on the section containing the for-sale/never-for-sale lists, so `BL-06` (methodology page) and `BL-30` (B2B pricing page) can link directly to the commitment, not just the page.

#### Scenario: Anchor resolves to the firewall section
- **WHEN** `/politica-editorial.html#firewall` is requested
- **THEN** the browser scrolls to the section containing both the "for sale" and "never for sale" lists

### Requirement: Neutrality rationale in plain language
The page SHALL include a short (2-4 sentence) explanation of *why* the firewall exists, written for a general reader, not as legal boilerplate — consistent with the site's "platform, not opinion" positioning (`docs/business-model.md`).

#### Scenario: Rationale is present and readable
- **WHEN** the page is read top to bottom
- **THEN** a rationale paragraph appears before or alongside the for-sale/never-for-sale lists, in plain Spanish, without legal-only jargon as the sole explanation
