## 1. Fuentes y assets

- [x] 1.1 Vendorizar `Inter-Bold.ttf` (700) y `Inter-SemiBold.ttf` (600) bajo licencia SIL OFL en el repo (ej. `resources/fonts/`)
- [x] 1.2 Confirmar que los assets existentes en `public/assets/miniatura-compartir/` (fondo, logo, domain lockup) son los definitivos a usar en el render GD (no los normalizados de `.dev-tools/compare.php`)

## 2. Transformer (datos reales → shape visual)

- [x] 2.1 Crear transformer que reciba `RoundResult` (vía `SurveyRoundDetailFactory::make()`) y `TerritoryData`, y devuelva `{eyebrow, title, subtitle, footer_text, results[]}` según spec `og-thumbnail`
- [x] 2.2 Calcular `percentage`/`bar_width` por opción desde `voteCount`/`totalVotes`, tratando `totalVotes === 0` como `percentage = 0` en todas las opciones
- [x] 2.2b Derivar `footer_text` de `totalVotes` + `SurveyRoundData::$lastVoteAt` (formateado; sin fecha cuando `lastVoteAt` es `null`), no de un timestamp inventado
- [x] 2.3 Test unitario del transformer (equivalente en cobertura a los 4 fixtures de Fase 1: caso normal, sin votos/percentage 0, estado no activo → null)

## 3. Renderer GD

- [x] 3.1 Portar constantes de geometría/color de `resources/css/og-results-preview.css` a un renderer PHP GD (canvas 1200x630, panel 42,181/1116x414, rowsAreaTop=44/rowsAreaHeight=325, badges, barras, footer)
- [x] 3.2 Implementar auto-shrink de título con `imagettfbbox()` (desde 59px hasta mínimo 32px, ancho máx 805px), replicando el comportamiento JS de Fase 1
- [x] 3.3 Implementar filas dinámicas según cantidad de candidatos (mismo cálculo de `rowPitch` que la vista Blade de Fase 1)
- [x] 3.4 Test que genera el PNG desde un fixture conocido y verifica dimensiones exactas 1200x630
- [x] 3.5 Validación visual manual contra `og-results-preview-approved.png` — layout/colores/posiciones coinciden. Investigado el nombre largo que no entraba en 330px: medí el `.ttf` vendorizado contra el woff real que sirve Google Fonts CDN (mismo peso 700) para el mismo string — ambos miden 413px, idénticos; no era un problema de métricas de fuente ni de referencia desactualizada. Arreglado aplicando el mismo shrink-to-fit que ya tenía el título (22px→16px mínimo antes de truncar) — el nombre completo ahora entra sin cortar, visualmente coincide con la referencia

## 4. Cache por versión de datos

- [x] 4.1 Implementar cálculo de clave de cache: `round.id` + `SurveyRoundData::$lastVoteAt` (ver Decisión 1 en `design.md`, actualizada tras el cierre del carril de voto en `ffe2a77`/`e2e6eec`)
- [x] 4.2 Implementar servicio de cache en disco (`storage/app/og-thumbnails/{territory_id}-{hash}.png`): servir si existe, generar y guardar si no
- [x] 4.3 Test: dos solicitudes con el mismo `lastVoteAt` reusan el mismo archivo cacheado (no se regenera)
- [x] 4.4 Test: `lastVoteAt` avanza (voto nuevo) → clave distinta y PNG nuevo

## 5. Endpoint

- [x] 5.1 Crear `OgThumbnailController::show(string $scope, string $slug)` reusando `TerritoryCatalog` + `SurveyRoundQuery` (mismos contratos que `PublicPortalController`)
- [x] 5.2 Registrar ruta en `routes/web.php` — corregida a `GET /encuestas/{scope}/{slug}/og-image.png` (el prefijo real es `/encuestas`, no `/distrito` como decía el borrador de esta tarea; confirmado contra `routes/web.php` antes de escribir)
- [x] 5.3 Responder 404 cuando el territorio no existe, no está publicado, o no tiene ronda activa (sin generar PNG con datos vacíos)
- [x] 5.4 Feature test: territorio con ronda activa → 200 + `Content-Type: image/png`
- [x] 5.5 Feature test: territorio sin ronda activa / inexistente → 404

## 6. Cableado en la página real

- [x] 6.1 Asignar la URL real del endpoint a `shareImage` en `PublicPortalPageService::scopeViewData()` cuando hay ronda activa (dejar `null` en los demás casos) — cubierto con test en `PublicPortalPageServiceTest`
- [x] 6.2 Corregir `og:image:width`/`og:image:height` en `resources/views/partials/head.blade.php` a `1200`/`630`
- [x] 6.3 Verificado end-to-end: sqlite descartable en scratchpad (fuera del repo) + `php artisan serve` local + seed mínimo (territorio/candidato/ronda real vía Eloquent, sin tocar datos del repo) → `curl` a `/encuestas/district/san-isidro` confirma `og:image` apunta a `/encuestas/district/san-isidro/og-image.png` con `width=1200`/`height=630`, y esa URL devuelve `Content-Type: image/png` real y válido (caso 0 votos, single-candidate)

## 7. Cierre

- [x] 7.1 Correr suite completa de tests (`php artisan test`) — 24/24 verde (109 assertions)
- [x] 7.2 `/opsx:verify` no está entre las skills disponibles esta sesión (solo propose/apply/archive/explore/sync) — verificación manual: `openspec validate og-thumbnail-fase2` → "valid"; recorridos los 5 requirements de `specs/og-thumbnail/spec.md` uno por uno contra los tests reales. Encontrado y cerrado un hueco real: el shrink-to-fit del título no tenía test determinístico (solo verificación manual ad-hoc) — agregados `OgThumbnailRendererTest::test_title_shrinks_to_fit_a_moderately_long_district_name` y `test_title_stops_shrinking_at_32px_even_if_still_too_wide` (vía Reflection sobre el método privado). Hallazgo real de paso: el nombre de distrito más largo real (data/territories_ubigeo_map.json) no entra ni al piso de 32px (837px medidos vs 805px de caja) — comportamiento esperado y aceptado (mismo trade-off que ya tenía el JS de Fase 1, no una regresión)
- [ ] 7.3 `/opsx:archive` una vez verificado
