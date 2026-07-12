# Business model — encuestaselectorales.pe

> Product/monetization decision reference. Pattern adapted from simulatuvoto's `docs/ESTRATEGIA-NEGOCIO.md` (same author/agency).
> Created: 2026-07-12.

---

## Positioning

**encuestaselectorales.pe is a neutral aggregator, not a pollster.**

- We don't produce "the truth" about voting intention — we aggregate and contextualize what certified pollsters (IEP, Ipsos, Datum, CPI) produce + our own digital poll, with methodology always visible.
- Branding: "Built by Neuracode" — a tech agency, not a political actor, same pattern as simulatuvoto.
- Transparency as the differentiator: we show a fact sheet (sample size, margin of error, mode, date) for **every** source, including our own (honest about being opt-in/non-probabilistic).

## Market validation: the encuestas.com.pe case

**What it is**: a WordPress blog with its own vote widget (online, opt-in, 1 vote per IP+cookie) per election/district. When it mentions IEP/Ipsos/Datum/CPI polls, it does so as a loose text post — no structured fact sheet, no chart, no source comparison.

**What it validates**:
1. Real demand and SEO traffic exists on "polls + [district/region]" — the site covers down to all 43 Lima districts + provinces.
2. The problem is real: nobody compares physical pollsters against each other or against online sentiment, with methodological context.
3. Voters want a quick result; analysts/journalists want the fact sheet — nobody serves both well today.

**Our competitive edge**:

| Aspect | encuestas.com.pe | encuestaselectorales.pe |
|---|---|---|
| Sources | Own online poll only (+ loose third-party mentions) | Own poll + IEP/Ipsos/Datum/CPI aggregated, comparable |
| Fact sheet | Not visible | Sample size, margin of error, mode, date — always visible |
| Trend chart | No | Yes (538/Datafolha/G1 style) |
| Structure | WordPress blog, one post per poll | Aggregated view per office/district, multi-source |
| Methodological honesty | Doesn't distinguish "online opt-in" from "physical poll" | Labels and explains every mode |

**Conclusion**: we don't compete on post volume, we compete on structure, trust, and comparability.

---

## Products

### Product 1: Public portal (free) — MVP

**User**: any Lima citizen/voter (starting scope: Lima districts).
**Value**: see at a glance what every pollster (physical + our own) says about an office/district, with reliability context.

| Feature | Reference | Status |
|---|---|---|
| Multi-source aggregation per office/district | G1 (Brazil), 538 | To build |
| Visible fact sheet per poll | IEP/Ipsos/Datum/CPI (all publish a PDF with one) | To build |
| Trend chart | 538, Datafolha | To build |
| Interactive own poll (online opt-in) | encuestas.com.pe (same mechanism, better honesty) | To build |
| Candidate directory per district (with judicial records if applicable) | simulatuvoto `/candidatos` + `CandidatoFlags` | Reusable from simulatuvoto |
| Lima landing (district nav) | encuestas.com.pe (structure, not branding) | Planned (`BL-10`) |

**Model**: free. Generates traffic/SEO and credibility — the base the paid products stand on.

### Product 2: B2B for municipal/regional candidates and parties

Same pattern as simulatuvoto's Product 2, adapted: instead of "practice your preferential vote," the value here is **visibility and perception verification**.

**Problem it solves**: a district candidate has no cheap way to know how they're perceived against rivals, or to have a verified profile visible on a neutral site with traffic.

| Feature | Tier |
|---|---|
| Verified/featured candidate profile | Starter |
| Alerts when their district gets a new poll | Starter |
| Dashboard: how their % in the own poll trends weekly | Pro |
| Co-branded candidate landing page | Pro |
| Embeddable results widget for their campaign site | Enterprise |

**Potential volume**: 43 Lima districts × ~10-15 candidates each (district mayoralty) ≈ 500+ potential candidates in phase 1 alone (before adding council members/regional).

### Product 3: Data insights for media/analysts

Same pattern as simulatuvoto's Product 4: advanced dashboard with filters (district, office, week, source), CSV export, aggregate data API. Needs own-poll volume + digitized third-party poll history.

### Product 4: Licensing / geographic scale-up

Later phase: replicate from Lima Metropolitana → Lima Provincias → regions → future elections (2030), or even other countries with the same gap (pollster aggregation without structured comparison).

---

## Monetization roadmap

| Phase | Focus | Revenue |
|---|---|---|
| **1 — Now** | Lima districts: multi-source aggregation + fact sheet + chart + own poll | $0 (positioning/traffic) |
| **2** | Lima mayoralty + expand to more districts/Lima Provincias regional government | $0-low |
| **3** | B2B district candidates (verified profiles, dashboard) | Initial revenue |
| **4** | Media/analyst data insights + regional expansion | Recurring |

## Guiding principle

> "We aggregate and contextualize. We don't opine or predict."
> Be the trust layer between pollsters (who already have the data) and voters (who don't have time to read 4 different PDFs).
