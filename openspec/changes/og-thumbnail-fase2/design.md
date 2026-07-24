## Context

Fase 1 dejó un prototipo visual aprobado (HTML/CSS estático + PNG de
referencia) para la miniatura Open Graph de resultados por territorio, pero
sin conexión a datos reales ni a un mecanismo de servido. `shareImage` en
`PublicPortalPageService::scopeViewData()` es `null` hoy.

Restricciones duras:
- Hosting compartido (Hostinger, sin root, 60 PHP workers compartidos entre
  19 sitios) → nada de Chrome/Puppeteer headless en producción. Solo PHP GD.
- Los crawlers de OG piden la imagen en frío, sin sesión ni JS → todo el
  render tiene que resolverse en el request HTTP del crawler, rápido.
- No hay ningún timestamp real de "última actualización" en `SurveyRoundData`
  / `CandidateOptionData` — solo `voteCount` por opción.
- No hay `.ttf` de Inter en el repo; el prototipo Fase 1 depende de Google
  Fonts CDN, que GD no puede usar.

## Goals / Non-Goals

**Goals:**
- Servir un PNG de 1200x630 que reproduce pixel-a-pixel el diseño aprobado
  en Fase 1, con datos reales de la ronda vigente de un territorio.
- Regenerar la imagen solo cuando cambian los resultados reales (no en cada
  visita/crawler), sin saturar los PHP workers compartidos.
- Cablear la URL real en `shareImage` y corregir las dimensiones declaradas
  en `head.blade.php`.

**Non-Goals:**
- Miniatura para el home (listado de rondas) — solo la vista de detalle por
  territorio (`scope.blade.php` / `PublicPortalController::scope()`).
- Replicar el diff pixel-a-pixel automatizado de `.dev-tools/compare.php` en
  CI — sigue siendo herramienta dev-only.
- Rotación/limpieza automática de PNGs cacheados viejos (ver Riesgos).
- Internacionalización o soporte de otros formatos de imagen (solo PNG).

## Decisions

**1. Clave de cache = `round.id` + `SurveyRoundData::$lastVoteAt`.**
Actualizado 2026-07-23: Codex cerró el carril de voto en el commit `ffe2a77`
(mergeado a `cleanup/second-pass` en `e2e6eec`) y agregó `lastVoteAt`
(`?CarbonImmutable`, vía `EloquentSurveyRoundQuery::latestVoteAt()` =
`MAX(votes.created_at)` de las opciones de la ronda) — es real, está
commiteado, no es hipotético. Reemplaza la decisión anterior (hash de vote
counts): `sha1($round->id . '|' . ($round->lastVoteAt?->toIso8601String() ?? 'no-votes'))`.
Motivo del cambio: `lastVoteAt` es la misma señal que ya usa el resto del
sistema (nada nuevo que sincronizar, no es una fuente paralela) y además
resuelve gratis el footer `"Actualizado: DD/MM/AAAA HH:MM"` que en Fase 1 era
un valor inventado en el fixture — ahora sale del mismo dato real que arma la
clave de cache. Cuando `lastVoteAt` es `null` (ronda sin votos aún), el
footer debe mostrar el total en 0 sin fecha de actualización (ver spec,
requirement del transformer).
No renombrar/mover `SurveyRoundData`, `CandidateOptionData` ni
`EloquentSurveyRoundQuery` — son la fuente de verdad compartida con el flujo
de voto (carril de Codex), este change solo las consume.

**2. Archivo cacheado en disco, nombrado por territorio + hash:**
`storage/app/og-thumbnails/{territory_id}-{hash}.png`. Si el archivo existe,
se sirve tal cual (el hash ya garantiza que el contenido es válido); si no,
se genera y se guarda antes de responder. No se genera bajo `public/` ni se
depende del symlink `public/storage` (evita issues de symlink en hosting
compartido) — se sirve vía una ruta/controlador dedicado que lee el archivo y
responde con `Content-Type: image/png` + `Cache-Control` largo (el crawler ya
vuelve a pedir la URL cuando el link se comparte de nuevo, no hace falta
`Cache-Control` corto).

