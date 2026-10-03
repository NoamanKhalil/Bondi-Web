-- Bondi website: unsubscribing, "Your data" (see and download it, delete it) and a record of each request.
-- Run after 005_signup_page.sql (phpMyAdmin → your database → Import). Until this runs, those pages say
-- they're unavailable and point to support@; everything else keeps working.

SET NAMES utf8mb4;
SET time_zone = '+00:00';

-- When someone unsubscribed. Every send skips them; signing up again on the site clears it.
ALTER TABLE signups
    ADD COLUMN unsubscribed_at DATETIME NULL AFTER page,
    ADD KEY ix_signups_unsubscribed (unsubscribed_at);

-- One-time links emailed by the "Your data" page, to prove the request comes from that inbox.
-- Only the link's hash is kept; rows go once used or a day after they expire.
CREATE TABLE data_links (
    id          INT UNSIGNED  NOT NULL AUTO_INCREMENT PRIMARY KEY,
    token_hash  CHAR(64)      NOT NULL,
    email       VARCHAR(254)  NOT NULL,
    created_at  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    expires_at  DATETIME      NOT NULL,
    used_at     DATETIME      NULL,
    UNIQUE KEY uq_data_links_token (token_hash),
    KEY ix_data_links_expires (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Proof that a request was honoured (GDPR accountability), kept 3 years. Never the email itself: a keyed
-- fingerprint (HMAC with the server's secret) that can't be turned back into the address.
CREATE TABLE data_requests (
    id            INT UNSIGNED  NOT NULL AUTO_INCREMENT PRIMARY KEY,
    code          VARCHAR(20)   NOT NULL,                       -- the confirmation number, e.g. BR-7K3QX-M2PZ
    kind          ENUM('delete', 'unsubscribe') NOT NULL,
    channel       ENUM('web', 'email_link', 'admin') NOT NULL,  -- the Your data page, an email's link, or the admin page
    email_hmac    CHAR(64)      NOT NULL,
    requested_at  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    completed_at  DATETIME      NULL,
    removed       VARCHAR(255)  NULL,                           -- what was deleted, e.g. "sign-up, emails sent"
    kept          VARCHAR(255)  NULL,                           -- what was kept and why, e.g. "purchase record (tax law)"
    UNIQUE KEY uq_data_requests_code (code),
    KEY ix_data_requests_email (email_hmac),
    KEY ix_data_requests_requested (requested_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
