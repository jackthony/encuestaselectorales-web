# BL-15 — Secure Admin Dashboard

## Why

BL-14 encrypts every stored voter IP specifically so a raw DB dump doesn't expose it. This item is where that encryption gets *used* — which means it is also where a single compromised session undoes it for the entire dataset. Combined with exact GPS coordinates and political preference, `design.md` names this the most sensitive thing the project will ever hold: political opinion tied to location and network identity, for thousands of Peruvians, in an election year, on a platform whose only asset is neutrality.

The security bar for one operator's login is therefore not "consistent with the rest of the site" — the rest of the site is deliberately, correctly public. This page is the one place that isn't, and it protects the one dataset a breach would turn into a targeting list.

## What changes

1. `/admin/` — `password_hash()` with `PASSWORD_ARGON2ID`, mandatory TOTP second factor, exponential login backoff per username and per IP, generic failure messages, `hash_equals()` for token comparisons.
2. Hardened session config (`HttpOnly`, `Secure`, `SameSite=Strict`, `session_regenerate_id(true)` on login, idle + absolute timeouts).
3. **Aggregates-by-default dashboard** — counts, percentages, heatmap density computed without touching the AES key.
4. **Per-row decryption as a separate, explicit, audit-logged action** — never a page-load side effect.
5. **Append-only audit log** — logins, decryptions, exports. The DB user backing `/admin/` gets `INSERT`/`SELECT` on it, never `DELETE`/`UPDATE`.
6. `.htaccess` protection for `/admin/` (deny-by-default except the front controller, no directory listing, `noindex`, `robots.txt` disallow).
7. CSV export, if it ships: aggregates by default, raw rows behind a second confirmation and an audit-log entry, no GPS/decrypted IP in anything that leaves the server unless explicitly requested per row.

## Explicitly out of scope

- Multi-user roles/permissions — one operator (`BL-01`'s solo-operator hardening), one account, one TOTP secret. Roles get built when a second person exists.
- Anything upstream of this page's own boundary — BL-12 (perimeter) and BL-14 (what gets written) are consumed here, not re-implemented.
- Any public-facing feature.

## Success criterion

Viewing the dashboard's default screen never touches the AES key (verified by code path, not by intent). Decrypting one IP is a logged, single-row action. An attacker who obtains valid session cookies but not the TOTP secret cannot complete a login. The audit log survives an attempt by the admin account itself to delete or alter it (verified by testing the DB grants, not by trusting the application code not to try).
