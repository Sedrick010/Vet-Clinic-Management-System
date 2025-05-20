-- Add updated_at column only (created_at already exists)
ALTER TABLE pets ADD COLUMN updated_at TIMESTAMP NULL;

-- Set default values for updated_at
UPDATE pets SET updated_at = NOW() WHERE updated_at IS NULL; 