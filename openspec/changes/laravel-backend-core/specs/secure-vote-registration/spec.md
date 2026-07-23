## ADDED Requirements

### Requirement: Vote registration exposes a stable JSON contract
The system SHALL expose a Laravel JSON endpoint that accepts a survey option, territorial scope, GPS latitude, GPS longitude, GPS accuracy, interaction time, device token, and browser fingerprint, and SHALL return a stable status, machine-readable code, and Spanish public message.

#### Scenario: A valid vote request succeeds
- **WHEN** a client submits all required fields with valid types and values for an active survey option
- **THEN** the endpoint returns a success response and the opaque identifier of the registered vote

#### Scenario: A malformed request is submitted
- **WHEN** a required identifier, coordinate, accuracy, token, fingerprint, or interaction time is missing or malformed
- **THEN** the endpoint returns HTTP 422 with field-level validation errors and persists no vote

### Requirement: Votes are accepted only for active eligible options
The system SHALL accept a vote only when the referenced survey round is published and active and the selected option belongs to that round.

#### Scenario: The selected option is eligible
- **WHEN** the option belongs to the active round identified by the request
- **THEN** eligibility validation permits vote registration to continue

#### Scenario: The option belongs to another round
- **WHEN** the selected option does not belong to the requested active round
- **THEN** the endpoint rejects the request and persists no vote

#### Scenario: The round is closed or unpublished
- **WHEN** the referenced round is outside its publication window or is not published
- **THEN** the endpoint returns a conflict response indicating that voting is unavailable

### Requirement: Geographic evidence is validated and retained
The system SHALL validate coordinate ranges and GPS accuracy, resolve the submitted coordinates against the expected electoral territory, and store the submitted evidence together with the validated territory result.

#### Scenario: Location validates inside the required territory
- **WHEN** valid coordinates resolve inside the survey round's required territory and meet the configured accuracy threshold
- **THEN** the vote stores latitude, longitude, accuracy, validation outcome, and validated territory identifier

#### Scenario: Location cannot be validated
- **WHEN** coordinates fall outside the required territory, accuracy exceeds the configured threshold, or geographic validation fails
- **THEN** the endpoint rejects the vote with a public-safe geographic validation error and persists no vote

### Requirement: Network identifiers preserve privacy
The system SHALL derive the client IP from the trusted server request context, encrypt it using AES-256-GCM with a unique nonce and authentication tag, and separately calculate a keyed HMAC for duplicate detection.

#### Scenario: A vote is persisted
- **WHEN** server-side request validation succeeds
- **THEN** the vote stores the encrypted IP payload, nonce, authentication tag, and HMAC while never storing the plaintext IP

#### Scenario: A client forges a forwarded IP header
- **WHEN** the request does not arrive through a configured trusted proxy
- **THEN** the system ignores untrusted forwarding headers and derives the network signal from the server-observed connection

### Requirement: Duplicate prevention is atomic
The system SHALL prevent more than one accepted vote for the same survey round when the configured server-side IP HMAC or durable device-token signal matches an existing accepted vote, and SHALL enforce the decision atomically under concurrent requests.

#### Scenario: A connection repeats a vote
- **WHEN** an accepted vote already exists for the same round and IP HMAC
- **THEN** the endpoint returns a duplicate-vote conflict and creates no additional vote

#### Scenario: A device repeats a vote from another connection
- **WHEN** an accepted vote already exists for the same round and device-token signal
- **THEN** the endpoint returns a duplicate-vote conflict and creates no additional vote

#### Scenario: Concurrent duplicate requests arrive
- **WHEN** two requests with the same protected duplicate signal race for the same round
- **THEN** the transaction and database constraints commit at most one vote

### Requirement: Browser and device data are supporting abuse signals
The system SHALL retain normalized hashes of the device token and browser fingerprint as abuse-detection signals, but SHALL NOT treat client-supplied fingerprint or GPS data as a substitute for server-side network rate limiting.

#### Scenario: A fingerprint changes
- **WHEN** a repeat request changes its browser fingerprint but retains a duplicate server-side IP HMAC
- **THEN** the system still rejects the duplicate vote

#### Scenario: Abuse signals are recorded
- **WHEN** a valid first vote is accepted
- **THEN** the system records the normalized device and browser signals for audit and abuse analysis

### Requirement: Vote persistence is transactional and opaque
The system SHALL generate an application-owned opaque non-sequential vote identifier and SHALL persist the vote, privacy signals, geographic evidence, and audit timestamps in one database transaction using parameterized data access.

#### Scenario: Persistence succeeds
- **WHEN** all validations pass and all vote fields can be stored
- **THEN** the transaction commits one complete vote record with an opaque identifier

#### Scenario: Any persistence step fails
- **WHEN** encryption, duplicate enforcement, geographic evidence storage, or database persistence fails
- **THEN** the transaction rolls back and no partial vote record remains

### Requirement: Error responses do not disclose secrets
The system SHALL log operational details server-side and SHALL return public-safe JSON errors that do not expose SQL, credentials, encryption material, plaintext IP addresses, stack traces, or internal file paths.

#### Scenario: An unexpected server error occurs
- **WHEN** vote registration throws an unhandled infrastructure error
- **THEN** the endpoint returns HTTP 500 with a generic Spanish message and logs the diagnostic details securely
