-- Bondi license server: checkout hand-off (Phase 8). Run after 001_schema.sql.
--
-- When Bondi opens the checkout it gets a one-time claim secret; the buy page passes only its hash to
-- Paddle. When Paddle confirms the payment, the new license is linked to the claim, and Bondi (asking
-- with the secret) activates itself without the user typing the key. The key is kept here in plain
-- text only until Bondi collects it, and at most 2 days; after that only its hash remains.

SET NAMES utf8mb4;
SET time_zone = '+00:00';

CREATE TABLE checkout_claims (
    id                     INT UNSIGNED  NOT NULL AUTO_INCREMENT PRIMARY KEY,
    claim_hash             CHAR(64)      NOT NULL,        -- SHA-256 of the secret Bondi holds
    device_hash            CHAR(64)      NOT NULL,
    device_name            VARCHAR(100)  NULL,
    created_at             DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    paddle_transaction_id  VARCHAR(64)   NULL,
    license_id             INT UNSIGNED  NULL,
    key_plain              VARCHAR(40)   NULL,            -- cleared when collected or after 2 days
    claimed_at             DATETIME      NULL,
    UNIQUE KEY uq_checkout_claims_hash (claim_hash),
    KEY ix_checkout_claims_created (created_at),
    CONSTRAINT fk_checkout_claims_license FOREIGN KEY (license_id) REFERENCES licenses (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
