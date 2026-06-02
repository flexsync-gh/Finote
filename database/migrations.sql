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

-- Optional non-unique indexes for account/category management and transaction filters.
-- Review existing indexes before running these in production.
-- CREATE INDEX idx_accounts_userid_name ON accounts (userid, name);
-- CREATE INDEX idx_categories_userid_type_name ON categories (userid, type, name);
-- CREATE INDEX idx_transactions_userid_accountid ON transactions (userid, accountid);
-- CREATE INDEX idx_transactions_userid_categoryid ON transactions (userid, categoryid);
