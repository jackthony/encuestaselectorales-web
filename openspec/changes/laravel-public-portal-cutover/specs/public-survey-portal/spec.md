## ADDED Requirements

### Requirement: Public survey pages render the active round for each territorial scope
The system SHALL render the public survey pages for district, province, and region scopes using the active round for that scope, and the page title, card header, and route target SHALL clearly show the territorial level so same-named places remain distinguishable.

#### Scenario: Same slug in different levels remains readable
- **WHEN** a territorial slug exists at more than one level
- **THEN** the public page shows the level label next to the place name and routes to the correct district or territory page

#### Scenario: An active round appears on the correct page
- **WHEN** a round is currently open and published for a district, province, or region
- **THEN** the public survey page renders it as an active card rather than hiding it or treating it as a placeholder

### Requirement: Candidate cards show real party branding and a photo fallback
The system SHALL render each candidate card with the candidate's name, the party name, and the party logo when available; if the candidate photo is missing, the page SHALL use a neutral fallback image instead of inventing one.

#### Scenario: Candidate media is incomplete
- **WHEN** a candidate has no uploaded photo
- **THEN** the page shows the shared default face asset instead of a fabricated photo

#### Scenario: Party branding is present
- **WHEN** a candidate belongs to a real party record with a logo
- **THEN** the candidate card renders the party logo and party name together

### Requirement: Surveys without candidates show an explicit blocked state
The system SHALL show an explicit empty or blocked state when a survey scope has no candidates loaded yet, so the public can see that the vote cannot start until candidate data exists.

#### Scenario: A scope has no candidates
- **WHEN** a district, province, or region has no candidate records yet
- **THEN** the survey page shows a visible message that the survey cannot start yet rather than an empty card or fake placeholder lineup

### Requirement: Survey pages expose share actions and share metadata
The system SHALL provide share actions and share metadata for survey pages so visitors can share the page in Facebook, WhatsApp, and story-sized social surfaces.

#### Scenario: Share controls are available
- **WHEN** a survey page is rendered
- **THEN** it includes share actions and social metadata suitable for link previews and story-sized thumbnails

