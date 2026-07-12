# Connected backlog — encuestaselectorales-web

> Feature backlog, numbered in execution order (BL-01 first → BL-30 last), grouped by phase. Every spec in `openspec/changes/bl-xx-slug/` only translates one item into a requirement + test — it doesn't repeat research.
> Confirmed start: **legal + GitHub hardening first**, then authority/audience, then Lima-district data (pilot district first). Sources: `docs/business-model.md`, `docs/data-model.md`, `docs/design-references.md`, `docs/devsecops.md`.

## How progress is tracked (two levels, no conflict)

- **`docs/backlog.md` Status** (this file) = cross-item: which `BL-xx` is `not-started | in-progress | blocked | done`. This is "which item are we on."
- **`openspec/changes/bl-xx-slug/tasks.md` checkboxes** = within-item: which task inside the current item is `[x]`. This is "how far into this item."
- They never contradict: an item is `done` here only after all its `tasks.md` boxes are `[x]` and its PR merged. Start of session reads Status here; mid-item work reads its `tasks.md`.

Format per item: **what**, **status**, **depends on**, **entities** (index in `data-model.md`, when applicable), **reference**, optional **why**, **MVP scope (in/out)**, **done when**.

Pick the first `not-started` item whose dependencies are all `done`. Don't skip ahead.

---

## Phase 0 — Governance & legal (FIRST — no external data needed, de-risks everything after)

### BL-01 — GitHub repo hardening (solo-operator posture)
- **Status**: done (2026-07-12) — squash-only, `delete_branch_on_merge`, wiki off, topics set, verified via `gh api`. `has_projects` didn't apply (likely deprecated repo-level field, not blocking). CodeQL deferred to first `.js` file (BL-06/BL-11) — see `devsecops.md`.
- **Depends on**: nothing
- **Reference**: `docs/devsecops.md` (audited gaps + exact `gh` commands)
- **Why first**: `main` auto-deploys to production with no staging (`CLAUDE.local.md`), so the PR gate is the only safety net.
- **Solo-operator constraint**: GitHub won't let you approve your own PR. So `required_approving_review_count=1` + `enforce_admins=on` would deadlock the only maintainer — you could never merge. Those two wait until a 2nd maintainer joins. Today's hardening is everything that tightens safety WITHOUT needing a second human.
- **In** (apply now): keep PR-required, squash-only merges, `delete_branch_on_merge` on. Keep `approvals=0` and `enforce_admins=off` (deliberate, solo). Settings changes — **user runs the `gh` commands** (access-control changes; see `devsecops.md`).
- **Out**: required approval + `enforce_admins` (defer to 2nd-maintainer day); required status checks (needs CI — BL-20); CodeQL default setup — **tried, GitHub rejected it**: repo is 100% HTML today (`gh api .../languages` → `{"HTML":2291}`), 0 JS/TS files, CodeQL doesn't scan plain HTML. Re-attempt once the first `.js` lands (BL-06/BL-11).
- **Done when**: `gh api repos/.../` shows `allow_merge_commit=false` + `allow_rebase_merge=false` + `delete_branch_on_merge=true`. Approval/enforce_admins/CodeQL are documented-deferred, not blockers for closing this item.

### BL-02 — Editorial independence / conflict-of-interest policy
- **Status**: done (2026-07-12) — published at `/politica-editorial.html`, anchor `#firewall` for cross-links. `BL-05` (methodology) and `BL-29` (B2B pricing) must link `#firewall` when they ship.
- **Depends on**: nothing — must exist before BL-29 (B2B) launches, published alongside BL-05
- **Why**: `business-model.md` Product 2 sells featured candidate profiles. A "neutral aggregator" that also takes candidate money needs an explicit firewall (paying for a featured profile never changes poll numbers or aggregation) or the core differentiator (trust) collapses.
- **In**: public policy statement — what's for sale (visibility, verified badge) vs. never for sale (poll results, methodology, rankings)
- **Out**: nothing — blocks BL-29, not optional
- **Done when**: policy published and linked from the methodology page and any future B2B pricing page

