# Design & market references — encuestaselectorales-web

## encuestas.com.pe — structure (layout only, don't copy content/branding)

Deep review (real nav + live widget), 2026-07-12:

- **Real nav**: `PRESIDENCIAL | REGIONES (dropdown: 25 regions → provinces → districts) | ALCALDÍA DE LIMA | DISTRITOS DE LIMA (dropdown: 43 districts) | CALLAO | REGISTRA A TU CANDIDATO`
- **It's 100% a WordPress blog**: every "poll" is an individual post (title, date, reader comments, related articles). No aggregated/comparative view per office.
- **Own vote widget** (per post): form with a radio button per candidate + "Vote" button + "Results" button. Fixed copy: *"1 vote per IP + cookie, avoiding multiple voting"*, minimum 500 votes to publish. 100% online opt-in poll — **doesn't report its own institutional/physical polls**, this is its only product.
- **When it DOES mention physical pollsters** (IEP, CPI, Ipsos, Datum) it's as a **loose text post** citing the news (e.g. "Encuesta Alcaldía de Lima CPI – 26 Mayo 2025") — no structured fact sheet, no chart, no comparison against other sources or against its own online poll.
- Results: just % and counts in plain text — no charts, no visible fact sheet
- Grid of blog-news-style articles, paginated, with comments (political noise/spam in comments)
- Style: clean, info-dense, no strong visual identity

Business interpretation of these facts (what they mean, what we do differently) → `docs/business-model.md`, "Market validation" section.

## Physical/institutional pollster references (third-party sources to aggregate)

- **IEP** (Instituto de Estudios Peruanos) — `estudiosdeopinion.iep.org.pe`. Publishes PDF reports with a full fact sheet (sample size, geographic coverage, margin of error, confidence level). E.g.: presidential poll Feb 2026, n=1201, 24 departments, ±2.8%, 95% confidence.
- **Ipsos Perú** (with El Comercio) — face-to-face poll, 1200+ people, urban-rural, 20+ consecutive years. Reference for historical continuity.
- **Datum Internacional** — national urban-rural study, 40+ year history, n≈3000, breaks down by sex/region/urban-rural. `datum.com.pe/estudiopinion?categoria=electoral`.
- **CPI** (Compañía Peruana de Estudios de Mercado) — 300k+ polls/year, in-person+phone+online, public PDF reports at `cpi.pe`.

All 4 publish PDF reports with a fact sheet — structured data already exists, someone just needs to digitize and compare it. **That's the market gap.**

## Multi-source aggregation-with-charts reference: G1 (Brazil) — pesquisas eleitorais

Reviewed `especiaisg1.globo/.../pesquisas-eleitorais/`:
- Filters by: election round, **polling institute** (e.g. Datafolha vs Ipec), question type (total vote/spontaneous/rejection)
- Candidates with photo + % bar + click to highlight/compare trend between candidates
- "Estratos" section: same chart cut by demographic segment, toggleable per topic
- Fact sheet under "Metodologia": sample size, fieldwork dates, geographic coverage, confidence level, electoral-tribunal registration number
- Every data block clearly identifies the responsible institute — **this is the pattern to imitate for aggregating IEP/Ipsos/Datum/CPI in one place**, with our own online poll as one more source (labeled as such, never blended in as if institutional).

## Market authority references

**Peru (pollsters with institutional credibility):**
- IEP (Instituto de Estudios Peruanos) — academic reference
- Ipsos Perú — global brand, publishes with El Comercio
- Datum Internacional
- CPI (Compañía Peruana de Estudios de Mercado)

**International ("data journalism" format, target quality level):**
- FiveThirtyEight (538) — gold standard in aggregation + trend charts
- RealClearPolling
- Pew Research Center — methodology always visible
- Statista — clean data visualization

**Latam comparable:**
- Datafolha (Brazil) — the most cited/trusted in the region
- Cadem (Chile)

## Design conclusion

Neither encuestas.com.pe nor simulatuvoto's poll section show trend charts or a fact sheet (methodology/sample/margin of error). Adopting that standard (538/Datafolha/IEP style: chart + visible fact sheet) is the core differentiation behind `BL-13`/`BL-14`.
