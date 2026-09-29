-- Only if you ran 001_schema.sql before 2026-09-29's change: one Mac per license (owner decision).
-- A fresh setup with the current 001_schema.sql already has this; running it again does no harm.
ALTER TABLE licenses MODIFY max_activations TINYINT UNSIGNED NOT NULL DEFAULT 1;
UPDATE licenses SET max_activations = 1;
