## ADDED Requirements

### Requirement: Online survey rounds persist in a queryable schema
The system SHALL store each `online_propia` survey round as one row in an `encuestas` table, scoped to a `distrito_id`, with an open/close window and a publish-state gate, so real rounds can be created and later found by the public site.

#### Scenario: A round is active only inside its window and in production state
- **WHEN** a round's `estado_publicacion` is `producción` and the current time falls between its `fecha_apertura` and `fecha_cierre`
- **THEN** the round is considered active and eligible to appear in `index.php`'s "Encuestas Web Activas" column and its district's `distrito.php` sidebar

#### Scenario: A test round never appears publicly
- **WHEN** a round's `estado_publicacion` is `prueba`
- **THEN** no public page includes it, regardless of its open/close window

#### Scenario: An expired or not-yet-open round is not active
- **WHEN** the current time is before a round's `fecha_apertura` or after its `fecha_cierre`
- **THEN** the round is not considered active, even if `estado_publicacion` is `producción`

#### Scenario: A district can have multiple sequential rounds
- **WHEN** a district's first round closes and a second round is created for the same `distrito_id`
- **THEN** both rows exist independently, distinguished by their own `numero_ronda` and date window, with no data lost from the first round

### Requirement: Round creation is a trusted server-side action, not a public endpoint
The system SHALL provide a script-based path for an operator to create an `encuestas` row directly against the database; this path SHALL NOT be reachable from an unauthenticated public HTTP request.

#### Scenario: Creating a round requires direct script/database access
- **WHEN** an operator wants to open a new round
- **THEN** they run a script with direct database access — no public form or `/api/` endpoint exists for this in this capability
