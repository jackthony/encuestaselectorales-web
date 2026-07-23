# Opus Product Plan

Alcance: definir la interfaz pública mínima del producto actual, sin noticias y sin tocar la otra arquitectura de vistas que está en carpeta separada.

## Objetivo

Mostrar la votación actual, hacerla compartible y permitir revisar el estado de cada encuesta sin depender del legado.

## Principios

- Primero diseño, luego integración.
- Mock data al inicio, backend después.
- Una sola línea visual.
- Sin noticias.
- Sin legacy visible en navegación ni pantallas.
- No mezclar la lógica de negocio con las vistas.
- No intervenir todavía la arquitectura alternativa de vistas que vive en otra carpeta.

## Pantallas

### 1. Home principal

Propósito:
- Ser la portada del producto.
- Mostrar el estado actual de las encuestas web.
- Llevar al detalle de una encuesta o territorio.
- Facilitar compartir el resultado actual.

Contenido:
- Hero con mensaje principal.
- Resumen de votos actuales.
- Ranking visible de opciones.
- CTA de compartir.
- Acceso a la encuesta activa.
- Estado vacío si no hay ronda activa.

### 2. Página de territorio

Propósito:
- Mostrar una encuesta por región, provincia o distrito.
- Mostrar la votación actual del territorio.
- Mostrar quién lidera.
- Permitir votar o revisar el estado de la ronda.

Contenido:
- Título del territorio.
- Breadcrumb simple.
- Estado de la ronda.
- Lista de opciones.
- Votos por opción.
- Total acumulado.
- Bloque de compartir.

### 3. Páginas legales

Propósito:
- Mantener la parte institucional mínima.

Contenido:
- Política editorial.
- Privacidad.
- Fuentes y correcciones.

## Navegación

### Header

Debe contener solo lo necesario:
- Marca del sitio.
- Acceso a la home.
- Acceso a la encuesta activa o al bloque principal de resultados.
- Acceso a territorios o búsqueda si se decide mantenerlo.
- CTA de compartir si cabe sin ruido.

No debe contener:
- Enlaces legacy.
- Estudios de campo.
- Noticias.
- Menús sobrantes.
- Páginas que ya no forman parte del flujo actual.

### Footer

Debe contener:
- Marca resumida.
- Enlaces legales.
- Fuentes y correcciones.
- Contacto básico.
- Texto de copyright o versión si aporta.

No debe contener:
- Navegación duplicada.
- Rutas muertas.
- Accesos a módulos obsoletos.

## Cartas y Bloques

### Hero

Debe comunicar:
- que hay votos actuales,
- qué territorio se está viendo,
- y qué acción sigue.

### Card de encuesta activa

Debe incluir:
- territorio,
- cargo,
- total de votos,
- líder actual,
- estado de la ronda,
- botón de ver detalle.

### Card de ranking

Debe mostrar:
- top 5 como mínimo,
- nombre del candidato,
- partido,
- votos,
- porcentaje,
- barra visual.

### Card de compartir

Debe servir como preview social:
- título corto,
- subtítulo territorial,
- total de votos,
- líder,
- top 5,
- marca del sitio.

### Card territorial

Debe mostrar:
- región, provincia o distrito,
- ubigeo,
- estado de la ronda,
- acceso al detalle.

### Estado vacío

Debe mostrar:
- que no hay ronda visible,
- que no hay datos suficientes,
- y qué se espera para habilitarla.

## Data mínima

Cada pantalla debe poder funcionar con:
- 1 encuesta activa,
- 1 territorio,
- 5 opciones visibles,
- votos distintos,
- 1 líder claro.

## Accesibilidad

### Estructura

- Un `main` por página.
- Un `h1` principal por vista.
- Jerarquía de `h2` y `h3` consistente.
- Breadcrumb cuando haya contexto territorial.

### Interacción

- Navegación por teclado completa.
- Focus visible claro.
- Botones con texto útil.
- Links con destino explícito.
- Modales o paneles solo si aportan valor real.

### Lectura y contraste

- Contraste suficiente en textos y badges.
- No depender solo del color para comunicar estado.
- Evitar texto decorativo sin valor semántico.

### Imágenes

- `alt` útil y corto.
- Fallback neutral si falta foto.
- No usar imágenes aleatorias.

### Responsive

- La portada debe funcionar en móvil y desktop.
- El ranking no debe romperse en pantallas pequeñas.
- El share preview debe seguir legible en móvil.

## Reglas de contenido

- No incluir noticias.
- No mezclar estudios de campo con el flujo principal.
- No mostrar legacy.
- No inventar territorios.
- No inventar votos.

## Reglas técnicas

- Controllers delgados.
- Servicios de aplicación para armar datos de vista.
- Factories o presenters para cards y textos compartibles.
- Repositorios/query objects solo para leer o consultar datos.
- Views solo para renderizar.

## Criterios de aceptación

- La home se entiende en menos de 5 segundos.
- Se ve quién lidera.
- Se puede compartir el resultado actual.
- La navegación no expone legado.
- La UI es consistente entre home y territorio.
- La accesibilidad básica no se rompe.

## Fuera de alcance

- Noticias.
- Backend nuevo.
- Panel administrativo.
- Migración de la otra arquitectura de vistas.
- Reescritura total del proyecto.
- Historial viejo que ya no aporta.

