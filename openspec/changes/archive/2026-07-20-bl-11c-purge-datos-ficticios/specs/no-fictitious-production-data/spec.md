# no-fictitious-production-data

## ADDED Requirements

### Requirement: No fabricated poll, pollster, or result data ships to production
No page SHALL render a poll, pollster name, or result percentage that does not correspond to a real record in `data/*.json`. A label such as "(dato ficticio)" attached to fabricated content does not satisfy this requirement — the content SHALL NOT be present at all.

#### Scenario: The example pollster record does not exist
- **WHEN** `data/encuestadora.json` is inspected
- **THEN** it contains no entry with `id: "ejemplo"` or equivalent placeholder marker

#### Scenario: The example survey and its results do not exist
- **WHEN** `data/encuesta.json` and `data/resultado.json` are inspected
- **THEN** neither contains an entry whose `metodologia` or content indicates it is a fabricated/demo record

#### Scenario: No page renders a poll number for a real person's name in a race they were not a candidate for
- **WHEN** any production page is inspected for candidate-attributed poll figures
- **THEN** every displayed name, race, and percentage traces to a real `data/candidato.json` + `data/resultado.json` pairing for the correct district and year — never a name attached to a different race or a fabricated number

#### Scenario: Absence of real data shows the page's empty state, not fabricated content
- **WHEN** `sondeos.php`, `encuesta.php`, or `candidato.php` is requested for a subject with no real poll/result data
- **THEN** the page renders its real empty state (no card, no chart, no result bars) rather than falling back to any placeholder figure
