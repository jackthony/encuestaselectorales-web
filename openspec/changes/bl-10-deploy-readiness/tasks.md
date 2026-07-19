# BL-10b — Deploy readiness

BL-10 refactored 8 pages to PHP but left production in a state where merging
would silently regress the site. These are its own loose ends, so they close on
the same branch (`feat/bl-10-php-architecture`) before the PR — not as a
separate backlog item, which would mean merging known-broken code.

## 1. The silent killer: `index.html` shadows `index.php`
- [x] 1.1 Apache resolves `DirectoryIndex` in order and serves `index.html` before `index.php`. With both present, production keeps serving the old static page and the entire refactor is invisible — no error, no log entry. Confirm this is the case for the Hostinger Apache config, then fix it.
- [x] 1.2 Delete `index.html` (recoverable in git; `index.php` supersedes it).
- [x] 1.3 Belt and braces: `.htaccess` sets `DirectoryIndex index.php index.html` so the order is explicit even if an `.html` reappears.

## 2. ~138 dead links in pages that are live right now
`politica-privacidad.html`, `politica-editorial.html` and `fuentes-correcciones.html` were not part of the refactor but link into pages it renamed. They are in production today; after the merge their whole district dropdown 404s.

- [x] 2.1 In all three: `/distrito.html?id=` → `/distrito.php?id=` (43 links each, 129 total). **Correction found during 5.1's browser check**: `distrito.php` only reads `$_GET['slug']` (see its own docblock/source), no `id` fallback — the literal `?id=` transform above would have returned 200 on every link but silently rendered the generic "no district selected" empty state instead of the actual district, for all 129 links. Implemented as `/distrito.php?slug=` instead so the links actually work; `scripts/validate-nav.js` updated to match and re-verified in-browser (Miraflores, Comas).
- [x] 2.2 `/metodologia.html` → `/metodologia.php`, `/quienes-somos.html` → `/quienes-somos.php` (3 each).
- [x] 2.3 `/index.html` → `/` (3).
- [x] 2.4 Grep the whole tree for any remaining `href` to a `.html` file that no longer exists. Report anything found rather than guessing at a target.
- [x] 2.5 **Do not** restyle or convert these three pages to PHP partials. That is PD-03 debt and belongs to BL-11. This task fixes links only — smallest diff that unbreaks production.

## 3. `.htaccess` (does not exist yet)
- [x] 3.1 `DirectoryIndex index.php index.html`.
- [x] 3.2 Disable directory listing (`Options -Indexes`).
- [x] 3.3 Deny direct HTTP access to `includes/` and `partials/` — they are meant to be `require`d, never fetched. **Verify first** whether any JS fetches from those paths; if something does, report it instead of breaking it.
- [x] 3.4 **Check before denying `data/`**: client-side JS may `fetch()` the JSON catalogs. Grep `assets/js/` and inline scripts for `fetch`/`XMLHttpRequest` against `data/`. If anything reads them from the browser, `data/` stays public — say so in the task log rather than silently blocking it.
- [x] 3.5 Do not add security headers, HTTPS redirects or origin locking here. That is BL-12 and it depends on Cloudflare being in front, which it is not yet.

## 4. CI — nothing currently runs the checks
`.github/workflows/` is empty. `scripts/check-refactor.php` and the eight `scripts/validate-*.js` scripts only run when someone remembers, which means they stop running.

- [x] 4.1 One workflow, triggered on `pull_request` and on `push` to `main`.
- [x] 4.2 Runs `php scripts/check-refactor.php` (PHP 8, `shivammathur/setup-php`) and every `scripts/validate-*.js` (Node, no dependencies to install).
- [x] 4.3 Fails the job on any non-zero exit. No `continue-on-error`.
- [x] 4.4 Verify it actually fails when it should: temporarily break one thing (drop a class from a page), confirm red, revert, confirm green. A CI job never observed failing is not known to work.
- [x] 4.5 Do not mark it a required status check in branch protection — that is a repo setting, owner's call, not a code change.

## 5. Verify
- [x] 5.1 `C:\xampp\php\php.exe -S localhost:8000` — load the 8 PHP pages plus the 3 legacy HTML pages. Every nav link resolves, zero 404s, console clean.
- [x] 5.2 `C:\xampp\php\php.exe scripts/check-refactor.php` still 8/8 plus both partial checks.
- [x] 5.3 Confirm `/` serves the new `index.php` content, not the old page.

## Out of scope — do not touch
- `openspec/changes/bl-11*`, `bl-12*`, `bl-13*`, `bl-14*`, `bl-15*` — in progress elsewhere.
- `canvas-gemini/` — the folder is intentionally deleted; a new district prototype lands there later.
- Restyling the 3 legacy HTML pages (BL-11).
- Anything Cloudflare, database or `/api/` related.

## Known unknown to report, not solve
`CLAUDE.local.md` lists the Hostinger PHP version as unverified. The refactor assumes PHP 8. If the plan runs 7.x this breaks on deploy and nothing local would reveal it. Flag it in the final report; the owner checks hPanel.
