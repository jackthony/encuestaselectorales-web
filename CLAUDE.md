@CLAUDE.local.md

# encuestaselectorales-web

## Goal

Become the neutral, trusted source Peruvians check for 2026 municipal/regional election polls — Lima districts first — before Oct 4, 2026. Win on structure, methodology, and trust. Not on content volume (`encuestas.com.pe` already owns volume and has for years).

## Non-negotiable constraints

Every decision downstream must satisfy these. If a task conflicts with one of these, the constraint wins — stop and flag it, don't route around it.

1. **Election Oct 4, 2026 gates reality** (verified against JNE + El Comercio + Correo, 2026-07-12). Admitted candidate lists: Aug 5. Final candidacies: Sept 5. No real candidate/poll data exists before those dates. Build governance + authority first (`BL-01`-`BL-06`), not data catalogs — a data catalog with nothing real in it is wasted motion.
2. **Never individual-level tracking or fingerprinting.** Aggregate signals only (country/region/device, hashed IP for anti-abuse dedup, never raw). Violates Peru's Ley 29733, and it's the exact behavior of a covert political operator — which destroys the one asset this product sells: trust.
3. **Editorial firewall is absolute.** B2B revenue (featured candidate profiles) never touches poll numbers, rankings, or aggregation logic. No exceptions, no "just this once."
4. **Prove once, replicate by data.** `BL-07`-`BL-14` prove the pattern on 1 pilot district. `BL-28` repeats it across the other 42 with JSON, not new engineering. Don't build for 43 districts before 1 works end-to-end.
5. **No backend before its gates.** `BL-25` (own-poll backend) does not activate without `BL-03` (privacy policy) and `BL-26` (anti-abuse) already shipped. Static site until then.
6. **English everywhere in `docs/` and this file** — determinism. Domain/data values (district names, party names, JNE terms) stay Spanish, they're facts not prose. `CLAUDE.local.md` stays Spanish (human ops notes, not spec content, not committed).
7. **Test-first where there's logic; checklist where there's content.** Any `BL-xx` with logic (data validation, % math, parsing) writes its failing check FIRST, watches it fail, then implements to green — no exceptions, that's genuine TDD. Pure-content items (a Methodology page, About copy) can't have a meaningful failing test — they ship against an explicit checklist (the "done when" criterion + accessibility + responsive), not a fake red test. Don't cargo-cult a test onto prose; don't skip one where logic exists. Detail: `docs/engineering-standards.md` §5.
8. **Legal exposure is a first-class constraint, not a backlog afterthought.** This site republishes third-party pollster data, JNE candidate photos, and candidate judicial records. Cite-and-link, never wholesale-reproduce a pollster report. Every judicial claim carries its JNE source + date and a correction path. Verify photo-reuse terms, don't inherit simulatuvoto's assumption. Concrete rules: `BL-05`. When in doubt, publish less.

## Execution model

- Spec before code, always: `openspec/changes/bl-xx-slug/` (`~/.claude/OPENSPEC.md`). No exceptions.
- For any logic-bearing `BL-xx`, its `tasks.md` sequences the failing check as task 1, before implementation — not a "testing" task at the end (constraint 7 applies at proposal time). Content-only items sequence the checklist instead.
- **Two progress levels, no conflict**: `docs/backlog.md` **Status** = which `BL-xx` we're on (cross-item, ground truth for "where are we"). Its `openspec/changes/bl-xx-slug/tasks.md` checkboxes = how far into that item (within-item). An item is `done` in the backlog only after all its `tasks.md` boxes are checked and its PR merged. Read backlog Status at session start; read `tasks.md` mid-item.
- **Archive after merge, every time**: once a `BL-xx`'s PR merges, run `/opsx:archive` before starting the next item — syncs its delta spec into `openspec/specs/<capability>/spec.md` and moves the change under `openspec/changes/archive/`. Skipping this was the actual failure mode for `BL-02`-`BL-06` (all merged, none archived) — a merged PR is not the finish line, an archived spec is.
- 1 `BL-xx` = 1 branch (`feat/bl-xx-slug`) = 1 spec = 1 PR = 1 deploy on merge (no staging — `main` is production). Full session-start protocol: `docs/engineering-standards.md` §2.
- Numbering IS execution order (BL-01 first → BL-31 last). Pick the first `not-started` item whose dependencies are all `done`. Don't skip ahead, don't batch multiple items in one session unless told to.

## Stack

Static HTML/CSS/JS. No build, no framework. Hostinger auto-deploys `main` → `encuestaselectorales.pe`. Nothing to install locally — open `index.html` (`README.md`).

## Docs — one topic each, zero repeated text between them

| Doc | Answers |
|---|---|
| `docs/backlog.md` | What to build, when, current status (`BL-01`-`BL-31`, numbered in execution order) |
| `docs/data-model.md` | What each data object is — entity index + JSON shapes |
| `docs/business-model.md` | Why the product exists — positioning, monetization |
| `docs/design-references.md` | Raw competitor/reference research (facts only) |
| `docs/engineering-standards.md` | How to build — file/folder layout, branches, session protocol, commits, PRs, tests, UX, responsive |
| `docs/devsecops.md` | Repo/production security posture, audited against real GitHub settings |

Own reference (same author/agency, private): `github.com/jackthony/simulatuvoto` — schema, SEO, accessibility, anti-abuse, and virality patterns already proven in production, cited per backlog item where reused.

## Branch protection

`main` requires a PR to merge (tightened per `docs/devsecops.md`). No force-push, no delete.
