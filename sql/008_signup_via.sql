-- Bondi website: which invite link a sign-up came from (for example a WhatsApp group). Run after
-- 005_signup_page.sql (phpMyAdmin → your database → Import). A visit that doesn't sign up is not recorded.
-- Until this runs, sign-ups still work; the link name is just left off.

ALTER TABLE signups
    ADD COLUMN via VARCHAR(40) NULL AFTER page,   -- short name from /w/<name>, e.g. whatsapp-mac-devs
    ADD KEY ix_signups_via (via);
