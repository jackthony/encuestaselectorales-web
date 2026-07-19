# BL-12 — Design

## Starting position

DNS currently points at Hostinger's own nameservers (`aurora.dns-parking.com` / `nebula.dns-parking.com`, per `CLAUDE.local.md`). There is no Cloudflare in front of anything today. `HTTP_CF_CONNECTING_IP` and `HTTP_CF_IPCOUNTRY` do not exist yet, so any code reading them reads `null`. This item creates that layer.

## The mistake that makes the whole perimeter decorative

Putting Cloudflare in front of Hostinger does **not** stop anyone from talking to Hostinger directly. The origin keeps its own IP, and that IP is trivially discoverable — historical DNS records, certificate transparency logs, a stray `MX` record. An attacker who finds it sends votes straight to the origin and every WAF rule, every rate limit and every bot check simply never runs.

Worse, in that scenario the PHP reads a **client-supplied** `CF-Connecting-IP` header, because the request did not pass through Cloudflare and nothing stripped it. The attacker forges any source IP they like, which defeats the one gate BL-14 actually relies on.

**The perimeter is only real if the origin refuses non-Cloudflare traffic.** Two mechanisms, both required:

1. **Origin lock.** Allow inbound HTTP only from Cloudflare's published IP ranges — via Hostinger firewall if available, otherwise `.htaccess`. Cloudflare publishes the ranges and they change; the list needs a refresh, not a one-time paste.
2. **Header trust gate in PHP.** Trust `CF-Connecting-IP` **only** when `REMOTE_ADDR` is inside a Cloudflare range. Otherwise fall back to `REMOTE_ADDR` and mark the request suspicious. This is defense in depth: if the origin lock is ever misconfigured, the application still does not accept a forged IP.

Without both, everything else in this item is theater.

## Cloudflare configuration

| Setting | Value | Why |
|---|---|---|
| DNS | proxied (orange cloud) on the apex + `www` | grey cloud = DNS only = no protection at all |
| SSL/TLS mode | **Full (strict)** | "Flexible" leaves Cloudflare→origin unencrypted and makes the padlock a lie |
| Bot Fight Mode | on | first coarse filter |
| Rate limiting rule | `/api/*` — see BL-14 for the threshold | edge-level, before PHP boots |
| Always Use HTTPS | on | |
| Min TLS | 1.2 | |

## Country signal

`CF-IPCountry` becomes available and feeds `trust_score` (BL-14). It is a **signal, not a gate**. Peruvians abroad legitimately vote, and a VPN forges the country trivially. A non-`PE` country lowers the score; it never rejects the vote on its own.

## Rate limit placement

Cloudflare's rule and BL-14's PHP rate limit are not redundant. The edge rule absorbs volume before it costs a PHP process or a DB connection on shared hosting. The PHP rate limit is the one that survives a Cloudflare misconfiguration or a bypass. Both ship.

## Ordering constraint

BL-12 must be live before BL-14 exposes `/api/votar.php`. The backlog already sequences it that way. If the endpoint ships first, it runs with the header trust gate reading nothing and the origin fully exposed.

## Verification

Not "the site loads". The specific things to prove:

- `curl` directly to the origin IP with a `Host:` header for the domain → connection refused or 403.
- A forged `CF-Connecting-IP` sent through Cloudflare → does not survive to PHP (Cloudflare overwrites it).
- A forged `CF-Connecting-IP` sent directly to the origin → rejected by the origin lock; if it somehow lands, the PHP trust gate ignores it.
- `CF-IPCountry` present and correct on a normal request.

Each of these is a command someone can run, not a checkbox someone can tick.
