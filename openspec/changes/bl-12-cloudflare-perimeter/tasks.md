# BL-12 — Tasks

## 1. DNS cutover
- [ ] 1.1 Add the domain to Cloudflare, verify the automatically-imported DNS records match what's currently live on Hostinger's nameservers (A/AAAA/MX/TXT — nothing dropped).
- [ ] 1.2 Set apex and `www` to proxied (orange cloud).
- [ ] 1.3 Update nameservers at GoDaddy (currently `aurora.dns-parking.com` / `nebula.dns-parking.com`, per `CLAUDE.local.md`) to Cloudflare's assigned pair.
- [ ] 1.4 Wait for propagation (`nslookup encuestaselectorales.pe` — same check already used for the prior cutover). Do not proceed to section 2 until Cloudflare is confirmed authoritative.

## 2. TLS
- [ ] 2.1 SSL/TLS mode → **Full (strict)**. Confirm Hostinger's origin certificate is valid first — strict mode fails closed if it isn't, which is correct behavior but needs to be expected, not discovered as an outage.
- [ ] 2.2 Always Use HTTPS → on.
- [ ] 2.3 Minimum TLS version → 1.2.

## 3. Bot / rate perimeter
- [ ] 3.1 Bot Fight Mode → on.
- [ ] 3.2 Create (but do not finalize the threshold of) a rate-limiting rule scoped to `/api/*`. Leave a placeholder threshold with a comment pointing to BL-14, which owns the number — `/api/` does not exist yet at this point in the backlog.

## 4. Origin lock
- [ ] 4.1 Fetch Cloudflare's current published IP ranges (`https://www.cloudflare.com/ips-v4` / `-v6`).
- [ ] 4.2 Apply as an allow-list. Prefer Hostinger's firewall panel if the plan exposes one; otherwise `.htaccess` `Require ip` directives (coordinate with whatever `.htaccess` BL-10's deploy-readiness work already created — don't overwrite its `DirectoryIndex`/`Options -Indexes` rules, append to them).
- [ ] 4.3 Document where the range list lives and that it needs periodic refresh — it is not append-once. A comment with the fetch URL and today's date is enough; no automation required for MVP.
- [ ] 4.4 **Verify**: `curl` the origin's IP directly (not the domain) with a `Host:` header for the domain. Confirm it is refused, not silently served.

## 5. Header trust gate
- [ ] 5.1 `includes/trusted-ip.php`: a function that returns `REMOTE_ADDR` unless `REMOTE_ADDR` falls inside the Cloudflare range list from task 4.1, in which case it returns `CF-Connecting-IP` instead.
- [ ] 5.2 Reuse the same range list task 4 fetched — do not maintain two copies that can drift.
- [ ] 5.3 This file is committed now, unused (`/api/` doesn't exist yet). BL-14 imports it rather than re-implementing the check — note that explicitly in this file's own docblock so it isn't rediscovered from scratch.
- [ ] 5.4 **Verify**: simulate a request with `REMOTE_ADDR` outside Cloudflare's ranges and a forged `CF-Connecting-IP` header — confirm the function returns `REMOTE_ADDR`, not the forged value. A unit-style script under `scripts/` is enough; no test framework needed for one function.

## 6. Country signal plumbing
- [ ] 6.1 Confirm `HTTP_CF_IPCOUNTRY` is present on a real request once DNS has cut over. No logic consumes it yet (BL-14 does) — this task is verification only.

## 7. Verify end to end
- [ ] 7.1 Normal browser request to the domain: succeeds, TLS padlock valid, `CF-IPCountry` present.
- [ ] 7.2 `curl` directly to the origin IP: refused.
- [ ] 7.3 `curl` through Cloudflare with a forged `CF-Connecting-IP` header: does not survive to the origin (Cloudflare overwrites it before the origin ever sees it).
- [ ] 7.4 Update `docs/backlog.md`: BL-12 → `done`. Note that the `/api/*` rate-limit threshold and header-trust-gate consumption are intentionally deferred to BL-14, not forgotten.

## Out of scope — do not touch
- `/api/`, DB, `trust_score` — BL-14.
- `/admin/` — BL-15.
- `.htaccess` rules unrelated to the origin lock (DirectoryIndex, directory listing) — BL-10's deploy-readiness work owns those; this item only appends to the same file.
