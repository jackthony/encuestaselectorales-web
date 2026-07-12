# encuestaselectorales-web

Neutral aggregator of Peruvian municipal/regional election polls (2026), covering both institutional pollsters (IEP, Ipsos, Datum, CPI) and an own interactive poll — with visible methodology on every number. Starting scope: Lima districts.

**Live**: [encuestaselectorales.pe](https://encuestaselectorales.pe) (Hostinger, auto-deploys from `main`)

## Stack

Static site — HTML/CSS/JS, no build step, no framework. See `CLAUDE.md` for the full rationale.

## Run locally

Open `index.html` directly in a browser. No install, no build, no server required.

## Project docs

Everything that isn't obvious from the code lives in `docs/` (English, one file = one responsibility):

| File | What it answers |
|---|---|
| `docs/business-model.md` | Why this product exists, monetization |
| `docs/design-references.md` | Raw research on competitors/references |
| `docs/data-model.md` | What each data object is (entities + JSON shapes) |
| `docs/backlog.md` | What to build and when (source of truth for progress) |
| `docs/engineering-standards.md` | How to build it (branches, commits, PRs, tests, UX, responsive) |
| `docs/devsecops.md` | Repo/production security posture |

Read `CLAUDE.md` first — it points to all of the above.

## Contributing

1 branch per backlog item (`feat/bl-xx-slug`), spec-first via OpenSpec (`openspec/changes/bl-xx-slug/`), PR required to merge to `main`. Full flow in `docs/engineering-standards.md`.
