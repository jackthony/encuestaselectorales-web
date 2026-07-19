# BL-12 — Cloudflare WAF & Security Perimeter

## Why

DNS today points at Hostinger's own nameservers (`aurora.dns-parking.com` / `nebula.dns-parking.com`), no proxy in front of anything. `HTTP_CF_CONNECTING_IP` and `HTTP_CF_IPCOUNTRY` — which BL-14's rate limiting and `trust_score` both depend on — do not exist yet; any code reading them today reads `null`.

This matters now, before BL-14 writes a line of endpoint code, because of an ordering trap: if `/api/votar.php` ships before the origin is locked to Cloudflare-only traffic, an attacker who finds the origin IP (trivial — historical DNS, certificate transparency logs) talks to it directly. Every WAF rule, rate limit and bot check Cloudflare would apply simply never runs, and worse, the PHP ends up trusting a `CF-Connecting-IP` header the attacker forged themselves, because nothing stripped it. The backlog already sequences BL-12 before BL-14; this proposal is what makes that sequencing real rather than nominal.

## What changes

1. DNS moved to Cloudflare, proxied (orange cloud) on apex + `www`.
2. SSL/TLS mode set to **Full (strict)** — not Flexible, which leaves the Cloudflare→origin hop unencrypted.
3. Bot Fight Mode enabled.
4. An edge rate-limiting rule on `/api/*` (exact threshold owned by BL-14, this item only wires the mechanism).
5. **Origin lock**: Hostinger firewall or `.htaccess` allow-lists Cloudflare's published IP ranges, refused otherwise.
6. **Header trust gate**: a PHP helper that trusts `CF-Connecting-IP` only when `REMOTE_ADDR` itself is inside a Cloudflare range; falls back to `REMOTE_ADDR` and flags the request otherwise. This exists even though `/api/` doesn't yet — BL-14 consumes it, doesn't build it.

## Explicitly out of scope

- The actual rate-limit threshold and `trust_score` formula — BL-14.
- `/admin/` hardening — BL-15.
- Any DB or `/api/` endpoint code.

## Success criterion

A request sent directly to the origin's IP (bypassing Cloudflare, `Host` header set manually) is refused. A forged `CF-Connecting-IP` sent through Cloudflare does not survive to PHP. A forged `CF-Connecting-IP` sent directly to the origin is either blocked by the origin lock or ignored by the PHP trust gate if it somehow lands. Each of these is a `curl` command someone ran and watched fail correctly — not a setting someone toggled and assumed works.
