# Brief para Gemini Canvas — Página de Distrito

> Copiar desde la línea de abajo y pegar en Gemini.

---

Necesito el prototipo HTML de **una sola pantalla**: la página de detalle de distrito de `encuestaselectorales.pe` (encuestas electorales municipales, Perú 2026). Archivo único, autocontenido, Tailwind por CDN.

## Regla que manda sobre todas las demás

Esta pantalla se diseña **para el estado vacío primero**. Hoy, y hasta el 5 de agosto de 2026 (fecha en que el JNE admite las listas de candidatos), **42 de los 43 distritos de Lima no tienen ni un solo candidato registrado**. Solo Miraflores tiene datos, y son históricos de 2022.

Si diseñas la versión "llena" y resuelves el vacío con un `<p>Sin datos</p>`, estás diseñando la pantalla que casi nadie va a ver. El estado vacío es el producto durante los próximos meses: tiene que verse deliberado, informativo y confiable — no roto.

## Los 3 estados, en orden de importancia

### Estado 1 — Sin candidatos (por defecto, 42 de 43 distritos)

Lo que el sistema sí sabe y puede mostrar con honestidad:

- Nombre del distrito, provincia Lima, región Lima
- Que las candidaturas aún no existen porque el JNE no las ha admitido
- **Las fechas reales del calendario**: listas admitidas 5 de agosto de 2026, candidaturas finales 5 de septiembre de 2026, elección 4 de octubre de 2026
- Un CTA de aviso ("avísame cuando haya datos de [Distrito]") vía WhatsApp
- Enlaces a metodología y a otros distritos

Esta pantalla debe transmitir *"este sitio es riguroso y todavía no hay nada que reportar"*, nunca *"este sitio está vacío"*. La diferencia es todo el producto.

### Estado 2 — Con candidatos, sin encuestas

Directorio de candidatos. Por candidato solo existe: nombre completo, partido (nombre, siglas, color), cargo. **No hay foto ni número de lista** (`foto: null`, `numero: null` en todos los registros) — diseña un avatar de iniciales sobre el color del partido, no un placeholder de imagen rota.

Si los candidatos son de un proceso anterior (2022), la pantalla debe decirlo de forma prominente e inconfundible. Mostrar candidatos de 2022 sin etiquetar como si fueran de 2026 es desinformación, no un detalle de UI.

### Estado 3 — Con candidatos y resultados

Barras horizontales con porcentaje, ordenadas de mayor a menor, coloreadas por partido. Junto a cada número: la ficha técnica de la encuesta (encuestadora, fechas de campo, tamaño de muestra, margen de error, nivel de confianza, modalidad). El número nunca aparece sin su procedencia visible — es la promesa central del producto.

## El widget de voto

La página incluye el widget de sondeo propio (modal GPS, ya prototipado aparte, **no lo rediseñes**). Solo indica dónde va y qué muestra cuando **no hay candidatos por quienes votar** — que es el caso en 42 distritos.

## Datos reales (usa estas formas exactas, no inventes campos)

```json
distrito:  { "id": "miraflores", "nombre": "Miraflores", "provincia": "lima", "region": "lima" }
candidato: { "id": 1, "nombre": "Carlos Fernando Canales Anchorena", "partidoId": 1,
             "cargo": "alcalde_distrital", "distritoId": "miraflores",
             "foto": null, "numero": null, "activo": false }
partido:   { "id": 1, "nombre": "Renovación Popular", "siglas": "RP", "color": "#B22222", "logo": null }
encuesta:  { "id": "...", "fechaInicio": "2022-08-15", "fechaFin": "2022-09-01",
             "tamanoMuestra": 400, "margenError": 4.9, "nivelConfianza": 95,
             "modalidad": "presencial", "encuestadoraId": "ejemplo" }
resultado: { "candidatoId": 1, "porcentaje": 24.5, "tendencia": "estable", "cambioPorcentaje": 0 }
```

`distrito.json` tiene **solo** esos 4 campos. No hay población, ni alcalde actual, ni historial. No diseñes tarjetas de estadísticas que requieran datos que no existen.

## Restricciones legales (no negociables)

1. **Ninguna encuestadora real** (Ipsos, Datum, CPI, IEP) atribuida a una cifra de demostración. Usa "Encuestadora de ejemplo (dato ficticio, no es una institución real)".
2. **Solo distritos de Lima Metropolitana.** Nada presidencial, nada regional, ningún GORE.
3. Todo número inventado va visiblemente etiquetado como dato de ejemplo.

## Consistencia visual (crítico — hay un check automático)

El sitio ya está refactorizado a PHP con header y footer compartidos. **Reutiliza exactamente el header y el footer de `portal_de_sondeos_ciudadanos.html`** (logo de una línea, botón de búsqueda, nav "Distritos de Lima ▾"). No diseñes una variante nueva: existe un check que compara la estructura contra ese header, y una tercera generación lo rompe.

Paleta y tipografía ya fijadas:

```js
brand: {
  blue:  '#102f86',   // ancla de marca, chrome y titulares
  green: '#15ba75',   // SOLO indicadores no-texto (barras, puntos, bordes)
  bg:    '#f4f5f3',
  card:  '#ffffff',
  border:'#e2e8f0',
  text:  '#0f172a',
  textMuted: '#475569'
}
```

`#15ba75` da 2.5:1 con texto blanco encima y **falla WCAG AA**. Si necesitas verde con texto encima, usa `#0f7a4a` (5.4:1). Todo par texto/fondo debe llegar a 4.5:1.

Tipografía: `Inter` para cuerpo, `Noto Serif` para titulares. CDNs: Tailwind, FontAwesome 6.4.0, Google Fonts — los mismos que ya usa el sitio.

## Entregable

Un `.html` autocontenido con los 3 estados visibles (uno debajo del otro, separados por un divisor rotulado), para poder compararlos. Mobile-first. Sin librerías fuera de las 3 CDNs mencionadas.
