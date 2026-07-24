## 1. Fuentes y assets

- [ ] 1.1 Vendorizar `Inter-Bold.ttf` (700) y `Inter-SemiBold.ttf` (600) bajo licencia SIL OFL en el repo (ej. `resources/fonts/`)
- [ ] 1.2 Confirmar que los assets existentes en `public/assets/miniatura-compartir/` (fondo, logo, domain lockup) son los definitivos a usar en el render GD (no los normalizados de `.dev-tools/compare.php`)

## 2. Transformer (datos reales → shape visual)

- [ ] 2.1 Crear transformer que reciba `RoundResult` (vía `SurveyRoundDetailFactory::make()`) y `TerritoryData`, y devuelva `{eyebrow, title, subtitle, footer_text, results[]}` según spec `og-thumbnail`
- [ ] 2.2 Calcular `percentage`/`bar_width` por opción desde `voteCount`/`totalVotes`, tratando `totalVotes === 0` como `percentage = 0` en todas las opciones
- [ ] 2.2b Derivar `footer_text` de `totalVotes` + `SurveyRoundData::$lastVoteAt` (formateado; sin fecha cuando `lastVoteAt` es `null`), no de un timestamp inventado
- [ ] 2.3 Test unitario del transformer contra los 4 fixtures de `tests/Fixtures/og-results-preview*.php` (caso normal, 3 candidatos, empate, título largo) adaptados a partir de datos reales simulados

## 3. Renderer GD

- [ ] 3.1 Portar constantes de geometría/color de `resources/css/og-results-preview.css` a un renderer PHP GD (canvas 1200x630, panel 42,181/1116x414, rowsAreaTop=44/rowsAreaHeight=325, badges, barras, footer)
- [ ] 3.2 Implementar auto-shrink de título con `imagettfbbox()` (desde 59px hasta mínimo 32px, ancho máx 805px), replicando el comportamiento JS de Fase 1
- [ ] 3.3 Implementar filas dinámicas según cantidad de candidatos (mismo cálculo de `rowPitch` que la vista Blade de Fase 1)
- [ ] 3.4 Test que genera el PNG desde un fixture conocido y verifica dimensiones exactas 1200x630
- [ ] 3.5 Validación visual manual contra `og-results-preview-approved.png` usando `.dev-tools/compare.php` (no gate automático, solo QA)

## 4. Cache por versión de datos

- [ ] 4.1 Implementar cálculo de clave de cache: `round.id` + `SurveyRoundData::$lastVoteAt` (ver Decisión 1 en `design.md`, actualizada tras el cierre del carril de voto en `ffe2a77`/`e2e6eec`)
- [ ] 4.2 Implementar servicio de cache en disco (`storage/app/og-thumbnails/{territory_id}-{hash}.png`): servir si existe, generar y guardar si no
- [ ] 4.3 Test: dos solicitudes con el mismo `lastVoteAt` reusan el mismo archivo cacheado (no se regenera)
- [ ] 4.4 Test: `lastVoteAt` avanza (voto nuevo) → clave distinta y PNG nuevo

## 5. Endpoint

- [ ] 5.1 Crear `OgThumbnailController::show(string $scope, string $slug)` reusando `TerritoryCatalog` + `SurveyRoundQuery` (mismos contratos que `PublicPortalController`)
- [ ] 5.2 Registrar ruta `GET /distrito/{scope}/{slug}/og-image.png` (o equivalente ya usado por `scope.blade.php` para esa combinación) en `routes/web.php`
- [ ] 5.3 Responder 404 cuando el territorio no existe, no está publicado, o no tiene ronda activa (sin generar PNG con datos vacíos)
- [ ] 5.4 Feature test: territorio con ronda activa → 200 + `Content-Type: image/png`
- [ ] 5.5 Feature test: territorio sin ronda activa / inexistente → 404

## 6. Cableado en la página real

- [ ] 6.1 Asignar la URL real del endpoint a `shareImage` en `PublicPortalPageService::scopeViewData()` cuando hay ronda activa (dejar `null` en los demás casos)
- [ ] 6.2 Corregir `og:image:width`/`og:image:height` en `resources/views/partials/head.blade.php` a `1200`/`630`
- [ ] 6.3 Verificar manualmente en navegador que el `og:image` de una página de distrito real resuelve al PNG generado (curl o Facebook Sharing Debugger)

## 7. Cierre

- [ ] 7.1 Correr suite completa de tests (`php artisan test` o equivalente) y confirmar verde
- [ ] 7.2 `/opsx:verify` contra la spec `og-thumbnail`
- [ ] 7.3 `/opsx:archive` una vez verificado
