-- TABLA PRINCIPAL DE VOTOS (Optimizada para MySQL/MariaDB en Hostinger)

CREATE TABLE IF NOT EXISTS votos_interactivos (
    -- 1. IDENTIDAD SEGURA (Anti-Scraping / Anti-IDOR)
    id CHAR(32) PRIMARY KEY, -- Hash criptográfico aleatorio, reemplaza al AUTO_INCREMENT
    
    -- 2. DECISIÓN ELECTORAL
    encuesta_id VARCHAR(64) NOT NULL,
    ubigeo_votacion VARCHAR(6) NOT NULL, -- El distrito por el que está votando (Catálogo JNE)
    candidato_id INT NULL, -- NULL significa Blanco/Viciado
    
    -- 3. CARTOGRAFÍA EXACTA (Inteligencia Electoral)
    gps_lat DECIMAL(10, 8) NOT NULL, -- Precisión a nivel de calle (1.1 milímetros)
    gps_lng DECIMAL(10, 8) NOT NULL,
    gps_accuracy_meters INT NOT NULL, -- Margen de error (Si es > 1000m, es sospechoso)
    is_out_of_district BOOLEAN DEFAULT FALSE, -- ¿Está físicamente en otro distrito al votar?
    
    -- 4. SEGURIDAD Y RED (El triple escudo antifraude)
    ip_cifrada VARCHAR(255) NOT NULL, -- IP Real cifrada con AES-256 (Solo tú puedes leerla con tu llave secreta)
    ip_hash CHAR(64) NOT NULL, -- Para bloqueos automáticos rápidos
    browser_fingerprint CHAR(64) NOT NULL, -- Huella única del celular/PC
    device_token CHAR(64) NOT NULL, -- Cookie inyectada por el servidor
    
    -- 5. METADATOS Y COMPORTAMIENTO (Filtros contra Bots)
    interaction_time_ms INT NULL, -- Tiempo desde que abrió el popup hasta que votó (Detecta scripts rápidos)
    cf_pais CHAR(2) NULL, -- País detectado por Cloudflare (Ej: 'PE')
    user_agent VARCHAR(255) NULL, -- Modelo de celular/navegador
    
    -- 6. AUDITORÍA
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- REGLAS DE NEGOCIO (Bloqueo automático de doble voto en la misma encuesta)
    UNIQUE KEY uniq_vote_ip (encuesta_id, ubigeo_votacion, ip_hash),
    UNIQUE KEY uniq_vote_device (encuesta_id, ubigeo_votacion, browser_fingerprint)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Índices para analítica rápida (Tus mapas de calor cargarán en milisegundos)
CREATE INDEX idx_geo_heatmap ON votos_interactivos(ubigeo_votacion, gps_lat, gps_lng);
CREATE INDEX idx_fecha ON votos_interactivos(created_at);