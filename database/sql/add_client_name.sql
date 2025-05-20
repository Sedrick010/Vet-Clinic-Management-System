-- Add client_name column if it doesn't exist
ALTER TABLE appointments ADD COLUMN client_name VARCHAR(255) AFTER id;

-- Remove foreign key constraints if they exist
SET FOREIGN_KEY_CHECKS = 0;

-- Drop pet_id and client_id columns if they exist
ALTER TABLE appointments DROP COLUMN pet_id, DROP COLUMN client_id;

-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1; 