-- BL-14 follow-up: enforce one vote per encuesta per IP hash or device token.
-- This migration is intentionally stricter than the original BL-13 schema.
-- It reflects the live product requirement to block repeat voting from the
-- same connection or the same device.

DELETE v1
FROM votos_interactivos v1
JOIN votos_interactivos v2
  ON v1.encuesta_id = v2.encuesta_id
 AND v1.ip_hash = v2.ip_hash
 AND (
      v1.created_at > v2.created_at
      OR (v1.created_at = v2.created_at AND v1.id > v2.id)
 );

ALTER TABLE votos_interactivos
    ADD UNIQUE KEY uniq_vote_ip (encuesta_id, ip_hash),
    ADD UNIQUE KEY uniq_vote_device (encuesta_id, device_token);
