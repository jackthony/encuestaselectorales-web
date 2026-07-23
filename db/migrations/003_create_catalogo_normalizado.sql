-- BL-16: normalized electoral catalog for Hostinger/MySQL.
-- Execute after selecting the live database in phpMyAdmin.
-- No AUTO_INCREMENT is used. Every table uses deterministic CHAR(32) IDs.
-- source_system + source_key provide stable upsert anchors for future batches.

CREATE TABLE IF NOT EXISTS election_scopes (
    id                   CHAR(32)        NOT NULL,
    scope_uid            CHAR(32)        NOT NULL,
    source_system        VARCHAR(50)     NOT NULL,
    source_key           VARCHAR(120)    NOT NULL,
    territory_slug       VARCHAR(64)     NOT NULL,
    election_process_code VARCHAR(30)     NOT NULL,
    election_year        SMALLINT UNSIGNED NOT NULL,
    election_level       ENUM('REGIONAL','PROVINCIAL','DISTRITAL') NOT NULL,
    office_code          VARCHAR(80)     NOT NULL,
    office_name          VARCHAR(150)    NOT NULL,
    country_code         CHAR(2)         NOT NULL DEFAULT 'PE',
    country_name         VARCHAR(80)     NOT NULL DEFAULT 'PERU',
    region_ubigeo        VARCHAR(6)      NULL,
    region_name          VARCHAR(100)    NOT NULL,
    province_ubigeo      VARCHAR(6)      NULL,
    province_name        VARCHAR(100)    NULL,
    district_ubigeo      VARCHAR(6)      NULL,
    district_name        VARCHAR(100)    NULL,
    created_at           DATETIME        NULL,
    updated_at           DATETIME        NULL,

    PRIMARY KEY (id),
    UNIQUE KEY uq_election_scopes_uid (scope_uid),
    UNIQUE KEY uq_election_scopes_source (source_system, source_key),
    UNIQUE KEY uq_election_scopes_territory_level (territory_slug, election_level, office_code),
    KEY idx_scope_filter (
        election_process_code,
        election_level,
        region_name,
        province_name,
        district_name
    )
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS political_organizations (
    id                       CHAR(32)        NOT NULL,
    organization_uid         CHAR(32)        NOT NULL,
    source_system            VARCHAR(50)     NOT NULL,
    source_key               VARCHAR(120)    NOT NULL,
    jne_organization_id      INT UNSIGNED    NULL,
    organization_name        VARCHAR(255)    NOT NULL,
    organization_abbreviation VARCHAR(50)    NULL,
    organization_type        VARCHAR(50)     NULL,
    party_logo_url           TEXT            NULL,
    party_logo_local_path    VARCHAR(500)    NULL,
    organization_profile_url TEXT            NULL,
    legacy_party_logo_url    TEXT            NULL,
    created_at               DATETIME        NULL,
    updated_at               DATETIME        NULL,

    PRIMARY KEY (id),
    UNIQUE KEY uq_political_organizations_uid (organization_uid),
    UNIQUE KEY uq_political_organizations_source (source_system, source_key),
    UNIQUE KEY uq_political_organizations_jne_id (jne_organization_id),
    KEY idx_organization_name (organization_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS candidates (
    id                     CHAR(32)        NOT NULL,
    candidate_uid          CHAR(32)        NOT NULL,
    source_system          VARCHAR(50)     NOT NULL,
    source_key             VARCHAR(120)    NOT NULL,
    jne_candidate_id       VARCHAR(80)     NULL,
    candidate_full_name    VARCHAR(255)    NOT NULL,
    candidate_age          SMALLINT UNSIGNED NULL,
    candidate_photo_url    TEXT            NULL,
    candidate_photo_local_path VARCHAR(500) NULL,
    candidate_profile_url  TEXT            NULL,
    created_at             DATETIME        NULL,
    updated_at             DATETIME        NULL,

    PRIMARY KEY (id),
    UNIQUE KEY uq_candidates_uid (candidate_uid),
    UNIQUE KEY uq_candidates_source (source_system, source_key),
    UNIQUE KEY uq_candidates_jne_id (jne_candidate_id),
    KEY idx_candidate_name (candidate_full_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS candidacies (
    id                  CHAR(32)        NOT NULL,
    candidacy_uid       CHAR(32)        NOT NULL,
    source_system       VARCHAR(50)     NOT NULL,
    source_key          VARCHAR(180)    NOT NULL,
    scope_id            CHAR(32)        NOT NULL,
    organization_id     CHAR(32)        NOT NULL,
    candidate_id        CHAR(32)        NOT NULL,
    candidacy_status    VARCHAR(50)     NOT NULL,
    ballot_order        INT UNSIGNED    NULL,
    source_file         VARCHAR(255)    NULL,
    source_row          INT UNSIGNED    NULL,
    source_url          TEXT            NULL,
    retrieved_at        DATETIME        NULL,
    data_quality_status VARCHAR(120)    NOT NULL,
    notes               TEXT            NULL,
    created_at          DATETIME        NULL,
    updated_at          DATETIME        NULL,

    PRIMARY KEY (id),
    UNIQUE KEY uq_candidacies_uid (candidacy_uid),
    UNIQUE KEY uq_candidacies_source (source_system, source_key),
    UNIQUE KEY uq_candidacy_relation (scope_id, organization_id, candidate_id),
    CONSTRAINT fk_candidacies_scope
      FOREIGN KEY (scope_id) REFERENCES election_scopes(id),
    CONSTRAINT fk_candidacies_organization
      FOREIGN KEY (organization_id) REFERENCES political_organizations(id),
    CONSTRAINT fk_candidacies_candidate
      FOREIGN KEY (candidate_id) REFERENCES candidates(id),
    KEY idx_candidacy_status (candidacy_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS polls (
    id               CHAR(32)        NOT NULL,
    poll_code        VARCHAR(80)     NOT NULL,
    poll_name        VARCHAR(255)    NOT NULL,
    result_type      ENUM('POLL','OFFICIAL','SIMULATION') NOT NULL DEFAULT 'POLL',
    scope_id         CHAR(32)        NOT NULL,
    poll_start_date  DATE            NULL,
    poll_end_date    DATE            NULL,
    measured_at      DATETIME        NOT NULL,
    sample_size      INT UNSIGNED    NULL,
    source_system    VARCHAR(50)     NOT NULL,
    source_key       VARCHAR(120)    NULL,
    source_url       TEXT            NULL,
    notes            TEXT            NULL,
    created_at       DATETIME        NULL,
    updated_at       DATETIME        NULL,

    PRIMARY KEY (id),
    UNIQUE KEY uq_polls_code (poll_code),
    CONSTRAINT fk_polls_scope
      FOREIGN KEY (scope_id) REFERENCES election_scopes(id),
    KEY idx_polls_scope_date (scope_id, measured_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS poll_results (
    id               CHAR(32)        NOT NULL,
    result_uid       CHAR(32)        NOT NULL,
    poll_id          CHAR(32)        NOT NULL,
    candidacy_id     CHAR(32)        NOT NULL,
    votes            BIGINT UNSIGNED NULL,
    percentage      DECIMAL(8,5)    NULL,
    rank_position    INT UNSIGNED    NULL,
    progress_percent DECIMAL(6,3)    NULL,
    created_at       DATETIME        NULL,
    updated_at       DATETIME        NULL,

    PRIMARY KEY (id),
    UNIQUE KEY uq_poll_results_uid (result_uid),
    UNIQUE KEY uq_poll_candidacy (poll_id, candidacy_id),
    CONSTRAINT fk_poll_results_poll
      FOREIGN KEY (poll_id) REFERENCES polls(id),
    CONSTRAINT fk_poll_results_candidacy
      FOREIGN KEY (candidacy_id) REFERENCES candidacies(id),
    KEY idx_results_ranking (poll_id, rank_position, percentage)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
