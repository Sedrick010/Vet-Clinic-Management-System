-- Make sure both timestamp columns exist
ALTER TABLE pets ADD COLUMN IF NOT EXISTS created_at TIMESTAMP NULL;
ALTER TABLE pets ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP NULL;

-- Set default values for timestamps if they're NULL
UPDATE pets SET created_at = NOW() WHERE created_at IS NULL;
UPDATE pets SET updated_at = NOW() WHERE updated_at IS NULL;

-- Make sure soft delete column exists
ALTER TABLE pets ADD COLUMN IF NOT EXISTS deleted_at TIMESTAMP NULL; 