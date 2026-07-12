## ADDED Requirements

### Requirement: Public methodology page
The system SHALL publish a static page at `/metodologia.html` (Spanish content, root-level flat file, matching the `BL-02`/`BL-03`/`BL-05` file-layout convention) explaining how sources will be aggregated/labeled and disclosing the opt-in own-poll mechanic.

#### Scenario: Page is reachable and well-formed
- **WHEN** a visitor requests `/metodologia.html`
- **THEN** the page loads with a `<title>` mentioning "metodología" and passes structural HTML validation (doctype, no duplicate ids, viewport meta, charset, no `<img>` without alt)

#### Scenario: Cross-links to editorial firewall and sources policy
- **WHEN** the methodology page is read
- **THEN** it links to `/politica-editorial.html#firewall` and `/fuentes-correcciones.html#fuentes-correcciones`

#### Scenario: Own-poll mechanic disclosed honestly
- **WHEN** the own-poll section is read
- **THEN** it states the mechanic is opt-in (respondent answers voluntarily) and that no real own-poll data exists yet, consistent with `BL-03`'s privacy baseline

### Requirement: Public about page
The system SHALL publish a static page at `/quienes-somos.html` naming the team behind the site and declaring editorial neutrality.

#### Scenario: Page is reachable and well-formed
- **WHEN** a visitor requests `/quienes-somos.html`
- **THEN** the page loads with a `<title>` mentioning "quiénes somos" and passes structural HTML validation

#### Scenario: Neutrality statement present
- **WHEN** the about page is read
- **THEN** it contains an explicit neutrality statement consistent with `/politica-editorial.html`

### Requirement: Real, working contact channel
The system SHALL expose a working WhatsApp contact link in the site footer/about, and SHALL NOT present an unconfirmed email address as if it were active.

#### Scenario: WhatsApp link is real and clickable
- **WHEN** the footer/about contact block is read
- **THEN** it contains a `https://wa.me/51971388435` link (or equivalent `wa.me` deep link) that is clickable, not plain text

#### Scenario: Email shown as pending, not fabricated-active
- **WHEN** the footer/about contact block is read
- **THEN** any email address shown is either omitted or visibly marked as not yet active (no clickable `mailto:` link presented as a live channel)

### Requirement: Election calendar content
The system SHALL publish static election calendar content (25 regional governments, 196 provincial mayoralties, 1696 district mayoralties; JNE key dates: admitted candidate lists Aug 5 2026, final candidacies Sept 5 2026, election Oct 4 2026) without any candidate names or poll figures.

#### Scenario: Calendar shows structural counts and dates only
- **WHEN** the election calendar section (on `/metodologia.html` or a dedicated section) is read
- **THEN** it shows the three JNE dates and the three race counts, and contains no candidate name or poll percentage

### Requirement: Footer/contact block applied site-wide
Every existing published page (`/politica-editorial.html`, `/politica-privacidad.html`, `/fuentes-correcciones.html`) SHALL carry the same footer contact block (WhatsApp link, links to methodology/about) as the two new pages.

#### Scenario: Footer consistent across all pages
- **WHEN** any of the four existing static pages plus the two new pages is loaded
- **THEN** each contains the same WhatsApp link and links to `/metodologia.html` and `/quienes-somos.html`

### Requirement: Social content plan artifact
The system SHALL produce a docs artifact (not a deployed page) listing a social media content calendar covering today through Aug 5 2026, including the election-calendar facts and JNE countdown dates.

#### Scenario: Plan covers the pre-Aug-5 window
- **WHEN** the social content plan doc is read
- **THEN** it lists dated content items spanning from today through Aug 5 2026, referencing the JNE key dates
