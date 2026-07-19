# BL-15 — Tasks

Auth and the audit-log grants are logic-bearing — test-first applies to both.

## 1. Failing tests (before the dashboard exists)
- [ ] 1.1 `scripts/test-auth.php`: assert a login with correct password but wrong/missing TOTP code is rejected; assert `N+1`th failed attempt within the backoff window is rejected regardless of correctness (rate limit applies even to a correct password after too many wrong ones). **Confirm fails** — nothing exists yet.
- [ ] 1.2 `scripts/test-audit-immutable.php`: using the `/admin/` DB user's actual grants (not a superuser), attempt `DELETE` and `UPDATE` against the audit log table; assert both are rejected by MySQL itself, not by application logic. **Confirm fails** — table doesn't exist yet.
- [ ] 1.3 `scripts/test-default-view-no-decrypt.php`: assert that rendering the dashboard's default (aggregate) view executes zero calls to the decryption function. **Confirm fails.**
- [ ] 1.4 Commit all three failing tests.

## 2. Schema additions
- [ ] 2.1 `db/migrations/002_create_admin_auth.sql`: single admin account table (`username`, `password_hash`, `totp_secret`, `created_at`) — one row, per the solo-operator scope.
- [ ] 2.2 `db/migrations/003_create_audit_log.sql`: append-only table (`id`, `admin_id`, `action`, `target_vote_id` nullable, `detail`, `created_at`, `source_ip`).
- [ ] 2.3 Create a separate MySQL user for `/admin/`'s DB connection with `INSERT, SELECT` only on the audit table, and `SELECT` (plus whatever aggregate queries need) on `votos_interactivos` — no `DELETE`/`UPDATE` on either from this user. This is what task 1.2 tests against.

## 3. Authentication
- [ ] 3.1 `password_hash($pw, PASSWORD_ARGON2ID)` at account creation; `password_verify()` at login.
- [ ] 3.2 TOTP: generate secret at setup (displayed once, as a QR code, for the operator's authenticator app), verify a 6-digit code with a small time-window tolerance. No external dependency required — TOTP is HMAC-SHA1 over a time counter, implementable directly.
- [ ] 3.3 Exponential backoff keyed on username AND on source IP independently — both must be checked, either alone is bypassable (rotate IP, or hammer a different username).
- [ ] 3.4 Generic failure message ("credenciales incorrectas") regardless of whether the username, password, or TOTP code was wrong.
- [ ] 3.5 `hash_equals()` for any token/secret comparison — never `===` on secret material (timing attack surface).
- [ ] 3.6 `session_regenerate_id(true)` immediately after successful auth.

## 4. Session hardening
- [ ] 4.1 `session.cookie_httponly = 1`, `session.cookie_secure = 1`, `session.cookie_samesite = Strict`, `session.use_strict_mode = 1` — set in `/admin/`'s bootstrap, not relying on php.ini defaults.
- [ ] 4.2 Idle timeout 30 minutes, absolute timeout 8 hours, both enforced server-side (a stored `last_activity` and `session_started` timestamp, checked on every request).

## 5. Dashboard — aggregates only by default
- [ ] 5.1 Default view: counts, percentages, heatmap density (`ubigeo_votacion` + rounded/binned `gps_lat`/`gps_lng`, not raw per-vote coordinates) — all computed via `SELECT`/`GROUP BY`, zero calls to the AES decryption function anywhere in this code path.
- [ ] 5.2 Heatmap binning: round coordinates to a grid coarse enough that a single voter's exact address isn't reconstructable from the aggregate view alone (e.g. ~100m cells) — the default view protects location precision, not just the identity behind it.

## 6. Per-row decryption — explicit, logged
- [ ] 6.1 Separate action, one vote ID at a time: decrypt `ip_cifrada` using `ip_iv`/`ip_tag`, verify the GCM tag (a failed tag verification means tampering — surface this loudly, don't silently fall back).
- [ ] 6.2 Every decryption call writes an audit log row: admin id, `action = 'decrypt_ip'`, `target_vote_id`, timestamp, source IP, before returning the result to the admin.
- [ ] 6.3 No bulk decrypt endpoint. If investigating a pattern requires looking at many rows, that's many individual audited actions, not one.

## 7. `.htaccess` for `/admin/`
- [ ] 7.1 Deny direct access to everything except the front controller.
- [ ] 7.2 `Options -Indexes`.
- [ ] 7.3 `X-Robots-Tag: noindex` header; `robots.txt` disallow for `/admin/`.
- [ ] 7.4 Confirm this doesn't conflict with BL-10's deploy-readiness `.htaccess` or BL-12's origin-lock rules in the same file — append, don't overwrite.

## 8. Export (only if it ships in this pass — otherwise defer explicitly)
- [ ] 8.1 Aggregate CSV export requires no extra confirmation.
- [ ] 8.2 Raw-row export requires a second explicit confirmation step and is audit-logged (`action = 'export_raw'`, row count, timestamp).
- [ ] 8.3 No GPS coordinates or decrypted IPs in any exported file unless the export was the explicit per-row decrypt-and-export path from section 6 — never bundled into a bulk file.

## 9. Green
- [ ] 9.1 All three task-1 tests pass.
- [ ] 9.2 Manual check: valid password + wrong TOTP → rejected. Valid password + valid TOTP → in.
- [ ] 9.3 Manual check: as the `/admin/` DB user directly (not through the app), attempt to `DELETE FROM audit_log` — confirm MySQL itself refuses it.
- [ ] 9.4 Update `docs/backlog.md`: BL-15 → `done`.

## Out of scope — do not touch
- Anything public-facing.
- Multi-operator roles — single account only, per scope.
- Real-time alerting on suspicious patterns — that's a future item if it's ever needed, not part of this dashboard's first version.
