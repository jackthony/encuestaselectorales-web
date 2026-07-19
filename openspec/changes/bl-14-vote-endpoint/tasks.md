# BL-14 — Tasks

Logic-bearing per `CLAUDE.md` — failing tests first, for the two things most
likely to regress silently: the rate limit and the trust boundary around
client input. Sequence is binding.

## 1. Failing tests (before the endpoint exists)
- [ ] 1.1 `scripts/test-rate-limit.php`: seed `votos_interactivos` (against a test DB, BL-13's schema) with N votes sharing one `ip_hash` inside the last hour; assert that a rate-limit check function rejects the `N+1`th and accepts requests from a different `ip_hash`. Function doesn't exist yet — **confirm this fails**.
- [ ] 1.2 `scripts/test-client-trust.php`: assert that a payload with a forged/absent `device_token` cookie and a fresh random `browser_fingerprint` still gets rate-limited by IP (i.e. the endpoint doesn't grant a free pass just because the device signals look "new") — the exact attack in `design.md`. **Confirm this fails** against nothing (no endpoint yet).
- [ ] 1.3 `scripts/test-district-whitelist.php`: assert a payload with `ubigeo_votacion` not present in `data/distrito.json` is rejected before any DB write. **Confirm this fails.**
- [ ] 1.4 `scripts/test-trust-score-not-exposed.php`: assert the endpoint's response body, on both success and error paths, never contains a `trust_score` key. **Confirm this fails** (endpoint doesn't exist, so trivially "fails" — the point is this test exists and runs red before green is possible).
- [ ] 1.5 Commit all four failing tests together.

## 2. `/api/votar.php` skeleton
- [ ] 2.1 `header('Content-Type: application/json')`; reject non-`POST` with `405`, explicit status code (draft's `die(json_encode(...))` with no status set is the bug being fixed).
- [ ] 2.2 Reject before parsing: rate limit via `includes/trusted-ip.php` (BL-12) resolving the real client IP, hashed with the project's HMAC salt, queried against `idx_ratelimit_ip`. `429` above threshold. Threshold lives in `/config/` as a named constant, not a magic number in the endpoint — it will be tuned against real traffic.
- [ ] 2.3 Parse `php://input` as JSON. Malformed JSON → `400`.
- [ ] 2.4 Validate every field with `filter_var()` per `docs/engineering-standards.md` §3: `gps_lat`/`gps_lng` numeric range (`isset()`, not `empty()` — `empty()` is true for `0.0`, a real latitude), `interaction_time_ms` positive int, `candidato_id` int or absent depending on `tipo_voto`.
- [ ] 2.5 **GPS presence check uses `isset()`, not `empty()`.** Missing → `400` before any further processing.
- [ ] 2.6 `ubigeo_votacion` checked against `data/distrito.json`'s id list (loaded via `includes/data.php`, BL-10). Not in the list → `400`.
- [ ] 2.7 `tipo_voto` determines whether `candidato_id` is required; a `candidato_id` cast with `(int)` on a `null` silently becomes `0` — validate explicitly instead of casting-and-hoping (the draft's bug).

## 3. Crypto
- [ ] 3.1 `ip_hash = hash_hmac('sha256', $ip, $ip_salt)` — HMAC, not `hash('sha256', $ip . $salt)`. The salt is 32+ random bytes from `/config/`, not a short string.
- [ ] 3.2 AES-256-GCM: `openssl_encrypt($ip, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag)`. Store ciphertext, `$iv` and `$tag` in the three separate columns BL-13 created.
- [ ] 3.3 Key: 32 raw bytes read from a keyfile path defined in `/config/` (outside `public_html/`), generated once via `random_bytes(32)`, never a human-typed string.
- [ ] 3.4 Local-dev fallback: `HTTP_CF_CONNECTING_IP` is absent locally. Fallback to `REMOTE_ADDR` **only** when an explicit `LOCAL_DEV` flag is set in `/config/` — never a bare `??`, which in production would let an attacker set `CF-Connecting-IP` directly (BL-12's origin lock is the primary defense; this is defense in depth on the application side).

## 4. trust_score
- [ ] 4.1 Compute from: GPS falls within the claimed district's approximate bounds; `CF-IPCountry === 'PE'` (from BL-12, `NULL`-safe until BL-12 is live); `interaction_time_ms` within a plausible band (reject sub-200ms as certainly-scripted, don't reward extremely slow either); `gps_accuracy_meters` under a sane ceiling; no burst from this `ip_hash` in the last N minutes beyond the rate-limit threshold itself.
- [ ] 4.2 Exact weights are tunable and not spec-locked — write them as named constants in `/config/`, document the reasoning for each in a code comment, expect them to change once real data exists.
- [ ] 4.3 Write to `trust_score` column. **Grep the entire file after writing the response-construction code**: `trust_score` must not appear in any `json_encode()` call. This is the task 1.4 test's assertion, made true.

## 5. Vote insert
- [ ] 5.1 Prepared statement, all fields bound — no string concatenation anywhere in this file.
- [ ] 5.2 Cryptographic PK: `bin2hex(random_bytes(16))`.
- [ ] 5.3 `device_token`: read from cookie if present, else generate; `setcookie(..., ['httponly' => true, 'secure' => true, 'samesite' => 'Strict'])` — the draft's `setcookie` with positional args and no `SameSite` is the bug being fixed.
- [ ] 5.4 On unique-constraint or other `PDOException`, respond with the appropriate status (not a blanket 500 hiding a 429-worthy duplicate) and a message that doesn't leak schema detail.
- [ ] 5.5 Success response: `{"status": "success", "message": "..."}`. No `trust_score`, no internal IDs beyond what the client needs to reference its own vote if anything.

## 6. Green
- [ ] 6.1 All four task-1 tests pass against the real endpoint.
- [ ] 6.2 Manual `curl` replay of the attack in `design.md` (fresh fingerprint per request, no cookie, hand-typed coordinates, looped) — confirm it gets rate-limited, not silently accepted, within the configured threshold.
- [ ] 6.3 `scripts/check-refactor.php` and all `validate-*.js` still green (this item shouldn't touch anything they cover, but confirm).
- [ ] 6.4 Update `docs/backlog.md`: BL-14 → `done`.

## Out of scope — do not touch
- `/admin/`, decryption UI — BL-15.
- The GPS modal's client-side UX — BL-11 already built it; this item is only what it calls.
- Tuning the rate-limit threshold or `trust_score` weights against real production traffic — that happens after real traffic exists, not now.

## Known unknown to report, not solve
Same PHP-version and MySQL-plan unknowns flagged in BL-12/BL-13 apply here directly — this is the item that actually needs both live. Confirm before deploying, not after.
