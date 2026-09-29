-- Bondi license server: database setup (Phase 8)
--
-- Run once on Hostinger: hPanel → Databases → phpMyAdmin → pick the Bondi database → Import (or SQL)
-- → this file. Works on MySQL 8 and MariaDB 10.6+. Safe to run on an empty database only.
--
-- What it stores, and what it doesn't:
--   * Trials: an anonymous device ID (a SHA-256 hash made on the Mac; the server never sees the Mac's
--     real hardware ID) and when the trial started, so reinstalling doesn't restart it.
--   * Customers and licenses: the buyer's email and Paddle's IDs. Payment details stay with Paddle.
--   * Activations: which Macs use a license (hashed device ID and the Mac's name).
--   * Nothing about apps, usage or history. That never leaves the Mac.
--
-- License keys and activation tokens are stored only as SHA-256 hashes: a leaked database can't be
-- used to activate Bondi. A lost key is recovered by issuing a new one to the buyer's email; Macs that
-- are already activated keep working, because they hold an activation token, not the key.
--
-- All times are UTC.

SET NAMES utf8mb4;
SET time_zone = '+00:00';

-- Prices the app and website show, read from here rather than built into the app. The launch price
-- ends after `cap` licenses are sold at it; the license API then offers the next active tier.
CREATE TABLE price_tiers (
    tier            VARCHAR(20)   NOT NULL PRIMARY KEY,   -- 'launch', 'regular'
    paddle_price_id VARCHAR(64)   NULL,                   -- pri_… from Paddle; filled in after you create the prices
    amount_cents    INT UNSIGNED  NOT NULL,
    currency        CHAR(3)       NOT NULL DEFAULT 'USD',
    cap             INT UNSIGNED  NULL,                   -- licenses at this price; NULL = no limit
    sort_order      TINYINT       NOT NULL,
    active          TINYINT(1)    NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO price_tiers (tier, amount_cents, cap, sort_order) VALUES
    ('launch',   699, 250,  1),   -- $6.99 for the first 250 buyers (owner, 2026-09-29)
    ('regular', 2999, NULL, 2);   -- $29.99 after that

-- One row per Mac that has started a trial.
CREATE TABLE trials (
    device_hash   CHAR(64)     NOT NULL PRIMARY KEY,     -- SHA-256 made on the Mac
    started_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    last_seen_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    app_version   VARCHAR(20)  NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE customers (
    id                  INT UNSIGNED  NOT NULL AUTO_INCREMENT PRIMARY KEY,
    email               VARCHAR(254)  NOT NULL,           -- stored lower-case
    paddle_customer_id  VARCHAR(64)   NULL,               -- ctm_…
    created_at          DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_customers_email (email),
    UNIQUE KEY uq_customers_paddle (paddle_customer_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE licenses (
    id                     INT UNSIGNED     NOT NULL AUTO_INCREMENT PRIMARY KEY,
    customer_id            INT UNSIGNED     NOT NULL,
    key_hash               CHAR(64)         NOT NULL,     -- SHA-256 of the key; the key itself is only emailed
    key_last4              CHAR(4)          NOT NULL,     -- shown in Settings as ••••-1A2B
    plan                   VARCHAR(20)      NOT NULL DEFAULT 'full',
    price_tier             VARCHAR(20)      NOT NULL,     -- 'launch', 'regular' or 'comp' (given free)
    status                 ENUM('active', 'refunded', 'revoked') NOT NULL DEFAULT 'active',
    max_activations        TINYINT UNSIGNED NOT NULL DEFAULT 1,   -- Macs at once (1, owner 2026-09-29)
    paddle_transaction_id  VARCHAR(64)      NULL,         -- txn_…; NULL for comp licenses
    amount_cents           INT UNSIGNED     NULL,         -- what was paid, from Paddle
    currency               CHAR(3)          NULL,
    created_at             DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    status_changed_at      DATETIME         NULL,
    UNIQUE KEY uq_licenses_key (key_hash),
    UNIQUE KEY uq_licenses_transaction (paddle_transaction_id),  -- one license per payment, even if Paddle retries
    KEY ix_licenses_customer (customer_id),
    KEY ix_licenses_tier (price_tier, status),
    CONSTRAINT fk_licenses_customer FOREIGN KEY (customer_id) REFERENCES customers (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- One row per Mac per license. Deactivating sets deactivated_at; activating the same Mac again reuses
-- its row.
CREATE TABLE activations (
    id              INT UNSIGNED  NOT NULL AUTO_INCREMENT PRIMARY KEY,
    license_id      INT UNSIGNED  NOT NULL,
    device_hash     CHAR(64)      NOT NULL,
    device_name     VARCHAR(100)  NULL,                   -- "Noaman's MacBook Pro", for Manage Macs
    token_hash      CHAR(64)      NOT NULL,               -- SHA-256 of the token the Mac keeps
    activated_at    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    last_check_at   DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    deactivated_at  DATETIME      NULL,
    UNIQUE KEY uq_activations_device (license_id, device_hash),
    UNIQUE KEY uq_activations_token (token_hash),
    CONSTRAINT fk_activations_license FOREIGN KEY (license_id) REFERENCES licenses (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Every Paddle webhook, kept so a retried event is never processed twice and problems can be traced.
CREATE TABLE webhook_events (
    event_id      VARCHAR(64)  NOT NULL PRIMARY KEY,      -- evt_… from Paddle
    event_type    VARCHAR(64)  NOT NULL,                  -- transaction.completed, adjustment.updated, …
    occurred_at   DATETIME     NULL,
    received_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    processed_at  DATETIME     NULL,
    error         VARCHAR(500) NULL,
    payload       JSON         NOT NULL,
    KEY ix_webhook_events_type (event_type, received_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Emails sent (license key, recovery), for support and to slow down repeated recovery requests.
CREATE TABLE email_log (
    id          INT UNSIGNED  NOT NULL AUTO_INCREMENT PRIMARY KEY,
    email       VARCHAR(254)  NOT NULL,
    kind        VARCHAR(20)   NOT NULL,                   -- 'license', 'recovery'
    license_id  INT UNSIGNED  NULL,
    sent_at     DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY ix_email_log_recent (email, kind, sent_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Requests per caller per minute, to stop key guessing on the activate and recover endpoints.
CREATE TABLE rate_limits (
    bucket        VARCHAR(100)  NOT NULL,                 -- e.g. 'activate:<ip hash>'
    window_start  DATETIME      NOT NULL,
    hits          INT UNSIGNED  NOT NULL DEFAULT 0,
    PRIMARY KEY (bucket, window_start)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- The price to offer right now and how many launch licenses are left. The license API reads this to
-- pick the Paddle price for a new checkout. (A few checkouts started at the same moment can take the
-- launch count slightly past 250; every buyer still pays the price they were shown.)
CREATE VIEW current_offer AS
SELECT p.tier,
       p.paddle_price_id,
       p.amount_cents,
       p.currency,
       CASE WHEN p.cap IS NULL THEN NULL
            ELSE GREATEST(CAST(p.cap AS SIGNED) - (SELECT COUNT(*) FROM licenses l
                                                   WHERE l.price_tier = p.tier AND l.status = 'active'), 0)
       END AS remaining
FROM price_tiers p
WHERE p.active = 1
  AND (p.cap IS NULL OR (SELECT COUNT(*) FROM licenses l
                         WHERE l.price_tier = p.tier AND l.status = 'active') < p.cap)
ORDER BY p.sort_order
LIMIT 1;
