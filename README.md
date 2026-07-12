# encuestaselectorales-web

Neutral aggregator of Peruvian municipal/regional election polls (2026) — institutional pollsters (IEP, Ipsos, Datum, CPI) plus an own interactive poll, with visible methodology on every number. Starting scope: Lima districts. Election: Oct 4, 2026.

**Live**: [encuestaselectorales.pe](https://encuestaselectorales.pe) (Hostinger, auto-deploys from `main`)

## Stack

Static site — HTML/CSS/JS, no build step, no framework.

## Run locally

Open `index.html` in a browser. No install, no build, no server.

## Docs

Everything not obvious from the code lives in `docs/` (English, one file = one responsibility). Read `CLAUDE.md` first — it's the strategy brain and points to all of these.

| File | Answers |
|---|---|
| `docs/backlog.md` | What to build, when, current status — `BL-01`…`BL-31`, numbered in execution order |
| `docs/data-model.md` | What each data object is (entity index + JSON shapes) |
| `docs/business-model.md` | Why the product exists — positioning, monetization |
| `docs/design-references.md` | Raw competitor/reference research (facts only) |
| `docs/engineering-standards.md` | How to build — branches, commits, PRs, tests, UX, responsive |
| `docs/devsecops.md` | Repo/production security posture (audited against real GitHub settings) |

## Contributing

- Backlog is numbered in execution order — pick the first `not-started` item whose dependencies are `done` (`docs/backlog.md` **Status**).
- Spec-first via OpenSpec: `openspec/changes/bl-xx-slug/` before code.
- 1 item = 1 branch (`feat/bl-xx-slug`) = 1 PR. `main` is production (no staging) — every change goes through a PR.
- **Test-first where there's logic** (data validation, math): write the failing check before the code. Content pages ship against a checklist. See `docs/engineering-standards.md` §5.

Built by [Neuracode](https://neuracode.dev) — a tech agency, not a political actor. Neutral platform, not opinion.
