## Purpose

Candidate-lookup module and district page's candidate-directory rendering — joins `data/candidato.json` to `data/partido.json` per district, shared unmodified between Node tests and the browser (`BL-11`).

## Requirements

### Requirement: Candidate lookup joins district, candidate, and party data
`scripts/candidatos-por-distrito.js` SHALL export a function `candidatosPorDistrito(distritoId, candidatos, partidos)` that returns every candidate whose `distritoId` matches, each enriched with its resolved party (`nombre`, `siglas`, `color`), sorted by `numero` ascending then `nombre` alphabetically.

#### Scenario: District with candidates returns enriched list
- **WHEN** `candidatosPorDistrito('miraflores', candidatos, partidos)` runs against the committed `data/candidato.json`/`data/partido.json`
- **THEN** it returns all 8 Miraflores candidates, each with its `partido` field resolved to the matching `data/partido.json` record

#### Scenario: District with no candidates returns an empty list
- **WHEN** `candidatosPorDistrito('barranco', candidatos, partidos)` runs (a district with no `data/candidato.json` entries)
- **THEN** it returns an empty array

#### Scenario: Unresolvable partidoId does not throw
- **WHEN** a candidate record's `partidoId` doesn't match any `data/partido.json` id
- **THEN** the function returns that candidate with `partido: null` instead of throwing

### Requirement: District page renders a candidate directory when data exists
`distrito.html` SHALL render a candidate directory (name, party name + color swatch, list number or fallback, placeholder avatar) for a district that has entries in `data/candidato.json`, using `candidatosPorDistrito`.

#### Scenario: Pilot district shows its candidates
- **WHEN** a visitor opens `distrito.html?id=miraflores`
- **THEN** the page shows a directory of all 8 candidates with each one's party name and color swatch

#### Scenario: Null list number falls back to explicit text
- **WHEN** a rendered candidate's `numero` is `null`
- **THEN** its card shows the literal text "N.º no disponible" instead of a blank or "null"

#### Scenario: Null photo falls back to a generic placeholder avatar
- **WHEN** a rendered candidate's `foto` is `null`
- **THEN** its card shows a CSS-only placeholder avatar with no external image request