**3. Ruta dedicada, no reusar `PublicPortalController::scope()`.**
`GET /distrito/{scope}/{slug}/og-image.png` → nuevo controlador
`OgThumbnailController::show()`. Reusa `TerritoryCatalog` + `SurveyRoundQuery`
+ `SurveyRoundDetailFactory` (mismos contratos que ya usa
`PublicPortalPageService`, sin duplicar la consulta). Separado del
controlador de la página HTML porque la respuesta es binaria y su ciclo de
cache es distinto.

**4. Transformer puro `OgThumbnailData` (o similar), sin lógica de negocio.**
Toma `RoundResult`/`ranked_options` (ya ordenado por
`SurveyRoundDetailFactory`) + `TerritoryData` y arma el shape validado en
Fase 1: `eyebrow`, `title`, `subtitle`, `footer_text`, `results[]` (con
`bar_width` derivado de `percentage`, no del origen). Reusa el mismo cálculo
de ranking/empates que ya usa la página real — no reimplementa el `usort`.

**5. Renderer GD replica la geometría fija del CSS aprobado, no la
reinterpreta.** Todas las coordenadas/tamaños de
`resources/css/og-results-preview.css` se llevan literal a constantes PHP
(panel en 42,181 / 1116x414; rowsAreaTop=44; rowsAreaHeight=325; barra máx
258px; colores exactos). El único cálculo dinámico es el de Fase 1: alto de
fila según cantidad de candidatos, y auto-shrink del título (hoy JS vía
`document.fonts.ready`) reimplementado con `imagettfbbox()` para medir texto
y reducir tamaño hasta caber en 805px o tocar el mínimo de 32px — mismo
comportamiento, sin navegador.

**6. Vendorizar `Inter-Bold.ttf` (700) e `Inter-SemiBold.ttf` (600) en el
repo**, bajo licencia SIL OFL 1.1 (permite embeber/redistribuir). Son los
únicos dos pesos que usa el CSS aprobado.

## Risks / Trade-offs

- **[Riesgo] Archivos PNG cacheados se acumulan por territorio** (un archivo
  nuevo por cada cambio de resultado, los viejos no se borran) → **Mitigación**:
  aceptable para Fase 2 (volumen bajo: 50 distritos, cambios discretos por
  voto agregado, no por voto individual salvo que el orden/porcentaje
  cambie); dejar tarea de limpieza periódica (`artisan schedule` o comando
  manual) fuera de este change, documentada como deuda conocida.
- **[Riesgo] Medición de texto con `imagettfbbox` no es 100% idéntica al
  layout engine del navegador** (kerning/hinting distintos) → **Mitigación**:
  el margen ya existe en el diseño (min 32px, contenedor 805px con aire);
  validar visualmente contra `og-results-preview-approved.png` con
  `.dev-tools/compare.php` como parte de QA manual, no como gate automático.
- **[Riesgo] Ronda sin votos (`total_votes = 0`)** → división por cero al
  calcular `percentage`/`bar_width` → **Mitigación**: transformer trata
  `total_votes === 0` como `percentage = 0` para todas las opciones,
  explícito en la spec.
- **[Trade-off] No hay invalidación por tiempo, solo por contenido** — si el
  hosting pierde el archivo cacheado (deploy que limpia `storage/`), se
  regenera en el próximo request sin intervención manual; costo aceptado.

## Migration Plan

1. Vendorizar fuentes TTF.
2. Construir transformer + renderer + tests unitarios contra los fixtures de
   Fase 1 (deben producir visualmente el mismo layout que
   `og-results-preview-approved.png`).
3. Agregar ruta + `OgThumbnailController`, con cache en disco.
4. Cablear `shareImage` en `PublicPortalPageService::scopeViewData()` y
   corregir `og:image:width/height` en `head.blade.php`.
5. Rollback: revertir el commit que cablea `shareImage` (vuelve a `null`,
   comportamiento actual) sin afectar el resto del sitio — el endpoint nuevo
   queda huérfano pero inofensivo si algo sale mal en producción.

## Open Questions

- ¿El dominio de producción sirve `storage/app/...` sin problema de permisos
  de escritura en Hostinger compartido, o hay que confirmar la ruta
  writable real antes de implementar? (a validar en `/opsx:apply`, no
  bloquea el diseño).
