## ADDED Requirements

### Requirement: Transformación de resultados reales al shape visual
El sistema SHALL transformar el `RoundResult`/ranked options de un territorio
(la misma fuente que usa la página de resultados vía
`SurveyRoundDetailFactory`) al shape `{eyebrow, title, subtitle, footer_text,
results: [{position, candidate_name, party_name, percentage, votes,
bar_width}]}` validado en Fase 1, sin duplicar la lógica de ranking/empates
existente.

#### Scenario: Ronda con votos
- **WHEN** se transforma una ronda activa con `total_votes > 0` y opciones
  con `voteCount` distintos
- **THEN** `results` queda ordenado igual que `ranked_options` (mismo criterio
  de empate: votos desc, luego `display_order`, luego nombre), y cada
  `percentage`/`bar_width` se calcula desde `voteCount / total_votes`

#### Scenario: Ronda sin votos todavía
- **WHEN** se transforma una ronda activa con `total_votes === 0`
- **THEN** todas las opciones quedan con `percentage = 0` y `bar_width = 0`,
  sin división por cero

### Requirement: Render server-side pixel-fiel al diseño aprobado
El sistema SHALL generar un PNG de 1200x630 px usando PHP GD que reproduce la
geometría, colores y assets definidos en
`resources/css/og-results-preview.css` (panel, filas dinámicas según cantidad
de candidatos, badges de posición, barras de progreso, footer), sin depender
de un navegador headless.

#### Scenario: Generación exitosa
- **WHEN** se solicita el render para un territorio con ronda activa
- **THEN** el PNG resultante mide exactamente 1200x630 px

#### Scenario: Título largo se ajusta sin truncar
- **WHEN** el nombre del territorio no entra en 805px de ancho al tamaño base
  (59px)
- **THEN** el render reduce el tamaño de fuente del título hasta que entre, sin
  bajar de 32px, replicando el comportamiento ya validado en Fase 1

### Requirement: Cache por versión de datos, no por tiempo
El sistema SHALL cachear el PNG generado en disco con una clave derivada
determinísticamente del identificador de la ronda y los `voteCount` de sus
opciones, y SHALL reusar el archivo cacheado mientras esa combinación no
cambie.

#### Scenario: Vote counts sin cambios
- **WHEN** se solicita la miniatura dos veces seguidas sin que cambien los
  votos de la ronda
- **THEN** ambas respuestas usan el mismo archivo cacheado (no se regenera el
  PNG)

#### Scenario: Vote counts cambian
- **WHEN** cambian los `voteCount` de al menos una opción de la ronda entre
  dos solicitudes
- **THEN** la clave de cache cambia y se genera (y sirve) un PNG nuevo que
  refleja los resultados actualizados

### Requirement: Endpoint de servido de la miniatura
El sistema SHALL exponer una ruta pública que, dado `scope` y `slug` de un
territorio publicado, responde con el PNG (`Content-Type: image/png`)
correspondiente a su ronda activa.

#### Scenario: Territorio con ronda activa
- **WHEN** se pide la ruta de miniatura para un territorio publicado con
  ronda activa
- **THEN** responde 200 con el PNG generado/cacheado de esa ronda

#### Scenario: Territorio sin ronda activa o inexistente
- **WHEN** se pide la ruta de miniatura para un territorio sin ronda activa
  vigente, o que no existe/no está publicado
- **THEN** responde 404, sin intentar generar un PNG con datos vacíos

### Requirement: `shareImage` real en la página de resultados
El sistema SHALL asignar en `PublicPortalPageService::scopeViewData()` la URL
real del endpoint de miniatura al campo `shareImage` (reemplazando el `null`
actual) cuando el territorio tiene ronda activa, y el `head.blade.php` SHALL
declarar `og:image:width`/`og:image:height` acordes al tamaño real generado
(1200x630).

#### Scenario: Página de un territorio con ronda activa
- **WHEN** se renderiza la vista de un territorio con ronda activa
- **THEN** el meta tag `og:image` apunta al endpoint de miniatura de ese
  territorio, y `og:image:width`/`og:image:height` son `1200`/`630`
