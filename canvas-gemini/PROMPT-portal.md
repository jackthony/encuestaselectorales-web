# Prompt para Gemini Canvas — Portal encuestaselectorales.pe (Lima 2026)

> Pegá TODO esto en Gemini Canvas. Pedile un solo archivo HTML autocontenido. Cuando lo genere, copiámelo a Claude Code y yo lo pulo (self-host de assets, data-driven, accesibilidad, sin misatribución).

---

## Rol
Sos diseñador de producto senior + front-end. Diseñá el portal web de **encuestaselectorales.pe**: plataforma ciudadana, neutral e independiente de **sondeos de opinión online** para las **elecciones municipales de Lima 2026**. Calidad objetivo: periodismo de datos tipo FiveThirtyEight / Pew Research / Datafolha. Limpio, confiable, denso en información, moderno 2026. Marca: "Hecho por Neuracode".

## Alcance (importante — no te salgas de acá)
- **Solo Lima, solo distritos.** NO presidencial, NO regiones, NO nacional, NO "simulador de voto".
- **Solo nuestra propia encuesta web** (sondeo ciudadano online, opt-in). NO menciones ni inventes encuestas de Ipsos, CPI, Datum, IEP ni ninguna encuestadora real — no agregamos terceros en esta versión, solo mostramos los resultados de nuestro propio sondeo web por distrito.
- Fase Beta: los datos son de ejemplo (aún no hay candidaturas 2026 oficiales — el JNE las habilita el 5 ago 2026). Etiquetá visiblemente "Dato de ejemplo" / "Fase Beta".

## Dirección visual (respetá la paleta)
- **Colores:** azul institucional `#102f86` (primario, titulares, botones), verde `#15ba75` (acento/acción, badges "en vivo", hovers, barras), fondo `#fcfcfc`, tarjetas `#ffffff`, bordes `#e5e7eb`, texto `#111827` / gris `#4b5563`.
- **Tipografía:** titulares en serif editorial (Noto Serif, peso 700/800). Cuerpo y datos en sans (Inter). Jerarquía fuerte, titulares grandes.
- **Estética 2026:** flat + editorial, bordes suaves redondeados, sombras muy sutiles (nada pesado). Micro-interacciones: hover lift en tarjetas, scroll-reveal en cascada, spring en botones, badge "en vivo" con pulso. Header sticky. Ticker superior verde con reloj en tiempo real. Botón flotante de WhatsApp. Footer institucional oscuro con borde verde superior.
- Accesible: contraste AA. El verde `#15ba75` **no** se usa para texto sobre fondo claro (solo UI/barras/badges); para links y texto destacado usá el azul `#102f86`.

## Pantallas y componentes

### 1. Header global (sticky)
- Ticker verde arriba: "Sondeo ciudadano en vivo · Elecciones 2026" + reloj (fecha/hora en vivo).
- Marca centrada: **Encuestas**electorales**.pe** (azul + verde) con bajada "Sondeo ciudadano · Lima 2026".
- Nav: Inicio · **Distritos de Lima ▾** (dropdown/mega-panel con los 43 distritos en columnas) · Metodología · Quiénes somos.
- Ícono **lupa** que abre un **buscador modal tipo command-palette (⌘K, estilo Stripe/Apple):** overlay oscuro, caja central, input grande, al teclear "mira/barr/surco" filtra los 43 distritos en vivo, Enter navega. Teclado-accesible (Esc cierra, flechas navegan).

### 2. Home
- **Hero** (fondo gris claro `#f4f5f3`): H1 serif grande "¿Quién va ganando en tu distrito?", bajada "El sondeo ciudadano online de Lima. Opiná, mirá los resultados en vivo y compará distrito por distrito — con metodología siempre visible." + badge "Fase Beta · datos de ejemplo".
- **Layout 2 columnas** (feed principal + sidebar):
  - **Feed de sondeos por distrito** (grid 2-col): una **tarjeta por distrito**. Tarjeta con resultados muestra: nombre del distrito, "Actualizado [fecha] · N votos", badge "Dato de ejemplo", **top 3 candidatos** (swatch color del partido + nombre + siglas + % + barra de progreso fina verde), pie "Sondeo web opt-in · muestra auto-seleccionada" + "Ver detalle →". Distritos sin datos: estado vacío honesto "Sondeo abierto — aún sin votos suficientes. Sé el primero en opinar."
  - **Sidebar:** (a) caja azul "Metodología honesta" — "No somos una encuestadora. Sondeo web abierto (opt-in): muestra auto-seleccionada, no probabilística. Te lo decimos en cada resultado." (b) **Widget de voto interactivo** (ver §4).

