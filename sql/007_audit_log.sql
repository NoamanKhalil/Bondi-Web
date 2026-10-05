-- Bondi admin: a record of who signed in to the admin page (and who tried), and what was changed there.
-- Run after 006_privacy.sql (phpMyAdmin → your database → Import). Until this runs, the admin's Activity page
-- says so and nothing is recorded; everything else keeps working.
--
-- What it keeps: the time, what happened ("Revoked license #12"), and the IP address and country it came from.
-- Never an email address or a password; license and sign-up numbers only. Rows older than a year are removed
-- as new ones are added.

SET NAMES utf8mb4;
SET time_zone = '+00:00';

CREATE TABLE audit_log (
    id          INT UNSIGNED  NOT NULL AUTO_INCREMENT PRIMARY KEY,
    created_at  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    event       VARCHAR(30)   NOT NULL,                 -- sign_in, sign_in_failed, revoke, email_send, …
    detail      VARCHAR(300)  NULL,                     -- what happened, in words
    ip          VARCHAR(45)   NULL,
    country     CHAR(2)       NULL,                     -- from the IP address, on our own server
    KEY ix_audit_created (created_at),
    KEY ix_audit_event (event)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
