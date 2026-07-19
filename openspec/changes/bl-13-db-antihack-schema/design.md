# BL-13 — Design

## Reference draft

`docs/reference/db-schema-draft.sql` is the starting point, not the target. It has two defects that would silently destroy the product. Both are fixed here.

## Defect 1 — UNIQUE KEY on `ip_hash` kills mobile traffic

The draft declares:

```sql
UNIQUE KEY uniq_vote_ip (encuesta_id, ubigeo_votacion, ip_hash)
```

Peruvian mobile carriers (Claro, Movistar, Entel) run **CGNAT** — thousands of subscribers share one public IPv4. This constraint permits **one vote per carrier, per district, per poll**. The second Claro user in Miraflores gets a duplicate-key error. Mobile is the majority of the traffic this product is built to capture, so the antifraud measure destroys the dataset it protects.

Same reasoning for `UNIQUE KEY uniq_vote_device (…, browser_fingerprint)`: fingerprints collide across identical devices (same phone model, same browser build, same settings), so it blocks legitimate distinct voters.

**Resolution.** Neither is a uniqueness constraint. Both become plain indexes supporting server-side rate limiting:

```sql
KEY idx_ratelimit_ip     (encuesta_id, ip_hash, created_at),
KEY idx_ratelimit_device (encuesta_id, device_token, created_at)
```

Rate limiting is a *query* ("how many votes from this hash in the last hour"), not a *constraint*. A query can encode "3 per hour is fine, 300 is not". A UNIQUE KEY can only encode "1, ever".

## Defect 2 — `gps_lng DECIMAL(10,8)` overflows

`DECIMAL(10,8)` allows 2 integer digits: max `±99.99999999`.

- `gps_lat` spans ±90 → fits exactly.
- `gps_lng` spans ±180 → **does not fit**.

Peru sits between roughly -68° and -81°, so domestic votes fit by luck. Peruvians abroad do vote, and the US spans -74° to -122°. In MySQL strict mode that row errors; without strict mode it is **silently clamped to -99.99999999** — a coordinate in the Pacific, written to the DB as if valid.

**Resolution.** `gps_lat DECIMAL(10,8)`, `gps_lng DECIMAL(11,8)`.

## Decision — GPS is mandatory

`gps_lat` and `gps_lng` are `NOT NULL`. No vote is accepted without coordinates. Owner decision, 2026-07-18, taken with the tradeoff on the table: browser geolocation prompts on political sites are denied by most users, so total vote volume will be a fraction of visitors, and the sample skews toward people comfortable sharing location.

What the project buys for that: every stored vote is geo-verified. There is no second-class tier to explain, no "declared" number a critic can attack, and the methodology page can state one unqualified claim — *every vote in this poll was cast from a verified physical location*. For a product whose entire position is trust over volume, one defensible number beats two qualified ones.

Consequences that follow from this and are binding downstream:

- `/api/votar.php` rejects a payload without coordinates with `400`, before rate limiting or any DB write (BL-14).
- The GPS widget's denial path is a **product-critical screen**, not an error case. The current `alert("Permiso denegado")` in the prototype is the single highest-leverage UX surface on the site — it is where most visitors stop. BL-11 owns rewriting it: explain why location is required, what is stored, and offer a retry. It never gets shipped as an `alert()`.
- No `vote_tier` column. There is one kind of vote.

**Measuring the cost costs nothing.** Denial rate = `1 − (votes ÷ district-page views)`, and BL-21 analytics already provides the denominator. No counter, no extra endpoint, no new table. If the real ratio turns out worse than the business can absorb, that is the moment to revisit — with data, not with a guess.

## Decision — cryptographic PK

`id CHAR(32)` from `random_bytes(16)`, per the anti-IDOR rule in `CLAUDE.md`. Random primary keys cause InnoDB page splits and inflate every secondary index by the PK width. At this scale (thousands of rows) the cost is not measurable. Not optimizing. If it ever matters, `BINARY(16)` halves the index footprint.

## Decision — annul, never delete

`estado ENUM('valido','sospechoso','anulado') NOT NULL DEFAULT 'valido'`. Fraud found after the fact gets marked, not deleted. A polling product that silently deletes rows cannot defend its own numbers under scrutiny.

## Other corrections from the draft

| Draft | Fix | Why |
|---|---|---|
| `user_agent VARCHAR(255)` | `VARCHAR(512)` | real UA strings exceed 255 and truncate |
| `candidato_id INT NULL` for blank/spoiled | explicit `tipo_voto ENUM('candidato','blanco','viciado')` | `NULL` collapses two distinct outcomes that get reported separately |
| `cf_pais CHAR(2)` | keep, nullable | depends on Cloudflare (BL-12). Until BL-12 ships it is always NULL — that is expected, not a bug |
| no `trust_score` column | `trust_score TINYINT UNSIGNED NULL` | BL-14 computes it; it is written here and never returned to a client |
