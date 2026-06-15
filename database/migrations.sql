-- Run these after importing web2.sql if your local database does not already include them.

ALTER TABLE users
    ADD COLUMN IF NOT EXISTS profile_photo VARCHAR(255) DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

ALTER TABLE transactions
    ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

CREATE TABLE IF NOT EXISTS budgets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    userid INT NOT NULL,
    categoryid INT NOT NULL,
    amount DECIMAL(15,2) NOT NULL DEFAULT 0,
    month TINYINT NOT NULL,
    year SMALLINT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_budgets_user_category_period (userid, categoryid, month, year)
);

ALTER TABLE budgets
    ADD COLUMN IF NOT EXISTS userid INT NOT NULL,
    ADD COLUMN IF NOT EXISTS categoryid INT NOT NULL,
    ADD COLUMN IF NOT EXISTS amount DECIMAL(15,2) NOT NULL DEFAULT 0,
    ADD COLUMN IF NOT EXISTS month TINYINT NOT NULL,
    ADD COLUMN IF NOT EXISTS year SMALLINT NOT NULL,
    ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

CREATE TABLE IF NOT EXISTS saving_goals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    userid INT NOT NULL,
    name VARCHAR(120) NOT NULL,
    target_amount DECIMAL(15,2) NOT NULL DEFAULT 0,
    current_amount DECIMAL(15,2) NOT NULL DEFAULT 0,
    target_date DATE NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

ALTER TABLE saving_goals
    ADD COLUMN IF NOT EXISTS userid INT NOT NULL,
    ADD COLUMN IF NOT EXISTS name VARCHAR(120) NOT NULL,
    ADD COLUMN IF NOT EXISTS target_amount DECIMAL(15,2) NOT NULL DEFAULT 0,
    ADD COLUMN IF NOT EXISTS current_amount DECIMAL(15,2) NOT NULL DEFAULT 0,
    ADD COLUMN IF NOT EXISTS target_date DATE NOT NULL,
    ADD COLUMN IF NOT EXISTS status VARCHAR(20) NOT NULL DEFAULT 'active',
    ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

CREATE TABLE IF NOT EXISTS saving_transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    goalid INT NOT NULL,
    userid INT NOT NULL,
    amount DECIMAL(15,2) NOT NULL DEFAULT 0,
    type VARCHAR(20) NOT NULL,
    note TEXT NULL,
    transaction_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

ALTER TABLE saving_transactions
    ADD COLUMN IF NOT EXISTS saving_goal_id INT NULL,
    ADD COLUMN IF NOT EXISTS goalid INT NOT NULL,
    ADD COLUMN IF NOT EXISTS userid INT NOT NULL,
    ADD COLUMN IF NOT EXISTS amount DECIMAL(15,2) NOT NULL DEFAULT 0,
    ADD COLUMN IF NOT EXISTS type VARCHAR(20) NOT NULL,
    ADD COLUMN IF NOT EXISTS note TEXT NULL,
    ADD COLUMN IF NOT EXISTS transaction_date DATE NOT NULL,
    ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;

ALTER TABLE saving_transactions
    MODIFY COLUMN saving_goal_id INT NULL;

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
