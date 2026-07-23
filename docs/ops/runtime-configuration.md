# Runtime configuration

Production secrets must not be committed or placed below the public document root.

The repository root is deployed to `public_html`, and the root `.htaccess` routes all
requests through `public/`. The production environment file belongs one level above
the repository root:

`/home/u185878096/domains/encuestaselectorales.pe/.env`

`bootstrap/app.php` detects that external file automatically. `APP_ENV_PATH` may
override the directory when Hostinger uses a different layout.

## Laravel

Laravel reads runtime settings from an external environment file or process environment.
The production release requires:

- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_KEY`
- `APP_URL`
- `APP_TIMEZONE=America/Lima`
- `DB_CONNECTION=mysql`
- `DB_HOST`
- `DB_PORT`
- `DB_DATABASE`
- `DB_USERNAME`
- `DB_PASSWORD`
- `VOTE_IP_HMAC_KEY`
- `VOTE_DEVICE_HMAC_KEY`
- `VOTE_ENCRYPTION_KEY`
- `VOTE_ENCRYPTION_KEY_VERSION`
- trusted proxy configuration appropriate to the active Hostinger/Cloudflare path
- `VOTE_TERRITORY_BOUNDS_JSON` with approved bounds for every territory accepting votes

Encryption and HMAC keys must be independent. Key values must not appear in logs,
deployment archives, CI variables printed to a job, or files under Laravel `public/`.

## Legacy compatibility window

The compatibility bootstrap accepts either:

- an external PHP config file selected through `VOTE_DB_CONFIG` or
  `CODEX_DB_CONFIG`; or
- `DB_DSN`, `DB_USERNAME`, and `DB_PASSWORD` process variables.

The expected file shape is documented in `config/db.example.php`. The live file must
remain Git-ignored and outside `public_html`.

Vote security compatibility settings continue to use an external file selected through
`VOTE_SECURITY_CONFIG` or `CODEX_SECURITY_CONFIG` until the Laravel vote endpoint is
authoritative.

## Rotation

Any credential previously committed or shared in development conversation must be
rotated after the Laravel production cutover. Removing a value from the current tree
does not remove it from Git history.

## Release procedure

1. Run the complete CI-equivalent test, style, and import validation suite.
2. Push the verified commit to `main`; Hostinger Git Deploy installs Composer from the
   root `composer.json`.
3. Ensure the external `.env` exists before switching traffic.
4. Run `php artisan migrate --force`.
5. Choose and record the vote migration mode:
   - `clean-start`: leave `interactive_votes` empty and retain legacy votes only
     in the legacy table and backup; or
   - `preserve`: run `php artisan legacy:backfill --dry-run`, review skips, run
     `php artisan legacy:backfill`, and reconcile before switching traffic.
6. Run the approved catalog import with `--publish`.
7. Run `php artisan db:seed --class=InitialSurveyRoundsSeeder --force`.
8. Run `php artisan optimize` and verify `/api/health`.
9. Smoke test the home, both initial survey scopes, vote persistence, duplicate
   rejection, mobile GPS, and social sharing.

For a self-contained rollback artifact, run:

`powershell -File scripts/build-laravel-release.ps1`

The builder uses only tracked files, installs `--no-dev` dependencies, excludes
environment secrets, and writes immutable commit metadata into `RELEASE.json`.
