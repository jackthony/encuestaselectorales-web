# vote-endpoint

## ADDED Requirements

### Requirement: Rate limiting is the primary gate, evaluated before parsing
`/api/votar.php` SHALL check the request's rate limit, keyed on the server-resolved IP hash, before parsing or validating the request body.

#### Scenario: A request over threshold is rejected before body parsing
- **WHEN** the resolved `ip_hash` already has ≥ threshold votes for this `encuesta_id` in the configured window
- **THEN** the endpoint responds `429` without reading `candidato_id`, GPS, or any other body field

#### Scenario: Distinct IP hashes are independent
- **WHEN** two requests carry different resolved IPs (and therefore different `ip_hash` values)
- **THEN** one being rate-limited does not affect the other

### Requirement: Client-supplied identity signals never bypass the rate limit
Fingerprint, device token and GPS coordinates SHALL be treated as evidence contributing to `trust_score`, never as an alternate gate that grants a vote past the IP rate limit.

#### Scenario: A forged fingerprint does not create a fresh rate-limit budget
- **WHEN** a request presents a `browser_fingerprint` never seen before and no `device_token` cookie
- **THEN** it is still subject to the same `ip_hash`-based rate limit as any other request from that IP

### Requirement: GPS coordinates are mandatory at the API boundary
The endpoint SHALL reject any request missing `gps_lat` or `gps_lng` with `400`, independent of the database's own `NOT NULL` constraint.

#### Scenario: Missing coordinates rejected before a DB round-trip
- **WHEN** a request omits `gps_lat`
- **THEN** the endpoint responds `400` without attempting an insert

#### Scenario: A zero-valued latitude is not treated as missing
- **WHEN** a request includes `gps_lat: 0` (a valid latitude on the equator)
- **THEN** the endpoint does not reject it as absent

### Requirement: District code is whitelisted
`ubigeo_votacion` SHALL be validated against `data/distrito.json` before any database write.

#### Scenario: Unknown district code rejected
- **WHEN** `ubigeo_votacion` does not match any entry in `data/distrito.json`
- **THEN** the endpoint responds `400` and does not insert a row

### Requirement: trust_score is never exposed to the client
`trust_score` SHALL be computed and stored server-side and SHALL NOT appear in any HTTP response body.

#### Scenario: Successful vote response omits trust_score
- **WHEN** a vote is successfully recorded
- **THEN** the JSON response does not contain a `trust_score` key

#### Scenario: Error responses also omit trust_score
- **WHEN** any error path responds to the client
- **THEN** the response does not contain a `trust_score` key, computed or otherwise

### Requirement: IP storage is authenticated encryption with a salted, keyed hash
The real IP SHALL be encrypted with AES-256-GCM and hashed for dedup with HMAC-SHA256, using a key/salt read from outside the web root.

#### Scenario: Ciphertext, IV and tag are all persisted
- **WHEN** a vote is recorded
- **THEN** `ip_cifrada`, `ip_iv` and `ip_tag` are all populated, none empty

#### Scenario: Hash uses HMAC, not a naive concatenation
- **WHEN** `ip_hash` is computed
- **THEN** it is produced via `hash_hmac('sha256', $ip, $salt)`, not `hash('sha256', $ip . $salt)`

### Requirement: Local development never forges the trusted IP path in production
The `REMOTE_ADDR` fallback for missing `CF-Connecting-IP` SHALL only activate behind an explicit local-development configuration flag.

#### Scenario: Production without the flag does not fall back silently
- **WHEN** `HTTP_CF_CONNECTING_IP` is absent and the local-dev flag is not set
- **THEN** the request is treated as coming through an unverified path (per BL-12's trust gate), not silently resolved via `REMOTE_ADDR` as if trusted

### Requirement: Device token cookie is hardened
`device_token` SHALL be set with `HttpOnly`, `Secure`, and `SameSite=Strict`.

#### Scenario: Cookie attributes are present
- **WHEN** the `Set-Cookie` header for `device_token` is inspected
- **THEN** it includes `HttpOnly`, `Secure`, and `SameSite=Strict`
