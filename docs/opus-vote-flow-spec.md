# Opus Vote Flow Spec

## Idea

Cerrar el flujo de voto de punta a punta:
- elegir candidato,
- validar ubicación,
- registrar voto,
- refrescar el conteo en vivo,
- y mostrar el mismo snapshot de datos en local que en la publicación actual.

## Objetivos

- Registrar el voto sin recargar la página.
- Reflejar el conteo actualizado inmediatamente en la vista del territorio.
- Mostrar errores concretos según la causa real.
- Mantener el flujo de voto dentro de la arquitectura actual.
- Hacer que el entorno local arranque con datos reproducibles y visibles.

## Alcance

Incluye:
- modal de validación GPS,
- `POST /api/votes`,
- persistencia de `interactive_votes`,
- actualización en vivo del conteo parcial,
- bootstrap local con snapshot territorial y ronda activa,
- validaciones funcionales y de experiencia.

No incluye:
- miniatura social detallada,
- rediseño visual del home,
- panel administrativo,
- analytics,
- ni reescritura de la arquitectura.

## Decisión actual

La primera versión del flujo de voto será estrictamente territorial y presencial:
- solo se aceptan votos dentro del distrito validado por GPS y bounds configurados,
- si la ubicación actual queda fuera del ámbito, el voto se rechaza,
- no existe modo remoto en esta iteración.

## Decisión futura pendiente

La próxima semana se reevalúan estas casuísticas de producto para decidir si entran en una fase posterior:
- voto desde fuera del distrito por viaje,
- voto remoto con verificación alternativa,
- tolerancia a GPS impreciso,
- voto con geolocalización denegada,
- reintento seguro si falló la red,
- cambio de distrito o encuesta entre selección y envío,
- criterios para empates y orden en vivo del conteo,
- casos de doble pestaña, doble click y envío repetido.

Texto base para esa decisión futura:
`La primera versión del flujo de voto es estrictamente territorial: solo se aceptan votos dentro del distrito validado por GPS y bounds configurados. Los casos de voto remoto, viaje, geolocalización débil o excepciones operativas quedan fuera de alcance de esta iteración y se reevalúan la semana siguiente como decisión de producto.`

## Flujo

### 1. Selección

El usuario debe marcar una candidatura antes de continuar.

Si no hay selección:
- se detiene el flujo,
- se muestra un error de UI,
- no se intenta GPS ni API.

### 2. Validación GPS

El botón de votar abre el flujo de validación.

El navegador debe:
- pedir permiso de ubicación,
- obtener coordenadas,
- registrar precisión,
- y calcular tiempo de interacción.

Si falla la validación:
- se muestra una causa concreta,
- el usuario no llega al `POST /api/votes`.

### 3. Confirmación

Si la ubicación es válida:
- se muestra la confirmación final,
- el usuario pulsa `Registrar mi voto`,
- se envía la petición al backend.

### 4. Persistencia

La API debe:
- validar el request,
- resolver IP confiable,
- persistir `interactive_votes`,
- devolver `vote_id`,
- devolver el `result` actualizado de la ronda.

### 5. Conteo en vivo

Cuando el voto se guarda:
- el frontend emite `vote:registered`,
- la vista del territorio actualiza el total,
- las tarjetas de opciones refrescan votos y porcentajes,
- sin recarga.

## Estados de error

### Error de selección

Condición:
- no hay candidato seleccionado.

Respuesta:
- mensaje corto,
- no abrir GPS,
- no tocar backend.

### Error de GPS

Condiciones:
- permiso denegado,
- GPS no disponible,
- timeout,
- posición inválida.

Respuesta:
- título y cuerpo diferenciados por caso,
- no abrir el paso de confirmación.

### Error de backend

Condiciones:
- encuesta cerrada,
- voto duplicado,
- validación geográfica fallida,
- fallo de validación de red/conexión.

Respuesta:
- mensaje concreto según el `code` HTTP/API,
- conservar el modal como feedback visible,
- no emitir evento de conteo si el voto no quedó persistido.

## Datos locales

El entorno local debe mostrar el snapshot público actual como referencia funcional.

Mínimos del snapshot:
- territorio `070103` `Carmen de la Legua-Reynoso`,
- ronda activa publicada,
- total inicial en `0`,
- candidaturas visibles,
- conteo listo para subir tras votar.

El bootstrap local debe:
- cargar el mapa territorial desde `data/territories_ubigeo_map.json`,
- sembrar la ronda del distrito mostrado en producción,
- dejar el flujo operativo sin depender de inserts manuales.

## Validaciones

- `php artisan migrate --seed` debe dejar el distrito visible.
- `/encuestas/district/carmen-de-la-legua-reynoso` debe abrir en local.
- el modal debe mostrar mensajes distintos para GPS y backend.
- `POST /api/votes` debe devolver `201` al persistir.
- el conteo en pantalla debe actualizarse sin reload.

## Pruebas

### Backend

- `php artisan test --filter=Vote`
- persistencia de `interactive_votes`
- respuesta con `device_token`
- `result` actualizado en la respuesta

### Local data

- `php artisan db:seed`
- abrir la ruta del distrito
- confirmar que aparecen candidaturas reales del snapshot

### Frontend

- votar sin selección
- negar GPS
- aceptar GPS y confirmar voto
- verificar que el total sube sin recargar

## Criterio de aceptación

- El voto se guarda.
- El conteo sube en vivo.
- Los errores son específicos.
- La ruta local del distrito abre sin 500.
- El local reproduce el snapshot operativo actual.
