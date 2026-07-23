-- MySQL 8+ / MariaDB moderno
CREATE DATABASE IF NOT EXISTS electoral_polls
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE electoral_polls;

CREATE TABLE IF NOT EXISTS election_scopes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    scope_uid VARCHAR(32) NOT NULL UNIQUE,
    election_process_code VARCHAR(30) NOT NULL,
    election_year SMALLINT UNSIGNED NOT NULL,
    election_level ENUM('REGIONAL','PROVINCIAL','DISTRITAL') NOT NULL,
    office_code VARCHAR(80) NOT NULL,
    office_name VARCHAR(150) NOT NULL,
    country_code CHAR(2) NOT NULL DEFAULT 'PE',
    country_name VARCHAR(80) NOT NULL DEFAULT 'PERU',
    region_ubigeo VARCHAR(2) NULL,
    region_name VARCHAR(100) NOT NULL,
    province_ubigeo VARCHAR(4) NULL,
    province_name VARCHAR(100) NULL,
    district_ubigeo VARCHAR(6) NULL,
    district_name VARCHAR(100) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX idx_scope_filter (
        election_process_code,
        election_level,
        region_name,
        province_name,
        district_name
    )
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS political_organizations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    organization_uid VARCHAR(32) NOT NULL UNIQUE,
    jne_organization_id INT UNSIGNED NULL UNIQUE,
    organization_name VARCHAR(255) NOT NULL,
    organization_abbreviation VARCHAR(50) NULL,
    organization_type VARCHAR(50) NULL,
    party_logo_url TEXT NULL,
    party_logo_local_path VARCHAR(500) NULL,
    organization_profile_url TEXT NULL,
    legacy_party_logo_url TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX idx_organization_name (organization_name)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS candidates (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    candidate_uid VARCHAR(32) NOT NULL UNIQUE,
    jne_candidate_id VARCHAR(80) NULL UNIQUE,
    candidate_full_name VARCHAR(255) NOT NULL,
    candidate_age SMALLINT UNSIGNED NULL,
    candidate_photo_url TEXT NULL,
    candidate_photo_local_path VARCHAR(500) NULL,
    candidate_profile_url TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX idx_candidate_name (candidate_full_name)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS candidacies (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    candidacy_uid VARCHAR(32) NOT NULL UNIQUE,
    scope_id BIGINT UNSIGNED NOT NULL,
    organization_id BIGINT UNSIGNED NOT NULL,
    candidate_id BIGINT UNSIGNED NOT NULL,
    candidacy_status VARCHAR(50) NOT NULL,
    ballot_order INT UNSIGNED NULL,
    source_system VARCHAR(50) NOT NULL,
    source_file VARCHAR(255) NULL,
    source_row INT UNSIGNED NULL,
    source_url TEXT NULL,
    retrieved_at DATETIME NULL,
    data_quality_status VARCHAR(120) NOT NULL,
    notes TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    CONSTRAINT fk_candidacies_scope
      FOREIGN KEY (scope_id) REFERENCES election_scopes(id),
    CONSTRAINT fk_candidacies_organization
      FOREIGN KEY (organization_id) REFERENCES political_organizations(id),
    CONSTRAINT fk_candidacies_candidate
      FOREIGN KEY (candidate_id) REFERENCES candidates(id),
    UNIQUE KEY uq_candidacy_relation (
        scope_id, organization_id, candidate_id
    ),
    INDEX idx_candidacy_status (candidacy_status)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS polls (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    poll_code VARCHAR(80) NOT NULL UNIQUE,
    poll_name VARCHAR(255) NOT NULL,
    result_type ENUM('POLL','OFFICIAL','SIMULATION') NOT NULL DEFAULT 'POLL',
    scope_id BIGINT UNSIGNED NOT NULL,
    poll_start_date DATE NULL,
    poll_end_date DATE NULL,
    measured_at DATETIME NOT NULL,
    sample_size INT UNSIGNED NULL,
    source_system VARCHAR(50) NOT NULL,
    source_url TEXT NULL,
    notes TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    CONSTRAINT fk_polls_scope
      FOREIGN KEY (scope_id) REFERENCES election_scopes(id),
    INDEX idx_polls_scope_date (scope_id, measured_at)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS poll_results (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    result_uid VARCHAR(32) NOT NULL UNIQUE,
    poll_id BIGINT UNSIGNED NOT NULL,
    candidacy_id BIGINT UNSIGNED NOT NULL,
    votes BIGINT UNSIGNED NULL,
    percentage DECIMAL(8,5) NULL,
    rank_position INT UNSIGNED NULL,
    progress_percent DECIMAL(6,3) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    CONSTRAINT fk_poll_results_poll
      FOREIGN KEY (poll_id) REFERENCES polls(id),
    CONSTRAINT fk_poll_results_candidacy
      FOREIGN KEY (candidacy_id) REFERENCES candidacies(id),
    UNIQUE KEY uq_poll_candidacy (poll_id, candidacy_id),
    INDEX idx_results_ranking (poll_id, rank_position, percentage)
) ENGINE=InnoDB;
