-- Add created_at column if it doesn't exist
ALTER TABLE pets ADD COLUMN IF NOT EXISTS created_at TIMESTAMP NULL;

-- Add updated_at column if it doesn't exist
ALTER TABLE pets ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP NULL;

-- Set default values for timestamps
UPDATE pets SET created_at = NOW(), updated_at = NOW() WHERE created_at IS NULL OR updated_at IS NULL; 