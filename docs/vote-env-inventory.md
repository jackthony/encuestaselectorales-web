# Inventario — `VOTE_TERRITORY_BOUNDS_JSON` + claves de voto (producción)

Estado a 2026-07-23. Repo no trackea `.env` (correcto, está en `.gitignore`), tampoco hay `.env.example` — este doc es la fuente de verdad de qué vars faltan antes de ir a producción.

## 1. Claves de voto (`config/vote.php`)

| Env var | Uso | Formato esperado | Estado |
|---|---|---|---|
| `VOTE_ENCRYPTION_KEY` | Cifra IP del votante (AES-256-GCM) — `AesGcmVotePrivacy::key()` | 32 bytes exactos. Acepta `base64:...` o raw. | No configurada (no hay `.env` en repo) |
| `VOTE_IP_HMAC_KEY` | HMAC-SHA256 de IP | ≥32 bytes | No configurada |
| `VOTE_DEVICE_HMAC_KEY` | HMAC-SHA256 de device token + fingerprint | ≥32 bytes | No configurada |
| `VOTE_ENCRYPTION_KEY_VERSION` | Versión guardada junto al voto (rotación futura) | int, default `1` | Default OK si no se rota |
| `VOTE_MAX_GPS_ACCURACY_METERS` | Rechaza GPS impreciso | int, default `100` | Default OK |

**Riesgo si falta en prod**: `AesGcmVotePrivacy::key()` (`app/Infrastructure/Security/AesGcmVotePrivacy.php:54-72`) sólo usa fallback derivado de `app.key` cuando `app()->environment(['local','testing'])`. En cualquier otro entorno (`production`, `staging`), key inválida o ausente → `RuntimeException: Invalid runtime key`. **El voto se rompe entero**, no degrada.

**Acción**: generar 3 secretos random de 32 bytes (`openssl rand -base64 32` → prefijo `base64:`) y setearlos en el entorno de producción antes del primer voto real. No reusar `APP_KEY`.

## 2. `VOTE_TERRITORY_BOUNDS_JSON`

Consumida en `config/vote.php:13-17` como configuración auxiliar. Hoy `ConfiguredGeographicValidator::contains()` ya no la usa para bloquear votos.

**Estado actual**: no requerida para que el flujo funcione en producción.

**Uso futuro**: si en algún momento queremos reactivar un geofence estricto por distrito, este JSON vuelve a ser la fuente adecuada.

## 3. Bloqueante de lanzamiento

Las claves sí son **hard blockers** de producción, no cosméticas:
- Sin claves → excepción dura en cada intento de voto.
- `VOTE_TERRITORY_BOUNDS_JSON` ya no bloquea el flujo de voto.

Ninguno de los dos está reservado a Codex (`RegisterVote`/`VoteController` sí lo están, pero configurar env vars no toca esos archivos).

## 4. Hallazgo post-merge (2026-07-23, merge `codex/fix-vote-flow` → `cleanup/second-pass`)

El `.env` local (no trackeado, no tocar por accidente al copiar a prod) tiene
`VOTE_MAX_GPS_ACCURACY_METERS=50000` — 500x el default de `config/vote.php`
(`100`). Es útil para bootstrap local (no requiere GPS real preciso para
probar), pero **si ese valor se copia tal cual a producción, la validación de
precisión GPS queda efectivamente desactivada** (tolera 50km de margen de
error). Confirmar explícitamente el valor de `VOTE_MAX_GPS_ACCURACY_METERS`
en el entorno de producción, no asumir que "no seteado = default seguro" —
si alguien clona el `.env` local por error, hereda el valor permisivo.

De paso, esto rompe `tests/Unit/Security/VoteInfrastructureTest.php::test_geographic_validator_allows_testing_without_bounds_configuration`
en local (`assertFalse($validator->contains(..., 500))` falla porque 500 <
50000 ya no dispara el rechazo por precisión) — el test depende del valor de
`.env` en vez de fijar `config(['vote.max_gps_accuracy_meters' => ...])`
explícitamente en el propio test. Frágil independientemente del valor de
prod; vale la pena que Codex lo blinde.

## 5. Pendiente / a decidir con el usuario

- Fuente de los polígonos/bounding boxes por distrito, sólo si algún día decidimos reactivar el geofence estricto (¿shapefile INEI, Geoportal, algo ya usado en `scope.blade.php` para el mapa?).
- ~~Dónde vive el env de producción~~ — confirmado: **Hostinger** (coincide con [[og-thumbnail-fase2-architecture]]: Business, `public_html`, sin root, 60 PHP workers compartidos entre 19 sitios). Hostinger compartido no tiene panel de "env vars" tipo Railway — el `.env` es un archivo real en el hosting, editado por File Manager/SFTP/SSH si el plan lo permite. Falta confirmar: ¿hay acceso SSH a esa cuenta, o solo File Manager? Quien suba las 3 claves + bounds tiene que escribir el `.env` de prod directamente, a mano, con cuidado de no pisarlo con el `.env` local (ver hallazgo de `VOTE_MAX_GPS_ACCURACY_METERS=50000` arriba — mismo riesgo aplica a las 3 claves si alguna vez se sincroniza el archivo entero por error).
- ¿Rotación de claves planeada para el día de elección? Si no, `VOTE_ENCRYPTION_KEY_VERSION=1` fijo alcanza.
