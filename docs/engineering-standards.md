# Engineering standards — encuestaselectorales-web (mandatory)

> How this project gets built, mechanically. English on purpose — every doc under `docs/` is English for LLM determinism (see `CLAUDE.md`). Domain/data values (district names, party names, JNE terms) stay in Spanish since they're real-world Peruvian entities, not prose.

---

## 1. Branch topology (DevOps)

```
main  (protected: PR required, no force-push, no delete)
  |     auto-deploys to Hostinger public_html on every merge
  |
  +-- feat/bl-xx-slug   1 per backlog item, short-lived, deleted after merge
  +-- fix/...           bug fixes outside a BL item
  +-- chore/...         maintenance, no spec (deploy config, etc.)
```

- **Trunk-based, single environment.** No `develop`, no release branches — `main` IS production (Hostinger auto-deploy from `main`, per `CLAUDE.md`). PR review is the only gate before prod.
- Branch name = same slug as `openspec/changes/bl-xx-slug/` = same id as the PR title. One chain: backlog item → spec → branch → PR → merge → deploy.
- Branch lives exactly as long as its `BL-xx`. No stacking multiple items on one branch.

## 2. Session protocol — so every session lands precisely on task

At the start of any session touching this repo:

1. Read `docs/backlog.md` **Status** column. Pick the first `not-started` item whose `depends on` are all `done`.
2. Check current git branch. If already on `feat/bl-xx-*` mid-work, resume that item — don't start a new one.
3. Confirm `openspec/changes/bl-xx-slug/` exists for the item. If not, propose it first (spec-before-code is mandatory, see `~/.claude/OPENSPEC.md`).
4. Stay inside that branch/spec scope. One `BL-xx` per session unless the user explicitly asks for more.
5. On completion: mark the item `done` in `docs/backlog.md`, open the PR, stop — don't auto-start the next item.

`docs/backlog.md` Status column is the single source of truth for "where are we." No separate state file, no reconstructing progress from git log each session.

## 3. Commits

Conventional Commits, referencing the BL id:

```
feat(bl-06): 43 Lima district catalog
fix(bl-12): margin of error not shown when 0
docs(backlog): renumber items in execution order
chore: .gitignore config
```

Types: `feat`, `fix`, `docs`, `chore`, `refactor`, `test`. No `--no-verify`, no amending pushed commits.

## 4. Pull requests

- 1 PR per `BL-xx` (same scope as its spec — if the PR grows bigger than the spec, the spec was cut wrong).
- Mandatory description: what changes, which `BL-xx`/spec it references, how it was tested (the backlog "done when" criterion + the spec's deterministic test).
- No direct merge to `main` (already blocked by branch protection) — always via PR, always reviewed before approval.

## 5. Tests — test-first where there's logic, checklist where there's content

Two kinds of `BL-xx`, two rules. Don't blur them (`CLAUDE.md` constraint 7).

**Logic items** (data validation, % math, parsing, a script, a component with behavior) — genuine TDD, no exceptions:

```
1. Take the spec's "done when" criterion
2. Write the check FIRST — it must FAIL (red), because the feature doesn't exist yet
3. Implement the minimum that makes it pass (green)
4. PR states both: what the red check was, what made it green
```

If a logic item's "done when" can't be turned into a failing check before the code, the spec wasn't concrete enough — fix the spec, don't skip the test.

**Content items** (a Methodology page, About copy, a policy page — BL-02/03/04/05) — there is no meaningful "failing test" for prose. They ship against an explicit **checklist**, not a fake red test: the "done when" criterion is met + accessibility (§below) + responsive (§7) + any required links resolve. Writing `assert(pageExists)` for an HTML page is theater — don't. The honesty of "no exceptions" depends on not pretending content is logic.

Static site, no build → lightweight checks, no heavy framework (Jest/Vitest would need Node+build tooling that doesn't exist and isn't justified for plain HTML/CSS/JS). TDD is about the ORDER (test before code), which a plain node script satisfies fine:

| What's checked | How (no new dependencies) | Kind |
|---|---|---|
| Data shape (`distrito.json`, `partido.json`, `candidato.json`, `encuesta.json`) | `scripts/validate-data.js` (pure `node`, no libs) — required fields, unique ids, cross-refs (`encuestadoraId` in catalog, `distritoId` exists). `node scripts/validate-data.js` | **Logic — test-first**: write the check for the new field/ref before the JSON has it, watch it fail, then add data |
| % math / parsing in any JS (e.g. BL-12 chart, BL-14 trend delta) | `assert`-based mini-test next to the function | **Logic — test-first** |
| Base accessibility (BL-19) | axe DevTools on every new page | Checklist (not a red/green test) |
| Responsive (§7) | Manual check at the 3 breakpoints | Checklist |
| Valid HTML | W3C Validator (or one-off `npx html-validate`) | Checklist |

Ponytail rule on tooling: mini-tests stay `assert`-based and framework-free until logic volume actually justifies Jest/Vitest. The discipline is the ORDER, not the framework — "no framework yet" is never an excuse to skip writing a logic check first.

## 6. UX/UI

- **Mobile-first**: CSS defaults to mobile, widens via `min-width` media queries — never the reverse.
- **Design tokens as CSS custom properties** (`:root { --color-... }`), no repeated hardcoded values. Neutral palette for site chrome (nav, background, text) — **party colors live only in the data** (`partido.color`), never in layout, same principle as simulatuvoto's `columna-tokens.ts` (keep political brand color separate from UI color).
- Charts (BL-12, trend): follow the `dataviz` skill before picking colors/shapes — already available in the harness, use it when building that component.
- No JS framework dependency — vanilla JS, same criterion as the already-locked stack decision.

## 7. Responsive

Same breakpoints as simulatuvoto (same author/agency, consistent with a pattern already proven in production — that repo's `docs/ARQUITECTURA.md`):

| Breakpoint | Range | Layout |
|---|---|---|
| Mobile | < 768px | 1 column, simplified nav (no full dropdown) |
| Tablet | 768–1199px | 2 columns where applicable (e.g. candidate list + fact sheet) |
| Desktop | ≥ 1200px | Full layout, nav with district dropdown visible |

Manual checklist per new page: test all 3 widths before merging the PR (part of every `BL-xx`'s "done when" criterion).

---

## Full flow per BL-xx

```
read docs/backlog.md status -> pick next unblocked item
  -> openspec propose bl-xx (spec first)
  -> branch feat/bl-xx-slug
  -> conventional commits
  -> deterministic test (validate-data.js or equivalent)
  -> responsive checklist (3 breakpoints) + base accessibility
  -> PR (description: BL-xx + how it was tested) -> review -> merge to main -> auto-deploy
  -> mark BL-xx "done" in docs/backlog.md
```
