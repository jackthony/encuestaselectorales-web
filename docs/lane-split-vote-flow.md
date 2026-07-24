# Reparto de carriles — flujo de voto + post-voto

Dos agentes, dos worktrees, sin cruce de archivos. Definido 2026-07-23.

## Codex
Worktree: `C:\Users\jaaguilar\.codex\worktrees\a141`. Rama: `codex/fix-vote-flow`.

- Persistencia del voto end-to-end.
- Conteo en vivo (sin recargar página).
- Errores específicos del flujo: GPS/permiso, fuera de ámbito, duplicado, encuesta no disponible, validación de request.
- Bootstrap local reproducible (datos actuales en la ruta de distrito).
- Spec en `docs/` del flujo de voto y validación.

Archivos reservados: `VoteController`, `RegisterVote`, `AesGcmVotePrivacy`, `ConfiguredGeographicValidator`, `scope.blade.php`.

## Claude Code
Carpeta principal. Rama: `cleanup/second-pass`.

- Miniatura compartible post-voto (Fase 2 OG image).
- Navegación de regreso (home ↔ detalle).
- Inventario `VOTE_TERRITORY_BOUNDS_JSON` + claves de voto para producción.
- UX restante (lo que no sea post-voto técnico ni conteo vivo).

## Regla
Si una tarea toca un archivo reservado del otro, se para y se avisa antes de tocar — no se asume.
