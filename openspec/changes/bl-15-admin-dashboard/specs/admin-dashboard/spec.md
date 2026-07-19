# admin-dashboard

## ADDED Requirements

### Requirement: Login requires both password and TOTP
Authentication SHALL require a correct password verified via `password_verify()` against an Argon2id hash, AND a correct TOTP code, before establishing a session.

#### Scenario: Correct password with wrong TOTP is rejected
- **WHEN** a login attempt supplies the correct password and an incorrect or missing TOTP code
- **THEN** the login is rejected and no session is established

#### Scenario: Both factors correct grants access
- **WHEN** a login attempt supplies both the correct password and a valid TOTP code within the tolerance window
- **THEN** a session is established and the session ID is regenerated

### Requirement: Login attempts are rate-limited by username and by source IP independently
Repeated failed attempts SHALL trigger exponential backoff keyed on both dimensions, so that rotating the source IP does not reset the per-username limit and vice versa.

#### Scenario: Backoff persists across a changed source IP
- **WHEN** repeated failed attempts for one username have triggered backoff, and a subsequent attempt for the same username arrives from a different source IP
- **THEN** the backoff for that username still applies

#### Scenario: Failure messages do not distinguish the cause
- **WHEN** a login fails due to wrong username, wrong password, or wrong TOTP
- **THEN** the response message is identical in all three cases

### Requirement: Default dashboard view never decrypts
Loading the dashboard's default view SHALL compute only aggregates and SHALL NOT invoke the IP decryption function.

#### Scenario: Rendering the default view makes zero decryption calls
- **WHEN** the default dashboard view is rendered
- **THEN** the decryption function is called zero times during that request

### Requirement: Per-vote IP decryption is a separate, audited action
Decrypting a specific vote's IP SHALL be an explicit action distinct from viewing the dashboard, and SHALL write an audit log entry before returning the result.

#### Scenario: A decryption request produces exactly one audit entry
- **WHEN** an admin decrypts one vote's IP
- **THEN** exactly one row is appended to the audit log recording the admin, the target vote, and a timestamp, before the decrypted value is returned

#### Scenario: No endpoint decrypts more than one row per request
- **WHEN** the admin interface is inspected for decryption actions
- **THEN** no available action decrypts more than one vote's IP per request

### Requirement: The audit log is append-only at the database level
The MySQL user backing the admin application SHALL lack `DELETE` and `UPDATE` privileges on the audit log table, enforced by database grants, not application logic alone.

#### Scenario: Direct DELETE against the audit log is refused by MySQL
- **WHEN** a `DELETE` statement against the audit log table is attempted using the admin application's own DB credentials
- **THEN** MySQL rejects it due to insufficient privileges

#### Scenario: Direct UPDATE against the audit log is refused by MySQL
- **WHEN** an `UPDATE` statement against the audit log table is attempted using the admin application's own DB credentials
- **THEN** MySQL rejects it due to insufficient privileges

### Requirement: Heatmap aggregates do not expose per-voter precision
Any location aggregate shown in the default view SHALL bin coordinates coarsely enough that no single voter's address is reconstructable from the aggregate alone.

#### Scenario: Heatmap cells are coarser than street-level
- **WHEN** the heatmap query's coordinate grouping is inspected
- **THEN** the bin size is documented and is coarse enough (~100m or greater) that it does not resolve to an individual address

### Requirement: /admin/ is not publicly discoverable
The path SHALL be excluded from search indexing and SHALL deny directory listing.

#### Scenario: Search engines are told not to index
- **WHEN** `/admin/` is requested by a crawler or its response headers are inspected
- **THEN** an `X-Robots-Tag: noindex` header is present, and `robots.txt` disallows the path

#### Scenario: Directory listing is disabled
- **WHEN** a directory under `/admin/` is requested without a matching file
- **THEN** no file listing is returned
