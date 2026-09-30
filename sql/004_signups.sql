-- Bondi website: "Sign up for updates" (beta and launch news). Run after 001_schema.sql.
--
-- One row per email. Signing up again with the same email updates the name, address and country.
-- Country is the two-letter code (CA, US, IN): from the visitor's IP when the host or CDN adds it to
-- the request (country_source 'ip'), otherwise from the browser's time zone ('timezone'). The IP is
-- never sent to an outside lookup service.

SET NAMES utf8mb4;
SET time_zone = '+00:00';

CREATE TABLE signups (
    id              INT UNSIGNED  NOT NULL AUTO_INCREMENT PRIMARY KEY,
    email           VARCHAR(254)  NOT NULL,
    name            VARCHAR(100)  NOT NULL,
    ip              VARCHAR(45)   NOT NULL,          -- IPv4 or IPv6
    country         CHAR(2)       NULL,              -- ISO 3166 code; NULL when unknown
    country_source  ENUM('ip', 'timezone') NULL,
    time_zone       VARCHAR(64)   NULL,              -- as the browser reported it, e.g. America/Toronto
    created_at      DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME      NULL,
    UNIQUE KEY uq_signups_email (email),
    KEY ix_signups_created (created_at),
    KEY ix_signups_country (country)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
