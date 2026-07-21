-- BL-13: votos_interactivos — corrected from docs/reference/db-schema-draft.sql.
-- See openspec/changes/bl-13-db-antihack-schema/design.md for the two defects fixed here
-- (UNIQUE KEY on ip_hash/browser_fingerprint blocking CGNAT; gps_lng DECIMAL(10,8) overflow).
--
-- ubigeo_votacion is validated against data/distrito.json at the application layer,
-- not a DB foreign key — data/distrito.json is the source of truth for districts.

CREATE TABLE IF NOT EXISTS votos_interactivos (
    id                   CHAR(32)        NOT NULL,
    encuesta_id          CHAR(32)        NOT NULL,
    ubigeo_votacion      VARCHAR(64)     NOT NULL,
    candidato_id         VARCHAR(64)     NULL,
    tipo_voto            ENUM('candidato','blanco','viciado') NOT NULL,
    gps_lat              DECIMAL(10,8)   NOT NULL,
    gps_lng              DECIMAL(11,8)   NOT NULL,
    gps_accuracy_meters  SMALLINT UNSIGNED NULL,
    interaction_time_ms  INT UNSIGNED    NULL,
    ip_hash              CHAR(64)        NOT NULL,
    ip_cifrada           VARBINARY(255)  NOT NULL,
    ip_iv                VARBINARY(16)   NOT NULL,
    ip_tag               VARBINARY(16)   NOT NULL,
    device_token         CHAR(64)        NOT NULL,
    browser_fingerprint  CHAR(64)        NOT NULL,
    user_agent           VARCHAR(512)    NULL,
    cf_pais              CHAR(2)         NULL,
    trust_score          TINYINT UNSIGNED NULL,
    estado               ENUM('valido','sospechoso','anulado') NOT NULL DEFAULT 'valido',
    created_at           DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    -- Rate limiting still uses plain indexes. The live product now also enforces
    -- one vote per encuesta per connection/device with unique constraints.
    KEY idx_ratelimit_ip     (encuesta_id, ip_hash, created_at),
    KEY idx_ratelimit_device (encuesta_id, device_token, created_at),
    KEY idx_geo_heatmap      (ubigeo_votacion, gps_lat, gps_lng),
    UNIQUE KEY uniq_vote_ip   (encuesta_id, ip_hash),
    UNIQUE KEY uniq_vote_device (encuesta_id, device_token)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
