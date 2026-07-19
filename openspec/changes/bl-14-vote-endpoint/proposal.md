# BL-14 — IP Traceability & Geolocation Logic (PHP)

## Why

`docs/reference/backend-controller-draft.php` treats three client-supplied values — `fingerprint`, `device_token`, `gps_lat`/`gps_lng` — as trust anchors. All three are forgeable with a single `curl` call: a fresh random fingerprint, no cookie sent, typed-in coordinates. Combined with BL-13's `UNIQUE KEY` defect (already fixed at the schema level), the draft's entire antifraud design collapses to "send a random string, get a vote counted." `design.md` documents the full attack and the fix.

This item is the one place in the backlog where "no one trusts the client" has to be enforced in actual code, not just stated as a principle. Everything downstream — the public trust the product sells, the `trust_score` BL-15 displays, the legitimacy of any published result — depends on this endpoint refusing to be gamed by anyone who reads its JavaScript.

## What changes

`/api/votar.php`, built from the corrected design:

1. **Rate limit first, before any parsing.** Query `votos_interactivos` by `ip_hash` (via BL-12's `includes/trusted-ip.php`) for this `encuesta_id` in the last hour; reject with `429` above threshold, before touching the request body.
2. **GPS mandatory, `400` if absent** — per BL-13's schema, enforced at the API boundary too, not just the DB constraint.
3. **`ubigeo_votacion` whitelisted** against `data/distrito.json` — prepared statements stop injection, not garbage district codes.
4. **`trust_score` computed server-side from signals the client cannot directly set**: GPS-inside-claimed-district, `CF-IPCountry == PE`, `interaction_time_ms` in a plausible human band, `gps_accuracy_meters` under a ceiling, absence of a recent burst from this `ip_hash`. Written to the DB. **Never returned in the response.**
5. **AES-256-GCM** for the reversible IP encryption, replacing the draft's CBC. **HMAC-SHA256** for the dedup hash, replacing the draft's plain `sha256($ip . $salt)`.
6. **Real key management**: 32 raw bytes from a keyfile outside `public_html/`, not a hardcoded ASCII placeholder.
7. **Cookie hardening**: `device_token` set `HttpOnly; Secure; SameSite=Strict`.
8. **Local-dev IP fallback gated behind an explicit flag**, not a bare `??` that lets `CF-Connecting-IP` be forged directly in production.

## Explicitly out of scope

- The schema itself — BL-13, this item only writes to it.
- Cloudflare configuration — BL-12, this item only consumes `includes/trusted-ip.php`.
- Anything the admin dashboard reads or decrypts — BL-15.
- The GPS recovery modal's UX — BL-11; this item is what the modal's successful path ultimately calls.

## Success criterion

The `curl`-loop attack described in `design.md` — fresh fingerprint, no cookie, hand-typed coordinates, repeated — is stopped by the rate limit within the stated threshold, not by any client-supplied value. A payload with a district code not in `data/distrito.json` is rejected. `trust_score` never appears in any HTTP response body, verified by grep across the whole endpoint's response-construction code, not by inspection of one code path.
