## MODIFIED Requirements

### Requirement: Every district link resolves
Every district link in the nav SHALL resolve to a page (`distrito.html?id=<slug>`) that renders without erroring. A district with candidate data (`data/candidato.json`) renders a candidate directory; a district without candidate data renders a "página en construcción" placeholder.

#### Scenario: District link renders a placeholder
- **WHEN** a visitor opens `distrito.html?id=<slug>` for a district with no entries in `data/candidato.json`
- **THEN** the page loads and displays that district's name from `data/distrito.json`, with a "página en construcción" placeholder rather than an error or blank page

#### Scenario: District link with candidate data renders the directory
- **WHEN** a visitor opens `distrito.html?id=miraflores` (a district with entries in `data/candidato.json`)
- **THEN** the page loads and displays the candidate directory instead of the "página en construcción" placeholder
