-- BL-14 follow-up: speed up duplicate checks on browser_fingerprint.
-- Safe to run once on the live BL-13 schema.

ALTER TABLE votos_interactivos
    ADD KEY idx_ratelimit_browser (encuesta_id, browser_fingerprint, created_at);
