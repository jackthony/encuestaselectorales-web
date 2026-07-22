# Handoff — Production session snapshot

This file is gitignored on purpose. It stores live credentials, deploy notes,
and the exact production state for the current session so nothing gets lost
across restarts.

## Current session snapshot

- Repo branch used for production push: `main`
- Production push completed: `git push origin main`
- Hostinger domain: `encuestaselectorales.pe`
- Hostinger account username: `u185878096`
- Production PHP version: `8.3.30`
- Live site now serves the updated PHP code from `main`
- The remaining production-sensitive files live in `config/` locally and are
  ignored by Git
- If the site ever falls back to an empty home again, the first thing to check
  is that `config/db.php` is present in the deployed webroot path

## Why this exists

Owner wants the online-encuestas backend live now, ahead of BL-12 (Cloudflare),
because `index.php`/`sondeos.php`/`distrito.php` currently always show the
"no hay encuestas" empty state — not a bug, just no backend has ever created
a real `online_propia` round. Owner also has real JNE candidate data
(`lista-candidatos/*.csv`) to import, and flagged a data-quality issue in one
of the URL columns (details below, already root-caused).

Three decisions the owner already made this session — do not re-litigate:
1. **Photos**: hotlink JNE URLs directly. No local download/cache, no DNI-based
   filename scheme (the CSVs have no DNI field at all).
2. **Sequencing**: build DB/backend (BL-13, BL-13b, BL-14) now. BL-12
   (Cloudflare WAF/rate-limit) is an explicit fast-follow, not a blocker.
3. **Hostinger DB**: provision a new dedicated DB now (done — see below).

## What's already done

