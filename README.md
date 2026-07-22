# Encuestas Electorales - Perú 2026

Plataforma de inteligencia electoral, agregación de encuestas y cartografía política para las Elecciones Municipales y Regionales 2026 en Perú.

## Repository Map
- `docs/repo-map.md`: quick guide to runtime code, reference material, and operational docs.
- `docs/backlog.md`: execution order for the active work.
- `docs/engineering-standards.md`: architecture and API rules.

## Tech Stack
- **Backend:** PHP 8, MySQL (Hostinger)
- **Frontend:** HTML5, Tailwind CSS (CDN), Vanilla JS, Chart.js
- **Security:** Cloudflare WAF, AES-256 IP Encryption

## Core Architecture
- **MVC-Lite:** Decoupled UI partials for maximum performance on shared hosting.
- **Bóveda Segura:** Advanced antifraud system utilizing GPS triangulation, Smart Match, and Trust Scoring to guarantee data purity.
- **Zero Build-Step:** Optimized for immediate FTP deployments without Node.js dependencies in production.
