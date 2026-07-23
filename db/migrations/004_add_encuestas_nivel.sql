-- BL-13b follow-up: differentiate survey rounds by territorial level.
-- Adds nivel so the same slug can exist as distrito/provincia/region.

ALTER TABLE encuestas
    ADD COLUMN nivel ENUM('distrito','provincia','region') NOT NULL DEFAULT 'distrito' AFTER distrito_id,
    ADD KEY idx_scope_activo (nivel, distrito_id, estado_publicacion, fecha_apertura, fecha_cierre);

UPDATE encuestas
SET nivel = 'region'
WHERE distrito_id = 'callao';

UPDATE encuestas
SET nivel = 'distrito'
WHERE distrito_id = 'lima-cercado';
