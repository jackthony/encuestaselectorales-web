# Segunda limpieza (post-Codex)

Rama: `cleanup/second-pass`. Sin spec previo (mecánico, reversible, bajo riesgo).

## Criterios de evaluación

Un archivo/clase se marca para borrar solo si cumple **todos**:
1. Grep en `app/`, `resources/views/`, `routes/`, `tests/`, `database/` no encuentra ninguna referencia fuera de su propio archivo (`use`, `new`, type-hint, `view()`, `@include`, bind en `ServiceProvider`, comando artisan).
2. No es autodescubierto por convención de framework (ej. `app/Console/Commands/*` se autoregistra en Laravel 11 aunque nada lo importe — eso NO cuenta como muerto).
3. Si es una interfaz: nadie la consume polimórficamente (nunca se type-hinta la interfaz, solo la clase concreta) y no está bound en `AppServiceProvider`. Interfaz con una sola implementación y cero uso polimórfico = YAGNI, se borra, se usa la clase concreta directo.

Violación de capas (Domain → Application/Infrastructure) se **anota como deuda**, no se corrige en esta pasada — es patrón sistémico (4 contratos), corregirlo bien es refactor de capa completa, no cabe en limpieza mecánica.

## Etapas

1. **Doc** (este archivo) — plan + criterios.
2. **Dead code confirmado**: borrar `app/Domain/Shared/OpaqueId.php`, `app/Domain/Survey/SurveyOptionEligibility.php`. Corregir referencia stale a `SurveyOptionEligibility` en `docs/data-dictionary.md`.
3. **Interfaces sin uso polimórfico**: borrar `app/Application/Import/Contracts/ElectoralCatalogImporter.php` y `CatalogSourceReader.php`. Quitar `implements`/`use` correspondientes en `TransactionalElectoralCatalogImporter.php` y `VersionedCatalogReader.php`.
4. **Marcar deuda de capas**: comentario `// ponytail:` en los 4 contratos de `Domain/*/Contracts` que importan tipos de `Application/`/`Infrastructure/` (`GeographicValidator`, `VotePrivacy`, `SurveyRoundQuery`, `TerritoryCatalog`), señalando el patrón y cuándo justifica invertirlo.
5. **Verificación**: `php artisan test` (o phpunit) tras cada etapa de borrado, confirmar suite verde.

## No tocado en esta pasada

- `Http/Controllers/Api/VoteController.php` inyectando `Infrastructure\Security\TrustedClientIp` directo — utilidad de seguridad, no vale abstraer para un solo caso.
- Duplicación de markup de resultados entre `home.blade.php` y `scope.blade.php` — dos ocurrencias no justifica extraer partial todavía; extraer si aparece una tercera.
- Cambio en curso (sin commitear) en `resources/views/pages/scope.blade.php` — no es parte de esta limpieza, se deja intacto.
