# cloudflare-perimeter

## ADDED Requirements

### Requirement: Origin refuses non-Cloudflare traffic
The Hostinger origin SHALL refuse HTTP requests whose source IP is not within Cloudflare's published ranges.

#### Scenario: Direct request to the origin is refused
- **WHEN** a request is sent directly to the origin's IP address with a `Host` header set to the site's domain
- **THEN** the origin refuses or does not serve the request

#### Scenario: A request through Cloudflare is served normally
- **WHEN** a request reaches the origin via Cloudflare's proxy
- **THEN** it is served normally

### Requirement: Client-supplied IP headers are never trusted directly
Application code SHALL determine the client's IP via a single shared helper that validates `REMOTE_ADDR` against Cloudflare's IP ranges before trusting `CF-Connecting-IP`.

#### Scenario: Forged header from outside Cloudflare's ranges is ignored
- **WHEN** a request's `REMOTE_ADDR` is not within Cloudflare's published ranges, regardless of what `CF-Connecting-IP` claims
- **THEN** the shared IP-resolution helper returns `REMOTE_ADDR`, not the `CF-Connecting-IP` value

#### Scenario: Legitimate Cloudflare-proxied request resolves to the real client IP
- **WHEN** a request's `REMOTE_ADDR` is within Cloudflare's published ranges
- **THEN** the helper returns the value of `CF-Connecting-IP`

### Requirement: TLS is authenticated end to end
The Cloudflare→origin connection SHALL be encrypted and certificate-validated, not merely the client→Cloudflare hop.

#### Scenario: SSL/TLS mode is Full (strict), not Flexible
- **WHEN** the Cloudflare SSL/TLS configuration is inspected
- **THEN** it is set to Full (strict)

### Requirement: Cloudflare IP range list is refreshable
The origin's allow-list and the application's trust-gate helper SHALL read from one documented, dated source of Cloudflare IP ranges, not two independently maintained copies.

#### Scenario: Single source of truth
- **WHEN** the origin allow-list and `includes/trusted-ip.php` are compared
- **THEN** both reference the same range list, and the list's fetch date is recorded in a comment
