-- Bondi website: which page someone signed up on, so beta-page sign-ups can be told apart. Run after
-- 004_signups.sql (phpMyAdmin → your database → Import). Everyone already on the list came from the homepage.
-- Until this runs, sign-ups still work; they just aren't marked.

ALTER TABLE signups
    ADD COLUMN page VARCHAR(20) NOT NULL DEFAULT 'home' AFTER time_zone,   -- 'home' or 'beta'
    ADD KEY ix_signups_page (page);
