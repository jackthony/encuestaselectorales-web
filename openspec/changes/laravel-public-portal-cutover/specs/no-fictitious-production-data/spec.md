## ADDED Requirements

### Requirement: Production pages SHALL never fabricate candidate or party data
The system SHALL only render candidate names, party names, survey rounds, and logos that exist in the real dataset. When a required media asset is missing, the page SHALL use a neutral fallback asset or an explicit empty state instead of inventing a fake candidate, fake party, or example placeholder.

#### Scenario: Missing candidate photo uses a neutral fallback
- **WHEN** a candidate record has no uploaded photo
- **THEN** the UI shows the shared default face asset instead of a fabricated portrait

#### Scenario: Missing party logo does not invent branding
- **WHEN** a party record has no logo available
- **THEN** the UI shows a neutral fallback mark or the party name only, but never a made-up logo

#### Scenario: Missing survey data shows absence, not examples
- **WHEN** a survey scope has no verified records yet
- **THEN** the public page shows an explicit empty or blocked state rather than an example card or synthetic entry

### Requirement: Public previews and share assets SHALL reflect real published content only
The system SHALL generate share metadata and preview assets only from real published survey content so that social cards, thumbnails, and open graph data never display fictional numbers, fictional institutions, or demo labels in production.

#### Scenario: A production survey is shared
- **WHEN** a public survey page is shared on social platforms
- **THEN** the metadata and thumbnail are derived from the live survey title, level, and real candidates

#### Scenario: No demo markers reach production
- **WHEN** any production-rendered page or share asset is generated
- **THEN** it SHALL not contain labels such as example, fictitious, demo, or placeholder as published content
