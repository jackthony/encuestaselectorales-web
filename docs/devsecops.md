# DevSecOps — encuestaselectorales-web

> Production security posture. Repo is public on GitHub (confirmed via `gh api`, 2026-07-12) — that unlocks several GitHub Advanced Security features **free**, most already on. Settings changes below are access-control/security changes — Claude Code documents them here but does not apply them silently; run the commands yourself or ask Claude Code to run one at a time with explicit confirmation.

## Audited current state (2026-07-12, via `gh api`)

| Feature | Status | Note |
|---|---|---|
| Repo visibility | `public` | Unlocks free secret scanning, push protection, Dependabot, CodeQL |
| Secret scanning | `enabled` | Good, no action |
| Secret scanning push protection | `enabled` | Good, no action — blocks commits containing known secret patterns |
| Dependabot security updates | `enabled` | Good, no action |
| Dependabot vulnerability alerts | `enabled` (204 on `/vulnerability-alerts`) | Good, no action |
| CodeQL (code scanning) | `not-configured` | **Deferred, not a gap** — tried BL-01, GitHub rejected: repo language is 100% HTML (`gh api .../languages` → `{"HTML":2291}`), 0 JS/TS files exist. CodeQL doesn't scan plain HTML. Re-run the same command once the first `.js` file lands (naturally BL-06/BL-11, `validate-data.js`) |
| GitHub Actions workflows | `0` | **Gap** — no CI exists (see `backlog.md` BL-20) |
| Branch protection: PR required | `on` | Good |
| Branch protection: required approvals | `0` | **Deliberate (solo)** — GitHub blocks self-approval; requiring 1 would deadlock the only maintainer. Raise to 1 when a 2nd maintainer joins. |
| Branch protection: enforce_admins | `off` | **Deliberate (solo)** — owner must be able to merge; with approvals=0 and no 2nd human, enforcing admins would lock merges. Enable alongside approvals=1 later. |
| Branch protection: required status checks | none configured | Expected — no CI exists yet (BL-20 fixes this) |
| Branch protection: force-push / delete | blocked | Good, no action |
| `delete_branch_on_merge` (repo setting) | `true` (applied 2026-07-12, BL-01) | Done |
| Commit signature verification | `off` | Optional — low priority for a solo-maintainer static site |
| Default Actions workflow permissions | `read` | Good, already secure default — no action, noted for BL-20 (workflows inherit read-only `GITHUB_TOKEN` unless a job explicitly requests more) |
| Merge strategies | squash-only (applied 2026-07-12, BL-01) | Done — history is 1:1 with the backlog |
| PR template | now exists (`.github/pull_request_template.md`, 2026-07-12) | Done — enforces the BL-xx/TDD-evidence/done-when format from `engineering-standards.md` §4-5 |
| Wiki | `disabled` (applied 2026-07-12, BL-01) | Done |
| Projects | still `enabled` — PATCH accepted, had no effect | **Known no-op** — `has_projects` is likely a deprecated repo-level field now that GitHub Projects moved to org/user-level; not worth chasing further, doesn't conflict with anything since nobody uses it |
| Repo topics | `elections`, `peru`, `polls` (applied 2026-07-12, BL-01) | Done |

## Target state — BL-01 (solo-operator, apply now)

Access-control/settings changes — **the user runs them** (safety boundary on access controls). Solo-operator posture: everything that tightens safety without needing a second human. `required_approving_review_count=1` and `enforce_admins` are deliberately NOT here — they'd deadlock a solo maintainer (see the audit table).

```bash
# Auto-delete branches after merge (matches engineering-standards.md)
gh api -X PATCH repos/jackthony/encuestaselectorales-web \
  -f delete_branch_on_merge=true

# Squash-only merges (clean 1 BL-xx = 1 commit history)
gh api -X PATCH repos/jackthony/encuestaselectorales-web \
  -f allow_squash_merge=true -f allow_merge_commit=false -f allow_rebase_merge=false

# CodeQL default setup — DEFERRED, don't run yet: repo is 100% HTML today,
# GitHub rejects javascript-typescript with "language not present in repository".
# Re-run this once the first .js file exists (BL-06/BL-11):
# gh api -X PATCH repos/jackthony/encuestaselectorales-web/code-scanning/default-setup \
#   -f state=configured -f query_suite=default -f 'languages[]=javascript-typescript'

# Optional: disable unused surfaces (backlog.md is the single tracker, not Issues/Projects/Wiki)
gh api -X PATCH repos/jackthony/encuestaselectorales-web \
  -f has_wiki=false -f has_projects=false

# Optional: repo topics (discoverability, not security)
gh repo edit jackthony/encuestaselectorales-web --add-topic peru --add-topic elections --add-topic polls
```

## Deferred to 2nd-maintainer day (do NOT apply while solo)

```bash
# ONLY once a 2nd maintainer exists — otherwise the solo owner can never merge their own PR.
gh api -X PUT repos/jackthony/encuestaselectorales-web/branches/main/protection/required_pull_request_reviews \
  -f required_approving_review_count=1
gh api -X POST repos/jackthony/encuestaselectorales-web/branches/main/protection/enforce_admins
```

`BL-01` owns applying the "apply now" block. Once BL-20 (CI pipeline) ships, add its check as a required status check too:

```bash
gh api -X PATCH repos/jackthony/encuestaselectorales-web/branches/main/protection/required_status_checks \
  -f strict=true -f 'checks[][context]=validate-data'
```

## Why this matters for THIS project specifically

There is **no staging environment** — Hostinger auto-deploys straight from `main` on every merge (`CLAUDE.local.md`). That collapses the usual dev→staging→prod safety net into a single gate: **the PR**. While solo, that gate is "the diff went through a PR + CI passed + CodeQL clean," not human approval (nobody to approve). Everything applied now exists to make that hold:

- PR-required + squash-only — every prod change is one reviewable, revertable commit, never a raw push.
- CI as a required check (BL-20) — a broken `encuesta.json` (bad margin of error, missing `encuestadoraId`) can't reach production readers.
- CodeQL — catches JS issues before they ship, free, zero maintenance once configured.
- Secret scanning (already on) — matters more once Track E adds real backend credentials (Supabase/Turnstile keys, per `data-model.md` §2).

## No GitHub MCP connected this session

Checked available MCP servers and skills — no GitHub-specific MCP server or skill is connected in this session. All the audit and recommendations above used the `gh` CLI directly (already authenticated as `jackthony`, per `CLAUDE.local.md`), which covers the same ground. If a GitHub MCP server gets connected in a future session, the same operations apply through it instead.

## Related backlog items

- `BL-01` — GitHub hardening (owns applying the settings in this doc)
- `BL-20` — CI pipeline as a required status check
- `BL-21` — Cloudflare in front of Hostinger (DDoS/bot protection, not a GitHub setting but same "production security" concern)
- `BL-25` — anti-abuse for the own-poll backend (Phase 7)

## Secrets policy (for when Track E lands)

No secrets exist in this repo today (fully static, no API keys). When Track E (own-poll backend) ships:
- Backend credentials (Supabase, Turnstile, etc.) go in GitHub Actions secrets / Hostinger environment variables — never committed, never in `docs/`.
- `.gitignore` already covers `.env*` (added 2026-07-12) so this doesn't require a future fix.
