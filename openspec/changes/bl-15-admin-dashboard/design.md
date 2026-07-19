# BL-15 — Design

## What is actually behind this login

Every other page on the site is public. This one is not, and the asymmetry matters more than it looks:

- **Decrypted voter IPs.** BL-14 stores them AES-256-GCM encrypted precisely so they are not readable from a DB dump. The admin dashboard is where the key gets used. A compromised admin session undoes the encryption for the entire dataset at once.
- **Exact GPS coordinates** of every voter, plotted on a heatmap. A coordinate is a home address, a workplace, a route.
- **Political preference joined to both.** Who voted for whom, from where.

That combination — political opinion tied to location and network identity, for thousands of Peruvians — is the most sensitive thing this project will ever hold. Under Ley 29733 political opinion is sensitive personal data. A breach here is not "a website got hacked"; it is a political targeting list, published, in an election year, from a platform whose sole asset is neutrality.

The security bar for this page is therefore not "the same as the rest of the site". It is higher than everything else combined, and it is a single operator's login.

## Authentication

- `password_hash()` with `PASSWORD_ARGON2ID` (PHP 8 default params). Never MD5/SHA for passwords — they are built to be fast, which is the opposite of what a password hash needs.
- **TOTP second factor, mandatory.** One password is the only thing between the public internet and the dataset above. TOTP is ~40 lines with no dependency and no SMS. Given what it protects, single-factor is not defensible.
- Login rate limit: exponential backoff per username **and** per IP. Both, because either alone is bypassable.
- Generic failure message. "Usuario no existe" enumerates valid accounts.
- Constant-time comparison for tokens (`hash_equals`).

## Session

```
session.cookie_httponly = 1
session.cookie_secure   = 1
session.cookie_samesite = Strict
session.use_strict_mode = 1
```

`session_regenerate_id(true)` on privilege change — without it, an attacker who sets a session ID before login owns the session after it (session fixation). Idle timeout 30 min, absolute timeout 8 h.

## Key access — the decision that limits blast radius

The AES key decrypts every stored IP. If the admin dashboard holds it in a variable on every page load, one file-read vulnerability anywhere in `admin/` leaks the whole dataset.

**Decryption is not the default view.** The dashboard shows aggregates — counts, percentages, heatmap density — computed without touching the key. Decrypting a specific IP is a separate, explicit action, one row at a time, and every use is written to an audit log (who, when, which vote id, why).

This is not ceremony. It is the difference between "an operator looked up one suspicious vote" and "someone exported 40,000 identities", and after an incident it is the only evidence that distinguishes them.

## Audit log

Append-only table: admin id, action, target row, timestamp, source IP. Covers logins (success and failure), decryption events, and any export. A log the admin can silently delete is not a log — the DB user for `admin/` gets `INSERT` and `SELECT` on it, never `DELETE` or `UPDATE`.

## Placement

`/admin/` sits inside `public_html/` (shared hosting, no choice). Therefore:

- `.htaccess` denies direct access to everything except the front controller.
- No directory listing.
- `noindex` header and a `robots.txt` disallow — a public search hit on this path is free reconnaissance.
- The config holding the AES key stays outside `public_html/`, as `CLAUDE.md` requires. `admin/` reads it; it never lives under a web-servable path.

## Export

CSV export is the single highest-risk feature. If it ships:

- Aggregates only by default; raw rows require a second confirmation and are audit-logged.
- No GPS coordinates and no decrypted IPs in any file that leaves the server, unless explicitly requested per-row.

An export that lands in an email attachment or a WhatsApp thread has left every control this document describes.

## Out of scope

Multi-user accounts, roles, permissions. There is one operator (`BL-01` hardening is explicitly solo-operator). One account, one TOTP secret. Roles get built when a second person exists, not before.