### 3. Detalle de distrito
- Link "← Volver a distritos".
- Título serif "[Distrito] — Alcaldía Distrital" + badges "Sondeo web ciudadano" / "Dato de ejemplo".
- **Ficha técnica** en fila: Votos · Modalidad (Online opt-in) · Indecisos % · Blanco/Nulo % · Actualizado.
- **Resultados completos** (los 8 candidatos): swatch partido + nombre + partido + % + barra.
- Disclaimer de honestidad metodológica (opt-in, no probabilístico, no representa a toda la población, dato de ejemplo).
- Sidebar: widget de voto + caja "Comparte / Generar tarjeta".

### 4. Widget de voto (sondeo interactivo)
- Cabecera: badge verde con pulso "Sondeo interactivo abierto" + pregunta "Si las elecciones para [Alcaldía de Lima / distrito] fueran mañana, ¿por quién votarías?".
- Opciones radio: candidatos (nombre + partido) + "Blanco / Viciado / No precisa".
- Botón azul "Registrar mi voto".
- **Disclaimer honesto (obligatorio):** "Demo. Al activarse: 1 voto por dispositivo, verificación anti-bot, IP hasheada (nunca cruda). Nunca te rastreamos." — NO afirmes que la protección anti-bot ya está activa; es a futuro.

### 5. Footer institucional (oscuro, borde verde arriba)
- Marca + descripción + nota "Sondeo web opt-in (no probabilístico). No somos una encuestadora. Metodología siempre visible."
- Columnas: Navegación (Inicio, Metodología, Política editorial, Privacidad) · Contacto (contacto@encuestaselectorales.pe · WhatsApp +51 971 388 435).

## Reglas duras (no las rompas)
1. **Cero terceros:** ninguna encuestadora real nombrada. Solo "sondeo web propio".
2. **Honestidad metodológica visible** en cada resultado (opt-in, no probabilístico).
3. **Labels "Dato de ejemplo / Fase Beta"** siempre presentes — no simular que son datos reales 2026.
4. **Solo Lima distritos.** Nada nacional/presidencial/regional.
5. Contraste AA; verde nunca como texto sobre claro.

## Datos reales para poblar (usá EXACTAMENTE estos)

**43 distritos de Lima:** Ancón, Ate, Barranco, Breña, Carabayllo, Chaclacayo, Chorrillos, Cieneguilla, Comas, El Agustino, Independencia, Jesús María, La Molina, La Victoria, Lima (Cercado), Lince, Los Olivos, Lurigancho (Chosica), Lurín, Magdalena del Mar, Miraflores, Pachacámac, Pucusana, Pueblo Libre, Puente Piedra, Punta Hermosa, Punta Negra, Rímac, San Bartolo, San Borja, San Isidro, San Juan de Lurigancho, San Juan de Miraflores, San Luis, San Martín de Porres, San Miguel, Santa Anita, Santa María del Mar, Santa Rosa, Santiago de Surco, Surquillo, Villa El Salvador, Villa María del Triunfo.

**Distrito piloto con resultados = Miraflores** (1,284 votos, actualizado 17 jul 2026, indecisos 8.0%, blanco/nulo 3.0%). Candidatos + partido (color) + %:
| Candidato | Partido | Color | % |
|---|---|---|---|
| Carlos Canales Anchorena | Renovación Popular (RP) | #B22222 | 24.5 |
| María Rocío Cano Guerinoni | Podemos Perú (PP) | #00A99D | 18.2 |
| Alessandra Krause Alva | Avanza País (AVP) | #F58220 | 14.0 |
| Manuel Masías Oyanguren | Alianza para el Progreso (APP) | #ED1C24 | 11.3 |
| Ernesto Mendoza De La Puente | Somos Perú (SP) | #009444 | 9.0 |
| Sergio Meza Salazar | Acción Popular (AP) | #6EC6E8 | 6.5 |
| Diego Mora Olivares | Partido Morado (PM) | #6A1B9A | 4.0 |
| Sitza Romero Peralta | Fuerza Popular (FP) | #FF6B00 | 1.5 |

Los otros 42 distritos: estado vacío "Sondeo abierto".

## Entrega
Un solo archivo **HTML autocontenido** (podés usar Tailwind CDN + Google Fonts + FontAwesome para el mockup — luego se self-hostean). Responsive mobile-first. Navegación funcional entre Home ↔ detalle de distrito (JS/hash routing) + buscador modal operativo.