### BL-03 — Privacy policy
- **Status**: not-started
- **Depends on**: nothing to publish a baseline now; must be complete before BL-24 (own-poll data collection) goes live
- **Why**: Peru's Ley de Protección de Datos Personales (29733) applies once real personal data is collected.
- **In**: explicit "what we collect / what we never collect" —
  - **Collect**: own-poll responses (demographics: sex, age group, NSE, zone — explicit consent, respondent opts in by answering); cookieless aggregate analytics (country/region/device/referrer, no individual ID); hashed IP (SHA-256, simulatuvoto's `ip_hash` pattern) for anti-abuse dedup only, never raw, never for tracking
  - **Never collect**: device/browser fingerprinting; cross-referencing visitor data with external sources (electoral roll, social media); raw IP storage; any cross-session individual tracking
  - Baseline now (site-wide, covers analytics); expanded before BL-24 activates
- **Out**: nothing — legal requirement once BL-24 activates
- **Done when**: baseline published now; re-reviewed as a blocking gate before BL-24 ships

### BL-04 — Legal & attribution policy (content republication, JNE photos, judicial records)
- **Status**: not-started
- **Depends on**: nothing — must be settled before BL-13 (publishes third-party poll data) and BL-23 (publishes judicial records)
- **Why**: this site republishes third-party pollster data, JNE candidate photos, and (BL-23) candidate judicial records. Each carries a distinct legal risk that, for a solo operator, is the highest-consequence area of the whole project. Constraint 8 in `CLAUDE.md` makes the principle binding; this item is where the concrete rules get written.
- **In**:
  - Pollster data: cite the figure + link the source PDF (safe use / attribution); never republish the full report. Written rule on what's a citation vs. a reproduction.
  - JNE photos: confirm reuse terms (simulatuvoto assumed reusable — verify, don't inherit the assumption).
  - Judicial records (BL-23): a correction / right-of-reply process and a "source: JNE hoja de vida + date" attribution on every badge, to bound defamation risk.
- **Out**: full legal counsel sign-off (baseline rules now; escalate if a dispute arises)
- **Done when**: a short public "sources & corrections" page states the attribution rule, the correction process, and the source link requirement — and BL-13/BL-23 specs reference it as a gate

---

## Phase 1 — Authority & audience (no external data needed; compounds while waiting for JNE)

### BL-05 — Authority shell + social media content plan
- **Status**: not-started
- **Depends on**: BL-02 (link the editorial policy), BL-04 (link the sources/corrections page)
- **Why**: confirmed JNE calendar — election Oct 4, 2026; admitted candidate lists Aug 5; final candidacies Sept 5. Today (Jul 12) no official candidate or municipal-poll data exists. The only controllable lever now is authority + audience.
- **Reference**: AAPOR/IEP-style public methodology (`design-references.md`), "platform, not opinion" positioning from simulatuvoto's `ESTRATEGIA-NEGOCIO.md`
- **In**:
  - "Methodology" page (how sources get aggregated/labeled, honesty about the opt-in own poll)
  - "About us" page (named team, declared neutrality)
  - Contact info in footer/about: email + WhatsApp button (needed before BL-29 B2B can convert a lead)
  - Election calendar content (25 regional governments, 196 provincial mayoralties, 1696 district mayoralties; JNE key dates) — no external data needed
  - Social media content plan (calendar, countdown) — accounts created by the user, not Claude Code
- **Out**: any real candidate/poll data (Phase 2 onward)
- **Done when**: methodology + about + contact published; social content calendar exists for the next 3-4 weeks (today → Aug 5)

---

## Phase 2 — Data foundation (nothing renders without this)

### BL-06 — 43 Lima district catalog
- **Status**: not-started
- **Depends on**: nothing
- **Entities**: #1 District
- **Reference**: `encuestas.com.pe` nav dropdown (43 districts in `design-references.md`)
- **In**: `distrito.json` with id/name/province/region for all 43
- **Out**: provinces outside Lima Metropolitana, full regional hierarchy
- **Done when**: all 43 districts exist as records with a stable id slug (URL-usable)

### BL-07 — Pollster catalog
- **Status**: not-started
- **Depends on**: nothing (parallel to BL-06)
- **Entities**: #6 Pollster
- **Reference**: `design-references.md` (IEP, Ipsos, Datum, CPI + own)
- **In**: `encuestadora.json` with the 5 sources (4 institutional + own)
- **Out**: dynamic pollster onboarding (fixed hand-maintained catalog)
- **Done when**: 5 records exist with id/name/type/website

### BL-08 — Parties and candidates, 1 pilot district
- **Status**: not-started
- **Depends on**: BL-06
- **Entities**: #3 Party, #4 Candidate
- **OPEN DECISION**: which district is the pilot is not yet chosen — pick one with real available data and manageable candidate count before proposing this spec. Blocks the whole Phase 2→4 chain until named.
- **Reference**: simulatuvoto's `partidos.ts`/`Candidato` (shape); real data = JNE municipal lists (available after Aug 5 — before that, use a documented dummy/historical set to unblock the stack)
- **In**: 1 full district (mayoral candidates) as an end-to-end test case
- **Out**: the other 42 districts (replicated by data later, BL-27)
- **Done when**: 1 named district has full candidate data and renders on the district page (BL-10)

---

## Phase 3 — Site shell

### BL-09 — Landing + nav (shell)
- **Status**: not-started
- **Depends on**: BL-06
- **Entities**: #1 District
- **Reference**: `encuestas.com.pe` real nav — structure only, not branding
- **In**: header, nav with "Lima Districts" dropdown (43), footer
- **Out**: Presidential/Regions/Callao nav (post-Lima)
- **Done when**: nav lists all 43 districts and every link resolves to a district URL (even if empty)

### BL-10 — District page (candidate fact sheet)
- **Status**: not-started
- **Depends on**: BL-08, BL-09
- **Entities**: #4 Candidate
- **Reference**: simulatuvoto's `/candidatos` (directory by party/office, JNE photo)
- **In**: pilot district with its mayoral candidate list
- **Out**: judicial records (BL-23)
- **Done when**: the pilot district page shows candidates with photo/party/list number

---

## Phase 4 — The differentiator (aggregation + fact sheet + chart)

### BL-11 — Poll + result shape (already defined)
- **Status**: not-started
- **Depends on**: BL-06, BL-07
- **Entities**: #7 Poll, #8 Result, #9 Fact sheet
- **Reference**: `data-model.md` (JSON shape with `encuestadoraId` already written)
- **In**: validate the `data-model.md` shape works for 1 real pilot-district poll (hand-loaded)
- **Out**: automatic ingestion/scraping of pollster PDFs (manual in MVP)
- **Done when**: ≥1 real `encuesta.json` exists for the pilot district (dummy/historical ok) with `encuestadoraId` != "propia"

### BL-12 — Fact sheet + trend chart component
- **Status**: not-started
- **Depends on**: BL-11
- **Entities**: #9 Fact sheet
- **Reference**: G1's "Metodologia" pattern + 538/Datafolha chart style (`design-references.md`)
- **In**: reusable component that takes 1 `encuesta.json` → bar/chart + fact sheet (sample size, margin of error, date, mode)
- **Out**: multi-poll comparison (BL-13)
- **Done when**: given 1 test `encuesta.json`, the component shows chart + full fact sheet

### BL-13 — District results view (multi-source)
- **Status**: not-started
- **Depends on**: BL-04 (attribution rule), BL-10, BL-11, BL-12
- **Entities**: #10 Multi-source comparison
- **Reference**: G1 (filter by polling institute)
- **In**: pilot district results page listing all its `encuesta.json` (even 1-2), each via BL-12, each with visible source link (BL-04 rule)
- **Out**: interactive own-poll widget (Phase 6)
- **Done when**: the pilot district results page shows ≥1 poll with fact sheet + chart, attributed to its pollster with source link

### BL-14 — Historical trend per candidate-district
- **Status**: not-started
- **Depends on**: BL-13
- **Entities**: #16 Historical trend (time view across #7 Poll / #8 Result rounds)
- **Reference**: confirmed gap — neither `encuestas.com.pe`, G1, nor IEP/Ipsos/Datum/CPI show a candidate's trend across rounds
- **In**: mini line chart with ≥2 rounds for the pilot district (dummy ok) showing rise/fall
- **Out**: projections/forecasting (real history only)
- **Done when**: the pilot district results page shows how ≥1 candidate's % changed between 2 rounds

### BL-15 — Shareable district result card
- **Status**: not-started
- **Depends on**: BL-13
- **Entities**: #17 Shareable result card (source data: #8 Result)
- **Reference**: simulatuvoto's `CardPuntaje`/`CardEncuesta`, adapted — confirmed gap, nobody in the Peruvian polling niche generates a shareable result card
- **In**: exportable PNG "here's how [district] stands" with top candidates + cited source
- **Out**: leaderboard/referrals (B2B, phase 9)
- **Done when**: the pilot district generates 1 downloadable/shareable card with real data

---

## Phase 5 — Cross-cutting (attach as the pages they cover exist)

### BL-16 — Structured SEO (JSON-LD, sitemap, dynamic OG)
- **Status**: not-started
- **Depends on**: BL-09
- **Reference**: simulatuvoto's `sitemap.ts`/`robots.ts`/`JsonLdScript`/`opengraph-image.tsx` — proven in production, confirmed gap across the polling niche
- **In**: sitemap with all 43 district URLs, dynamic OG image per district, basic JSON-LD
- **Out**: nothing — baseline requirement
- **Done when**: any district page has OG meta + a sitemap entry

### BL-17 — Analytics + Search Console
- **Status**: not-started
- **Depends on**: BL-09
- **Why**: BL-05's whole bet is "build audience before data exists" — unmeasurable without this. Also the direct fix for search positioning — Search Console shows what people actually search and whether you rank.
- **In**: **Plausible or Umami (self-hosted), cookieless, no individual tracking** — not GA4 (identifies individuals, needs a consent banner; conflicts with `BL-03`). Country/region/device/referrer aggregate only. + Google Search Console verified, sitemap (BL-16) submitted
- **Out**: GA4 / cookie-based / individual-level analytics, ad pixels
- **Done when**: traffic visible in a Plausible/Umami dashboard and Search Console shows indexed pages

### BL-18 — SEO content/keyword calendar tied to the JNE timeline
- **Status**: not-started
- **Depends on**: BL-05 (content plan), BL-16 (technical SEO)
- **Why**: search intent shifts predictably: now "cuándo son las elecciones municipales 2026" / "qué se elige" → after Aug 5 "candidatos alcaldía [distrito] 2026" → after Sept "encuesta alcaldía [distrito] 2026", "quién va ganando [distrito]". Publishing each wave BEFORE the spike wins the ranking race.
- **In**: keyword list per phase (now / Aug 5 / Sept-Oct), each mapped to the BL-xx page that answers it
- **Out**: paid SEO tools/audits
- **Done when**: each of the 3 phases has a named keyword set + target page/BL-xx

### BL-19 — Base accessibility
- **Status**: not-started
- **Depends on**: BL-09
- **Reference**: simulatuvoto's `accessibility`/WCAG skill — confirmed gap, no polling reference prioritizes this
- **In**: AA contrast, aria-labels on nav/charts, keyboard nav on the own-poll widget
- **Out**: full WCAG AAA audit (AA is enough for MVP)
- **Done when**: nav + district page pass a basic axe check with no critical errors

### BL-20 — CI pipeline as a required status check
- **Status**: not-started
- **Depends on**: BL-06 (needs data for `validate-data.js` to check), BL-01 (branch protection to attach the check to)
- **Reference**: `docs/devsecops.md` — GitHub Actions free/unlimited on this public repo. No CI today; `main` auto-deploys to prod with no staging — CI as a required check is the only automated gate.
- **In**: GitHub Actions workflow running `node scripts/validate-data.js` (BL-11) on every PR; set as a required status check
- **Out**: Lighthouse CI / perf budgets (later)
- **Done when**: a PR with broken data JSON fails the check and can't merge

### BL-21 — Cloudflare in front of Hostinger
- **Status**: not-started
- **Depends on**: nothing; highest value once BL-22 (interactive poll widget) exists — the scrape/bot target
- **Why**: free CDN + DDoS/bot protection. Political sites with a public vote widget are a common target (see BL-25 — `encuestas.com.pe`'s "1 vote per IP+cookie" is trivially bypassed). Also improves Core Web Vitals (SEO signal).
- **In**: DNS proxied through Cloudflare free tier, basic bot-fight-mode/WAF on
- **Out**: paid Cloudflare tiers
- **Done when**: domain resolves through Cloudflare and a basic bot-protection rule is active

---

## Phase 6 — Interactive own poll (doesn't block MVP)

### BL-22 — Own-poll widget per district
- **Status**: not-started
- **Depends on**: BL-10, BL-11
- **Entities**: #6 Pollster ("propia"), #7 Poll (`modalidad: online_opt_in`)
- **Reference**: `encuestas.com.pe` widget (form/radio/Vote-Results) — improved with a visible, honest fact sheet
- **In**: no backend yet — static version linking to an external form or a "coming soon" placeholder on the pilot district
- **Out**: real vote collection (needs backend, Phase 7)
- **Done when**: the pilot district has an "own poll" block visible on its results page (even if not collecting)

### BL-23 — Candidate judicial record badge
- **Status**: not-started
- **Depends on**: BL-04 (correction process + attribution rule), BL-10
- **Entities**: #5 Judicial records/flags
- **Reference**: simulatuvoto's `CandidatoFlags`/`BadgeAntecedente` — reusable, needs a JNE hoja-de-vida source
- **In**: visual badge on the candidate fact sheet if they have a sentence/prior office, with "source: JNE + date" (BL-04), for the pilot district
- **Out**: automatic JNE scraping (manual load in MVP)
- **Done when**: ≥1 pilot-district candidate with a real record shows the badge + its source attribution

---

## Phase 7 — Real backend (once Phase 6 needs to actually collect data)

### BL-24 — Own-poll collection schema
- **Status**: not-started
- **Depends on**: BL-22 validated (demand to activate), BL-03 (privacy policy complete — hard gate)
- **Entities**: #11 Individual response, #12 Public aggregate view, #13 Device token
- **Reference**: simulatuvoto's `001_encuesta_respuestas.sql` — same stratification enums (sex/age/NSE/zone/likely_voter/firmness), national → district scope
- **In**: out of scope until Phase 6 is validated — placeholder to hold the reference
- **Out**: everything, for now

### BL-25 — Anti-abuse for the own poll (extends BL-24)
- **Status**: not-started
- **Depends on**: BL-24
- **Reference**: simulatuvoto's Turnstile + Upstash rate-limit + Zod + IP-hash chain — confirmed gap, `encuestas.com.pe`'s "1 vote per IP+cookie" is trivially bypassable
- **In**: not implemented yet — mandatory note so BL-24 never activates without it
- **Out**: everything, until BL-24 activates

---

## Phase 8 — WhatsApp agent (only after Phase 7's backend/DB is real)

### BL-26 — WhatsApp agent for candidate alerts
- **Status**: not-started
- **Depends on**: BL-24 (needs a real DB to query — no DB, no agent)
- **Why**: `business-model.md` Product 2 promises "alerts when your district has a new poll" (Starter tier). The agent is the delivery channel, matching the author's existing `agente-wsp-*` stack family.
- **In**: nothing yet — placeholder keeping the ordering explicit: static site (Ph 1-6) → backend+DB (Ph 7) → agent reads that DB (Ph 8). Building it before Phase 7 has real data means nothing to alert on.
- **Out**: everything, until BL-24 ships
- **Done when**: defined later, once BL-24 is done

---

## Phase 9 — Scale-up (post-Lima, post-validation)

### BL-27 — Replicate Phase 2-4 to the remaining 42 districts
- **Status**: not-started
- **Depends on**: BL-13 working on the pilot district
- **Done when**: the proven pattern repeats by data, not new spec (same shape, more JSON)

### BL-28 — Lima mayoralty (metropolitan scope)
- **Status**: not-started
- **Depends on**: BL-27
- **Reference**: `business-model.md` phase-2 roadmap

### BL-29 — B2B featured candidate profile / media dashboard
- **Status**: not-started
- **Depends on**: BL-27, real data volume, BL-02 (editorial firewall published)
- **Entities**: #15 B2B client
- **Reference**: Product 2/3 in `business-model.md`

### BL-30 — Pollster quality/bias comparison (house effect)
- **Status**: not-started
- **Depends on**: BL-27, ≥3 real historical rounds per pollster
- **Entities**: #18 Pollster rating / house effect
- **Reference**: 538 pollster-rating methodology (`design-references.md`) — confirmed gap, neither Peru nor Brazil does this locally
- **In**: nothing yet — long-term placeholder, needs history that doesn't exist today
- **Out**: everything, until enough data volume exists

---

## Execution order (numbering IS the order)

```
Phase 0  BL-01 github-hardening ─ BL-02 editorial ─ BL-03 privacy ─ BL-04 legal   [parallel, no blockers]
Phase 1  BL-05 authority shell           (deps: BL-02, BL-04)
Phase 2  BL-06 districts ─ BL-07 pollsters ─ BL-08 pilot candidates (deps: BL-06)
Phase 3  BL-09 shell (BL-06) ─ BL-10 district page (BL-08, BL-09)
Phase 4  BL-11 poll shape (BL-06,07) → BL-12 chart → BL-13 results (BL-04,10,11,12) → BL-14 history / BL-15 card
Phase 5  BL-16 SEO ─ BL-17 analytics ─ BL-18 keywords ─ BL-19 a11y ─ BL-20 CI (BL-01,06) ─ BL-21 cloudflare  [attach as pages exist]
Phase 6  BL-22 own-poll widget (BL-10,11) ─ BL-23 judicial badge (BL-04,10)
Phase 7  BL-24 backend schema (BL-22, BL-03 gate) → BL-25 anti-abuse
Phase 8  BL-26 whatsapp agent (BL-24)
Phase 9  BL-27 replicate (BL-13) → BL-28 lima ─ BL-29 B2B (BL-02) ─ BL-30 house-effect
```

Each `BL-xx` = 1 spec in `openspec/changes/bl-xx-slug/`. The spec doesn't repeat the "why" (here) — only the "exactly what" + the failing-test-first (`CLAUDE.md` constraint 7). Update this file's **Status** field, don't track progress anywhere else.
