# district-page-and-accessibility

## ADDED Requirements

### Requirement: District page is one hybrid template, not exclusive full-page states
`distrito.php` SHALL render as a single template (`canvas-gemini/tablero_electoral_growth_hack_hibrido.html`) whose blocks toggle independently based on what exists for the requested district — growth-hack CTA, vote widget, candidate roster, own-poll evolution chart, and campo (third-party) studies sidebar are independent blocks, not mutually exclusive pages. A district can show a growth-hack CTA and a campo-studies sidebar at the same time (no online candidates yet, but a real field study exists); this is expected, not a contradiction to resolve.

Superseded: the earlier `canvas-gemini/distrito.html` 3-stacked-states model (empty / directory / results as one page each) is no longer the target. Its candidate-roster card and result-bar markup remain valid as reusable sub-components within the hybrid template's blocks, not as separate page states.

#### Scenario: District with zero candidates shows the growth-hack CTA
- **WHEN** `distrito.php?slug=<slug>` is requested for a district with no `data/candidato.json` entries
- **THEN** the page shows the growth-hack block ("¿Tu candidato no está en la lista? Escríbenos por WhatsApp para agregarlo y abrir la contienda") with a `wa.me` link, and does not render a candidate list, a vote widget, or a "no data" placeholder in its place
- **AND** the campo-studies sidebar still renders independently if a real `resultado.json`-backed field study exists for this district, regardless of the CTA showing

#### Scenario: District with candidates shows the roster, gated by whether voting can go live
- **WHEN** the requested district has `candidato.json` entries
- **THEN** the page lists each candidate with name, party name, siglas and party color
- **AND** the vote widget (radio form + "Registrar mi voto") renders only when `/api/votar.php` exists and is reachable — see the vote-button gating requirement below

#### Scenario: Own-poll results attach to their round, campo results attach to their methodology
- **WHEN** the requested district has a closed online round (`tipo='online_propia'`) with results
- **THEN** each candidate's percentage is shown against that round's date range, distinct from any campo-study card
- **WHEN** the requested district has a campo study (`tipo='campo_externa'`)
- **THEN** every displayed percentage in that card is adjacent to its pollster, field dates, sample size, margin of error and confidence level

#### Scenario: Historical candidates are labeled, never presented as current
- **WHEN** a rendered candidate has `"activo": false`
- **THEN** the page displays an explicit label identifying the candidacy as historical and not valid for the 2026 election

### Requirement: The vote button never renders without a live backend
The interactive vote widget ("Registrar mi voto" / the candidate-selection radio form) SHALL NOT render in production until `/api/votar.php` (BL-14), rate limiting, and GPS validation are deployed and verified — a district with candidates but no live voting backend shows the roster and, where applicable, the growth-hack CTA, never a form that would submit to nothing or bypass anti-fraud checks.

#### Scenario: Candidates exist but the vote endpoint is not yet live
- **WHEN** `distrito.php` renders for a district with candidates and BL-14's endpoint is not deployed
- **THEN** no vote form or "Registrar mi voto" button is rendered anywhere on the page

#### Scenario: Vote widget appears only after the endpoint ships
- **WHEN** BL-14's endpoint is deployed and verified
- **THEN** the vote widget is enabled via a single, explicit switch (not per-page removal of markup) so it can be gated and un-gated without a redeploy of unrelated content

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
