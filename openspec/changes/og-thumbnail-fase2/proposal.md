## Why

Cuando alguien comparte el link de resultados de un distrito/provincia/región
en WhatsApp, Facebook o Twitter, el crawler social pide `og:image` en frío —
no ejecuta JS ni ve lo que el usuario ve en pantalla. Hoy `shareImage` es
`null` en `PublicPortalPageService::scopeViewData()`, así que no hay
miniatura, o cae a un genérico sin datos. Fase 1 ya validó y aprobó el diseño
visual (prototipo estático HTML/CSS + PNG de referencia); falta conectar ese
diseño a los datos reales de la ronda vigente y servirlo como imagen server-side.

## What Changes

- Nuevo transformer que mapea `RoundResult`/`ranked_options` (fuente real,
  vía `SurveyRoundDetailFactory`) al shape visual ya validado en Fase 1
  (`eyebrow/title/subtitle/footer_text/results[]`), calculando `bar_width`
  desde `percentage`.
- Nuevo renderer PHP GD que reproduce 1:1 la geometría de
  `resources/css/og-results-preview.css` (canvas 1200x630, panel, filas,
  badges, barras, colores) sobre los assets ya existentes en
  `public/assets/miniatura-compartir/`.
- Vendorizar las fuentes Inter (Bold/SemiBold) como `.ttf` en el repo — GD no
  puede usar Google Fonts CDN.
- Endpoint que sirve el PNG generado, cacheado en disco con clave
  determinística derivada de los vote counts de la ronda (sin agregar columna
  de timestamp nueva) — se regenera solo cuando cambian los resultados.
- Cablear `PublicPortalPageService::scopeViewData()` para poner la URL real en
  `shareImage` en vez de `null`.
- **BREAKING (contenido, no contrato)**: corregir `og:image:width`/`og:image:height`
  en `resources/views/partials/head.blade.php`, que hoy dicen `1080x1350` y
  no coinciden con la imagen real de `1200x630`.

## Capabilities

### New Capabilities
- `og-thumbnail`: generación server-side de la miniatura Open Graph de
  resultados (transformer de datos reales → shape visual, render GD sobre el
  diseño aprobado en Fase 1, cache por versión de datos, endpoint de
  servido, cableado en `shareImage`).

### Modified Capabilities
(ninguna — no hay specs previas en `openspec/specs/`, `og-thumbnail` es
capability nueva)

## Impact

- Código nuevo: transformer, renderer GD, endpoint/ruta, servicio de cache.
- Código modificado: `PublicPortalPageService::scopeViewData()` (asignar
  `shareImage`), `resources/views/partials/head.blade.php` (fix dimensiones).
- Assets nuevos: `Inter-Bold.ttf`, `Inter-SemiBold.ttf` (o el peso exacto que
  use el CSS aprobado) vendorizados en el repo.
- No toca: `VoteController`, `RegisterVote`, `AesGcmVotePrivacy`,
  `ConfiguredGeographicValidator`, `scope.blade.php` (carril reservado de
  Codex, ver `docs/lane-split-vote-flow.md`).
- No toca la fuente de datos (`EloquentSurveyRoundQuery`, `SurveyRoundData`,
  `CandidateOptionData`, `SurveyRoundDetailFactory`) — solo la consume.