- **Hostinger MySQL DB provisioned** via `mcp__hostinger-hosting` MCP tools:
  - Database: `u185878096_encuestas`
  - DB user: `u185878096_encuestas_app`
  - Password: `Codexito1234.`
  - Assigned to domain `encuestaselectorales.pe` (account username `u185878096`)
  - Hostinger account has ~25 other domains on it (shared hosting, multiple
    unrelated projects) — always double check `domain` filters when calling
    `hosting_listAccountDatabasesV1` etc., don't touch the other 2 unrelated
    DBs already on the account (`u185878096_5lmBt`, `u185878096_78LL7`).
  - phpMyAdmin sign-in link: `mcp__hostinger-hosting__hosting_getPhpMyAdminLinkV1`
    with `username: "u185878096"`, `name: "u185878096_encuestas"` — generates a
    fresh single-use sign-on URL each call (the one already fetched this
    session has likely expired — call it again, don't reuse a stale link).
  - The production DB was seeded from the repo SQL after the schema existed.
  - The current app logic reads rounds from `encuestas` via `includes/db.php`
    and `includes/encuestas.php`.

- **FTP / file deployment details**
  - FTP host: `151.106.97.87`
  - FTP user: `u185878096.encuestaselectorales.pe`
  - FTP password: `Codexito1234.`
  - FTP port: `21`
  - Target folder: `public_html`
  - `curl`/PowerShell FTP from this machine failed to connect to port 21
    (`TcpTestSucceeded = False`), so production publication was done by
    pushing the repo branch to `main` and letting the host pick it up.

- **openspec/changes/bl-13b-encuestas-rondas-schema/**: fully drafted
  (proposal/design/specs/tasks, all 4 artifacts `done`). Read `design.md`
  before touching anything — it documents the `encuestas` table shape, the
  `includes/db.php` dual-path connection bootstrap, and why `campo_externa`
  stays in JSON while `online_propia` moves to MySQL.
- **openspec/changes/bl-13-db-antihack-schema/** and
  **openspec/changes/bl-14-vote-endpoint/**: already fully drafted from an
  earlier session (not by me this turn) — proposal/design/tasks all exist,
  tasks all unchecked. Read both `tasks.md` files fully before starting;
  they're detailed and sequence-binding (failing tests before schema/endpoint,
  per `CLAUDE.md`'s test-first rule).
- **`includes/db.php`**: PDO bootstrap that tries `__DIR__ . '/../config/db.php'`
  first and then `dirname($_SERVER['DOCUMENT_ROOT']) . '/config/db.php'`.
  Returns a PDO instance. Each `config/db.php` must return:
  `['dsn' => ..., 'user' => ..., 'pass' => ...]`.
- **`db/migrations/001_create_votos_interactivos.sql`** and
  **`db/migrations/002_create_encuestas.sql`**: written per the two design
  docs above. **Not yet applied to any database** (neither production nor a
  test DB) and **not yet tested** against the BL-13 tasks.md §1 failing-test
  requirements (CGNAT simulation, diaspora GPS coordinate, GPS-required
  rejection, blanco/viciado distinction). This is the first thing to finish.

## What's NOT done — pick up here

Work through these in order (each one's own `tasks.md` has the granular
checklist — follow it, don't reinvent):

1. **Finish BL-13**: write the failing test (`tasks.md` §1 — a PHP/PDO script
   is fine, plain SQL assertions are limited), confirm it fails against the
   old `docs/reference/db-schema-draft.sql`, then confirm
   `001_create_votos_interactivos.sql` passes it. The spec says this should
   run against "a fresh test database" — practically, either: (a) get local
   MySQL working (this session hit a wall: a MySQL/MariaDB instance was
   already running on this Windows machine on port 3306 with a root password
   already set that I don't have — ask the owner for it, don't try to reset
   it), or (b) provision a second throwaway Hostinger DB via
   `hosting_createAccountDatabaseV1` purely for this test and drop it after.
   Don't skip the test to save time — `CLAUDE.md`'s workflow rule is explicit
   about this.
2. **Apply both migrations** to the real `u185878096_encuestas` DB via
   phpMyAdmin (or direct PDO if Codex's network allows it).
3. **Keep `config/db.php`, `config/security.php`, and `config/ip.key` in sync**:
   - Locally they live under `config/` and are gitignored.
   - Production needs the same values available to the deployed app.
   - If the site ever stops showing rounds again, the first recovery step is
     to confirm those files still exist where the app expects them.
4. **BL-14**: implement `/api/votar.php` per its `tasks.md` (rate limit
   first, GPS mandatory, district whitelist, HMAC+AES-256-GCM, trust_score
   never in the response). Ship a **minimal** `includes/trusted-ip.php` stub
   now since BL-12 is deferred — just `REMOTE_ADDR` unless an explicit
   `LOCAL_DEV` config flag is set (per BL-14 tasks.md §3.4, which already
   anticipates this) — with a comment noting BL-12 will harden it later with
   the real Cloudflare-IP-range check. Don't build the Cloudflare-range logic
   itself now, that's BL-12's job.
5. **BL-13b's own tasks.md §3-7**: `includes/encuestas.php` read path, wire
   `index.php`/`sondeos.php`/`distrito.php` to it instead of the always-empty
   `data/encuesta.json` `online_propia` path, and the operator-only
   `scripts/crear-encuesta.php` CLI creation script (not a public endpoint —
   BL-15's admin dashboard, which needs BL-14's auth, is the future
   session-authenticated version of this).
6. **Candidatos import** (`lista-candidatos/*.csv` → `data/candidato.json`):
   - Two CSVs today: `consolidado lima - LIMA (Metropolitana) - PROVINCIAL.csv`
     (22 candidates, cargo = Alcalde Provincial de Lima) and
     `CONSOLIDADO CALLAO - CALLAO - REGIONAL.csv` (8 candidates, cargo =
     Gobernador Regional Callao). **Neither is the distrital-race data the
     site currently models** (`data/candidato.json` today only has
     `alcalde_distrital` records, 8 Miraflores candidates, 2022 historical).
     New `cargo` values (`alcalde_provincial`, `gobernador_regional`) need to
     be added to whatever candidato schema/rendering logic exists — check
     `candidato.php` and `distrito.php` for hardcoded assumptions about only
     one race type per district before assuming this is a drop-in data change.
   - **No DNI field in either CSV** — matches decision #1 above (hotlink
     photos, no local filename scheme needed).
   - **Column mapping, already verified this session** (fetched all three
     URL patterns via `WebFetch`, which isn't subject to this machine's
     network sandbox):
     - `Link Logo Partido` (`sipes.jne.gob.pe/documentos/logotipo/{id}.jpg`) —
       **dead domain, DNS doesn't resolve at all.** Don't use it. The
       existing `data/partido.json` already has party colors/branding from
       earlier work (BL-10) — check there first before treating this as a
       blocker for party branding.
     - `Link Foto Candidato` (`sroppublico.jne.gob.pe/Consulta/Simbolo/GetSimbolo/{id}`)
       — **works, returns a valid JPEG, but it's the party symbol, not a
       candidate photo** (same numeric `id` as `Link Logo Partido`, just a
       working mirror of it). Mislabeled column name in the source CSV.
     - `Foto Adicional` (`mpesije.jne.gob.pe/apidocs/{uuid}.jpg`, present only
       in the Callao CSV — check whether future per-district CSVs include it
       too) — **this is the real candidate photo**, valid JPEG confirmed.
   - Given decision #1, the import should store the `Foto Adicional` URL
     (when present) as the candidate's photo, hotlinked directly — matching
     the CSV column, not the misleadingly-named one.
7. **Wire the hub columns** (BL-13b tasks.md §4) once `encuestas` has at
   least one real row to prove the end-to-end path.

## Things I deliberately did NOT do (respect these boundaries)

- Did not touch `openspec/changes/bl-12-cloudflare-perimeter/` — explicitly
  deferred.
- Did not create any session-authenticated admin UI — that's BL-15, blocked
  on BL-14's auth existing.
- Did not attempt the national UBIGEO catalog (~1874 distritos) — out of
  scope per BL-13b's own proposal, flagged as a future change.
- Did not run any migration against production yet — see item 2 above.

## Session task tracker (for reference, mine — not authoritative for Codex)

My own `TaskList` this session had these 8 items; #1 (DB provision) and #2
(BL-13b proposal) were the only ones completed before handoff:
1. Provision Hostinger MySQL DB — mostly done, config/db.php placement open
2. Draft BL-13b openspec proposal — done
3. Implement BL-13 votos_interactivos schema — SQL written, untested
4. Implement BL-13b schema + apply migrations — SQL written, not applied
5. Implement BL-14 /api/votar.php — not started
6. Build minimal encuesta-creation path — not started
7. Import candidatos from CSVs — not started (root-cause analysis done, see above)
8. Wire index.php/sondeos.php encuestas hub to DB — not started

## Rotate later

- `u185878096_encuestas_app` MySQL password
- FTP password for `u185878096.encuestaselectorales.pe`
- `config/ip.key`
- `config/security.php` salt / trust config if policy changes
- Any temporary production config copied for this deployment
