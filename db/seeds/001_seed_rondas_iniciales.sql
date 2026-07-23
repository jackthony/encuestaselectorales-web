-- BL-13b initial production seed.
-- Execute after db/migrations/002_create_encuestas.sql in phpMyAdmin.
-- These rows are deterministic and idempotent, so the seed can be re-run.

INSERT INTO encuestas (
    id,
    distrito_id,
    nivel,
    tipo,
    numero_ronda,
    titulo,
    fecha_apertura,
    fecha_cierre,
    estado_publicacion
)
VALUES
(
    LOWER(LEFT(SHA2('encuestas:callao:1', 256), 32)),
    'callao',
    'region',
    'online_propia',
    1,
    'Encuesta web activa de la Región Callao',
    '2026-07-21 00:00:00',
    '2026-08-05 23:59:59',
    'producción'
),
(
    LOWER(LEFT(SHA2('encuestas:lima-cercado:1', 256), 32)),
    'lima',
    'provincia',
    'online_propia',
    1,
    'Encuesta web activa de la Provincia Lima',
    '2026-07-21 00:00:00',
    '2026-08-05 23:59:59',
    'producción'
)
ON DUPLICATE KEY UPDATE
    distrito_id = VALUES(distrito_id),
    nivel = VALUES(nivel),
    tipo = VALUES(tipo),
    numero_ronda = VALUES(numero_ronda),
    titulo = VALUES(titulo),
    fecha_apertura = VALUES(fecha_apertura),
    fecha_cierre = VALUES(fecha_cierre),
    estado_publicacion = VALUES(estado_publicacion);
