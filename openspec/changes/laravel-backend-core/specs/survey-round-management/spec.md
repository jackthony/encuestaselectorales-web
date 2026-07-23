## ADDED Requirements

### Requirement: Survey rounds are bound to one explicit territorial scope
The system SHALL bind each survey round to exactly one normalized territorial scope and SHALL expose whether that scope is a region, province, or district.

#### Scenario: A round is created for a homonymous location
- **WHEN** an administrator or seeder creates a survey round for a location whose name exists at multiple scope levels
- **THEN** the round references the intended territory identifier and returns the location name together with its scope type

#### Scenario: Different scope levels have different candidate rosters
- **WHEN** region, province, and district rounds exist for related territories
- **THEN** each round exposes only the candidate options assigned to its own scope and electoral office

### Requirement: Survey rounds have controlled publication windows
The system SHALL store an opening timestamp, closing timestamp, publication state, round number, survey type, title, and electoral office for every survey round.

#### Scenario: A published round is within its window
- **WHEN** the current server time is on or after the opening timestamp and on or before the closing timestamp of a published round
- **THEN** the round is considered active

#### Scenario: A round is outside its window
- **WHEN** the current server time is before the opening timestamp or after the closing timestamp
- **THEN** the round is not returned as active

#### Scenario: An unpublished round is within its dates
- **WHEN** a round is within its opening and closing timestamps but its publication state is not published
- **THEN** the round is not returned as active

### Requirement: Survey options reference valid scoped candidacies
The system SHALL model each selectable survey option as a reference to a candidacy whose territorial scope and electoral office match the survey round.

#### Scenario: A matching candidacy is added
- **WHEN** a candidacy matches the round's territory and electoral office
- **THEN** the system permits it to become a selectable option for that round

#### Scenario: A mismatched candidacy is added
- **WHEN** a candidacy belongs to another territory or electoral office
- **THEN** the system rejects the option and leaves the round unchanged

### Requirement: Candidate-less territories expose an explicit blocked state
The system SHALL distinguish a survey that cannot open because no verified candidate options exist from a survey that is merely unpublished, scheduled, or closed.

#### Scenario: A territory has no verified candidates
- **WHEN** the application requests survey availability for a territory and office with no eligible candidacies
- **THEN** it returns a blocked state with a public-safe reason indicating that candidate data is unavailable

#### Scenario: Candidates later become available
- **WHEN** eligible candidacies are imported for a previously blocked territory and a valid round is published
- **THEN** the active-round lookup returns the round and its options instead of the blocked state

### Requirement: Active-round lookup is provided by an application service
The system SHALL provide application-service queries for active rounds nationally and by region, province, or district, and presentation code SHALL NOT derive active state from files or raw database queries.

#### Scenario: The national portal requests active rounds
- **WHEN** the national portal invokes the active-round query
- **THEN** it receives only published rounds inside their publication windows with territory type, title, office, and candidate-summary data

#### Scenario: A territory requests its current round
- **WHEN** a territory page invokes the active-round query with a valid territory identifier
- **THEN** it receives the active round, a blocked result, or an explicit no-active-round result

### Requirement: Approved initial rounds are seeded idempotently
The system SHALL provide idempotent seed definitions for the approved Lima provincial and Callao regional rounds, with publication ending on 5 August 2026 at 23:59:59 in the application timezone, without fabricating candidates or media.

#### Scenario: Initial seeds run for the first time
- **WHEN** the production seeder runs against a catalog containing the approved Lima provincial and Callao regional candidacies
- **THEN** it creates one correctly scoped round for each approved dataset and associates only those verified candidacies

#### Scenario: Initial seeds run again
- **WHEN** the same production seeder is executed repeatedly
- **THEN** it reconciles the same opaque round and option records without creating duplicates or changing recorded votes

#### Scenario: An approved roster is unavailable
- **WHEN** a seed target lacks its verified candidate roster
- **THEN** the seeder does not fabricate options and the application exposes the target as blocked

### Requirement: Existing production round and vote data is preserved
Laravel migrations SHALL adopt or transform existing BL-13 and BL-14 records without replacing opaque identifiers or deleting valid votes.

#### Scenario: Migrations run against the live schema
- **WHEN** existing survey rounds, options, and interactive votes are present
- **THEN** the migration preserves their identifiers, relationships, timestamps, and vote records
