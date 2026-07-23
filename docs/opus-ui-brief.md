# Opus UI Brief

Objetivo inmediato: diseñar primero la interfaz, con `mock data`, sin depender aún del backend final.

## Prioridad

1. Mostrar el estado actual de votos.
2. Hacer que ese estado se pueda compartir.
3. Preparar una miniatura social reutilizable para WhatsApp, X y LinkedIn.
4. Mantener la paleta y la línea visual actual del proyecto.

## Lo que debe verse primero

- Ranking de votos actuales.
- Top 5 candidatos o partidos, como mínimo.
- Nombre visible de candidato y partido.
- Votos actuales por cada opción.
- Porcentaje relativo por opción.
- Total de votos de la encuesta.
- Lider actual.
- Bloque de compartir visible y claro.

## Lo que no entra en esta fase

- Backend nuevo.
- Persistencia real de votos.
- Historial viejo que ya no importa.
- Legacy data.
- Flujos administrativos.

## Regla visual

- Primero diseño, luego integración.
- Usar datos mockeados al principio.
- La pantalla debe verse creíble aunque todavía no exista la data real final.
- No romper la paleta ni el estilo ya existente.

## Pantalla principal

La home debe priorizar:

- encuesta activa actual
- votos acumulados en vivo
- CTA de compartir resultado
- acceso claro al detalle de encuesta

## Tarjeta de share

Debe funcionar como preview social y mostrar:

- título de la encuesta
- subtítulo de ubicación
- total de votos
- top 5 opciones
- líder actual
- marca visual del sitio

## Miniatura social

Debe servir para:

- WhatsApp
- X
- LinkedIn

Debe verse bien en formato preview y no depender de texto largo.

## Data mock mínima

Usar una estructura falsa con:

- 1 encuesta activa
- 5 opciones visibles
- votos distintos para que haya ranking
- 1 líder claro
- 1 territorio

## Criterio de éxito

- Se entiende rápido quién va ganando.
- Se puede compartir el estado actual.
- La miniatura queda útil para redes.
- La UI se ve mejor antes de tocar backend.
