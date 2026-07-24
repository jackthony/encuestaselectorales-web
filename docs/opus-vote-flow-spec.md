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

## Rutas canónicas

La URL canónica para Callao es:
- `/encuestas/region/callao-region`

Compatibilidad de legado:
- `/encuestas/region/callao` debe responder con `301` hacia la URL canónica.

Esto evita 404 para enlaces viejos, mantiene una sola URL pública y no duplica la lógica de la vista.

## Validaciones

- `php artisan migrate --seed` debe dejar el distrito visible.
- `/encuestas/district/carmen-de-la-legua-reynoso` debe abrir en local.
- `/encuestas/region/callao` debe redirigir a `/encuestas/region/callao-region`.
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
