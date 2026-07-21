# Project Core Profile

**Mission:** Build the most authoritative, secure, and data-driven electoral polling platform for Peru (2026).
**Stack:** PHP 8, MySQL (MariaDB), Tailwind CSS (via CDN), Vanilla JavaScript.
**Deployment:** Hostinger (Apache/PHP) + Cloudflare proxy.

## Required Documents Map
- `docs/backlog.md`: The single source of truth for execution order.
- `docs/engineering-standards.md`: API contracts and clean architecture rules.

## Architecture Rules (MVC-Lite)
1. **No Node.js builds in production:** Tailwind runs via CDN. Vanilla JS only.
2. **DRY through Partials:** All repeated HTML MUST live in `/partials/`.
3. **Visual Integrity:** The design, animations, and palette established in the Canvas HTML prototypes are final. Preserve class names exactly during refactoring.
4. **Image Handling:** Photos are in `/assets/img/candidatos/` as `.webp` named by DNI (e.g., `07123456.webp`). Implement a CSS initials fallback if missing.

## Security Rules (Anti-Hack)
1. **Zero Sequential IDs:** Never use `AUTO_INCREMENT`. Use UUIDv4 or NanoID.
2. **IP Blindness:** Store only `HTTP_CF_CONNECTING_IP` encrypted with `AES-256-GCM` (authenticated — CBC is malleable and has no integrity tag) and hashed with a salt for uniqueness.
3. **Secret Isolation:** DB credentials and AES keys must live in `/config/`, be Git-ignored, and — on Hostinger — sit outside `public_html/`. A key hardcoded in a `.php` under the web root is a leak waiting for one server misconfiguration.
4. **Never trust the client for anti-abuse.** Fingerprint, device token and GPS all arrive from the browser and can be forged with one `curl` call. They are *signals*, never the sole gate. Server-side rate limiting on the hashed IP is the actual floor.
5. **Prepared statements always.** String-concatenated SQL is a blocking review failure.

## Editorial & Legal Rules
1. **Editorial firewall is absolute.** B2B revenue (featured candidate profiles) never touches poll numbers, rankings, or aggregation logic. No exceptions, no "just this once." The product sells trust; one crossed line ends it.
2. **Cite and link, never wholesale-reproduce.** This site republishes third-party pollster data. Link the source report, quote the figure, never mirror the PDF.
3. **Every judicial claim carries its JNE source + date and a correction path.** Candidate judicial records are the highest-liability content on the site. Verify photo-reuse terms before publishing JNE photos.
4. **When in doubt, publish less.**

## Workflow
- Spanish for public UI/UX content; English for code, comments, and commits.
- Read `docs/engineering-standards.md` before writing any PHP/JS logic.

## MCPs y APIs
Para trabajar con este proyecto, conviene tener disponibles estas integraciones:

- `GitHub`: preferir MCP o, como mínimo, `gh` CLI autenticado para PRs, issues, releases y revisión de ramas.
- `Hostinger`: preferir MCP si está disponible; si no, usar API/CLI/documentación del hosting para deployments, bases de datos y dominios.
- `Codebase memory`: mantener indexado el repo para búsquedas de arquitectura, trazado de dependencias y análisis de impacto.
- `OpenSpec`: usarlo para cambios estructurados de producto y tareas de implementación.

Si un MCP no existe aún a nivel global, documentar el fallback equivalente en CLI/API para no bloquear el flujo de trabajo.
