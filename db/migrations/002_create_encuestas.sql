-- BL-13b: encuestas — one row per online survey round (tipo='online_propia')
-- or reserved for a future migration of campo_externa records (still static
-- in data/encuesta.json today — see openspec/changes/bl-13b-encuestas-rondas-schema/design.md).
--
-- distrito_id is validated against data/distrito.json at the application layer,
-- not a DB foreign key — same precedent as BL-13's ubigeo_votacion.
-- "Ronda" is not a separate table: each row IS one ronda, distinguished by its
-- own numero_ronda + date window within the same distrito_id.

CREATE TABLE IF NOT EXISTS encuestas (
    id                   CHAR(32)        NOT NULL,
    distrito_id          VARCHAR(64)     NOT NULL,
    nivel                ENUM('distrito','provincia','region') NOT NULL DEFAULT 'distrito',
    tipo                 ENUM('online_propia','campo_externa') NOT NULL,
    numero_ronda         TINYINT UNSIGNED NOT NULL DEFAULT 1,
    titulo               VARCHAR(255)    NOT NULL,
    fecha_apertura       DATETIME        NOT NULL,
    fecha_cierre         DATETIME        NOT NULL,
    estado_publicacion   ENUM('prueba','producción') NOT NULL DEFAULT 'prueba',
    created_at           DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    KEY idx_scope_activo (nivel, distrito_id, estado_publicacion, fecha_apertura, fecha_cierre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
