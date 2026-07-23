# BL-14 — Design

## The core problem: every antifraud signal is client-supplied

`docs/reference/backend-controller-draft.php` treats three values as trust anchors:

```php
$fingerprint   = $data['fingerprint'];                                   // from the browser
$device_token  = $_COOKIE['device_token'] ?? bin2hex(random_bytes(32));  // from the browser
$gps_lat       = (float)$data['gps_lat'];                                // from the browser
```

All three are attacker-controlled. The whole "bóveda blindada" collapses to:

```bash
for i in $(seq 1 10000); do
  curl -X POST https://encuestaselectorales.pe/api/votar.php \
    -H 'Content-Type: application/json' \
    -d "{\"fingerprint\":\"$(openssl rand -hex 32)\",\"gps_lat\":-12.12,\"gps_lng\":-77.03,...}"
done
```

Fresh fingerprint per request, no cookie sent, coordinates typed by hand. Every UNIQUE KEY is satisfied. The `Access-Control-Allow-Origin` header does not help — CORS is enforced by browsers and `curl` ignores it entirely.

**Design principle, binding:** client-supplied values are *evidence*, never *gates*. The only gate the server actually controls is rate limiting keyed on the connection, enforced before anything else runs.

## Defense layers, in order of reliability

| Layer | Attacker-controllable? | Role |
|---|---|---|
| Cloudflare WAF + Bot Fight (BL-12) | No | first filter, drops the obvious floods |
| Server-side rate limit on `ip_hash` | Only by renting IPs | **the real gate** |
| `device_token` set as `HttpOnly` cookie | Yes — clearable | raises cost, counts as a signal |
| `browser_fingerprint` | Yes — forgeable | signal only, never a constraint |
| GPS coordinates | Yes — typeable | signal only, cross-checked against `CF-IPCountry` |
| `interaction_time_ms` | Yes — fakeable | signal; sub-second submissions are cheap to flag |

Nothing below row 2 may ever block or uniquely key a vote on its own.

## Rate limit

Before any parsing or DB write:

```
votes from this ip_hash for this encuesta_id in the last hour > N  ->  429
```

`N = 5`. Chosen to survive CGNAT: a household or a shared carrier IP legitimately produces a handful of votes; it does not produce hundreds. This is a **tunable knob, not a constant** — real traffic will move it, and the value lives in `/config/` so it changes without a deploy.

## trust_score

Computed server-side, `0-100`, written to the DB, **never returned in any response**. Returning it hands the attacker a scoring oracle: submit, read the score, adjust, repeat until 100. It is readable only behind the BL-15 admin wall.

Inputs: GPS present and inside the claimed district; `CF-IPCountry` == `PE`; `interaction_time_ms` within a plausible human band; `gps_accuracy_meters` under a sane ceiling; no recent burst from the same `ip_hash`. Exact weights are BL-14 implementation detail and are expected to be tuned against real data — the spec fixes the inputs and the range, not the formula.

## Crypto

- **Hash** (dedup): `hash_hmac('sha256', $ip, $ip_salt)`. HMAC, not plain `sha256($ip . $salt)` — IPv4 has only ~4 billion values, so an unsalted or naively-salted digest is brute-forceable in minutes. The salt lives in `/config/`, outside `public_html/`.
- **Encryption** (reversible, admin only): `AES-256-GCM`. CBC has no integrity tag — anyone who can write to the DB can alter ciphertext undetected. GCM authenticates. Store `ciphertext`, `iv` and `tag`; a missing tag means the row was tampered with.
- **Key**: 32 raw bytes from a keyfile outside `public_html/`. The draft's `$secret_key = 'TU_LLAVE_MAESTRA_AES_256'` is a 23-character ASCII string that OpenSSL null-pads to 32 bytes — roughly 8 bits of real entropy.

## Other corrections from the draft

| Draft | Fix |
|---|---|
| Credentials hardcoded in a `.php` inside `public_html/` | `/config/db.php` outside the web root, gitignored |
| `die(json_encode(...))` with no status code | every error path sets an explicit HTTP status; a 200 carrying `{"error": ...}` is unparseable for the client |
| `empty($data['gps_lat'])` | `empty()` is true for `0`/`0.0`; use `isset()` + range validation. GPS is mandatory (BL-13) so a missing coordinate is a `400`, but the *check* for its presence must not itself reject latitude 0 |
| `$ubigeo_votacion` unvalidated | whitelist against `data/distrito.json` — prepared statements stop SQL injection, not garbage data |
| `$candidato_id = (int)$data[...]` | `(int)null` is `0`, not `NULL` — blank/spoiled votes need `tipo_voto`, not a cast |
| `setcookie` without `SameSite` | `SameSite=Strict`, `Secure`, `HttpOnly` |
| CORS header treated as protection | it is not; the rate limit is the protection |

## Local development

`HTTP_CF_CONNECTING_IP` does not exist off Cloudflare. Fallback to `REMOTE_ADDR` **only when an explicit local-environment flag is set in `/config/`** — never by bare `??`. In production a bare fallback lets an attacker spoof `CF-Connecting-IP` directly and forge any source IP they want.
