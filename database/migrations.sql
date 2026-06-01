-- Run these after importing web2.sql if your local database does not already include them.

ALTER TABLE users
    ADD COLUMN IF NOT EXISTS profile_photo VARCHAR(255) DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

ALTER TABLE transactions
    ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

-- Existing seed users in web2.sql include plaintext passwords.
-- The updated login code will rehash a matching plaintext password on first successful login.
-- Optional manual reset example:
-- UPDATE users SET password = '$2y$10$exampleReplaceWithPasswordHash' WHERE id = 1;
