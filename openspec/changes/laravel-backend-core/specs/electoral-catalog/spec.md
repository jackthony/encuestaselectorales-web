## ADDED Requirements

### Requirement: Territorial scopes are normalized and unambiguous
The system SHALL store every electoral territory as a normalized scope with an opaque non-sequential identifier, official ubigeo code, name, scope type (`region`, `province`, or `district`), and parent relationship where applicable.

#### Scenario: Homonymous territories remain distinguishable
- **WHEN** a region, province, and district share the same display name
- **THEN** each territory is returned with its own identifier, ubigeo code, scope type, and parent hierarchy

#### Scenario: A district retains its territorial ancestry
- **WHEN** a district is retrieved from the catalog
- **THEN** the response identifies its parent province and region

### Requirement: Electoral entities use stable opaque identifiers
The system SHALL assign UUIDv4 or an equivalently opaque non-sequential identifier to territories, political parties, candidates, candidacies, and media references, and SHALL NOT use database-generated sequential identifiers for those entities.

#### Scenario: A new catalog entity is persisted
- **WHEN** the system creates a territory, party, candidate, candidacy, or media reference
- **THEN** it persists an application-generated opaque identifier without relying on `AUTO_INCREMENT`

### Requirement: Candidates and political parties are represented independently
The system SHALL store a candidate independently from the political party and electoral candidacy through which that person participates, so the same person and party can be reconciled without duplicating their canonical records.

#### Scenario: A candidate participates in a scoped election
- **WHEN** a candidacy is registered
- **THEN** it references one canonical candidate, one canonical political party, one territorial scope, and one electoral office

#### Scenario: A party presents multiple candidates
- **WHEN** the same political party presents candidates in different territories or offices
- **THEN** the catalog reuses the party record and creates distinct candidacy records

### Requirement: Candidate and party media have explicit semantics
The system SHALL distinguish party logos from candidate photos in its media model and SHALL preserve the source URL and source attribution for each external media reference.

#### Scenario: Both media types are available
- **WHEN** a candidacy has a party logo and a candidate photo
- **THEN** the read contract returns each image in its correctly named field without interchanging them

#### Scenario: A candidate photo is unavailable
- **WHEN** a candidate has no verified photo
- **THEN** the catalog returns a null candidate-photo reference and the presentation contract identifies that the default face fallback must be used

### Requirement: Catalog reads are exposed through application contracts
The system SHALL expose territory, party, candidate, and candidacy reads through repository and application-service contracts, and controllers and views SHALL NOT query raw tables or catalog files directly.

#### Scenario: A public page requests candidates for a territory
- **WHEN** a controller requests the candidacies for an electoral scope and office
- **THEN** an application service returns normalized catalog records without exposing persistence implementation details

### Requirement: Catalog relationships enforce referential integrity
The system SHALL reject candidacies and media references whose candidate, party, territory, or related catalog entity does not exist.

#### Scenario: An invalid candidacy is submitted
- **WHEN** a candidacy references a missing candidate, party, or territorial scope
- **THEN** the system rejects the write and does not persist a partial relationship
