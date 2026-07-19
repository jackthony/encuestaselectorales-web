# district-page-and-accessibility

## ADDED Requirements

### Requirement: District page renders the correct state for its data
`distrito.php` SHALL render exactly one of three states based on what exists for the requested district: empty (no candidates), directory (candidates, no poll results), or results (candidates and results), never a mix and never a placeholder for missing data.

#### Scenario: District with zero candidates shows the empty state
- **WHEN** `distrito.php?id=<slug>` is requested for a district with no `data/candidato.json` entries
- **THEN** the page shows the empty-state content (JNE calendar dates, WhatsApp notify CTA) and does not render a candidate list or a "no data" placeholder in its place

#### Scenario: District with candidates but no poll shows the directory state
- **WHEN** the requested district has `candidato.json` entries but no matching `resultado.json` entry
- **THEN** the page lists each candidate with name, party name, siglas and party color, and shows no percentage or result bar

#### Scenario: District with results shows every number attached to its methodology
- **WHEN** the requested district has both candidates and a poll result
- **THEN** every displayed percentage is adjacent to its poll's pollster, field dates, sample size, margin of error and confidence level

#### Scenario: Historical candidates are labeled, never presented as current
- **WHEN** a rendered candidate has `"activo": false`
- **THEN** the page displays an explicit label identifying the candidacy as historical and not valid for the 2026 election

### Requirement: GPS permission-denied path never uses a browser-native alert
The vote flow SHALL replace `alert()` on geolocation denial with the recovery modal, keyboard-operable and screen-reader-labeled.

#### Scenario: Denial shows the recovery modal, not a dead end
- **WHEN** `navigator.geolocation.getCurrentPosition` returns `PERMISSION_DENIED`
- **THEN** the recovery modal is shown with instructions to re-enable location, and no browser `alert()` fires

#### Scenario: Already-denied permission is detected before prompting
- **WHEN** `navigator.permissions.query({name:'geolocation'})` reports `denied` on page load and the API is available
- **THEN** the recovery modal is shown directly, without first firing a geolocation prompt that would fail silently

#### Scenario: Retry preserves the selected candidate
- **WHEN** a user re-enables location via the browser's own UI and clicks "reintentar" in the modal
- **THEN** the previously selected candidate radio remains selected and the page does not reload

#### Scenario: Modal is keyboard-operable
- **WHEN** the recovery modal is open and the user presses Tab repeatedly
- **THEN** focus cycles only among the modal's own interactive elements
- **AND** pressing Escape closes the modal via the same path as clicking "Cancelar voto"

#### Scenario: No unqualified privacy claim in the modal
- **WHEN** the recovery modal's privacy text is inspected
- **THEN** it does not claim the coordinate is anonymous or that it is discarded, and it does not claim "100% guaranteed" privacy — it states what is stored (encrypted coordinate, tied to the vote) and what is not done with it (no cross-session tracking, no cross-referencing, no sale/sharing)

### Requirement: Text/background contrast meets WCAG AA
Every text and background color pair rendered across the site SHALL measure at least 4.5:1; non-text UI (borders, icons, chart bars, focus indicators) SHALL measure at least 3:1.

#### Scenario: Contrast audit exists and is current
- **WHEN** `docs/wcag-contrast-audit.md` is read
- **THEN** it lists every foreground/background pair in use, its measured ratio, and pass/fail against the applicable threshold

#### Scenario: No failing pair ships
- **WHEN** any pair in the audit fails its threshold
- **THEN** the shipped code does not use that pair — it is fixed before this change is considered done, not logged as future work

### Requirement: Chart.js is version-pinned
`candidato.php` SHALL load Chart.js from a specific, immutable version, not a floating `latest` alias.

#### Scenario: Script tag names an exact version
- **WHEN** `candidato.php`'s Chart.js `<script src>` is inspected
- **THEN** the URL contains a specific semver, not `latest` or an unversioned path
