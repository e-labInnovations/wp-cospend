-- WordPress Core Tables
CREATE TABLE wp_users (
    ID BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_login VARCHAR(60) NOT NULL,
    user_pass VARCHAR(255) NOT NULL,
    user_nicename VARCHAR(50) NOT NULL,
    user_email VARCHAR(100) NOT NULL,
    user_url VARCHAR(100) NOT NULL,
    user_registered DATETIME NOT NULL,
    user_activation_key VARCHAR(255) NOT NULL,
    user_status INT NOT NULL,
    display_name VARCHAR(250) NOT NULL,
    PRIMARY KEY (ID),
    UNIQUE KEY user_login (user_login),
    UNIQUE KEY user_email (user_email)
);

CREATE TABLE wp_usermeta (
    umeta_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NOT NULL,
    meta_key VARCHAR(255),
    meta_value LONGTEXT,
    PRIMARY KEY (umeta_id),
    KEY user_id (user_id),
    KEY meta_key (meta_key(191))
);

CREATE TABLE IF NOT EXISTS cospend_groups (
    id BIGINT(20) NOT NULL AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    currency VARCHAR(3) DEFAULT 'INR',
    created_by BIGINT(20) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY created_by (created_by)
);

CREATE TABLE IF NOT EXISTS cospend_members (
    id BIGINT(20) NOT NULL AUTO_INCREMENT,
    wp_user_id BIGINT(20),
    name VARCHAR(255) NOT NULL,
    created_by BIGINT(20) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY wp_user_id (wp_user_id),
    KEY created_by (created_by)
);

CREATE TABLE IF NOT EXISTS cospend_categories (
    id BIGINT(20) NOT NULL AUTO_INCREMENT,
    parent_id BIGINT(20),
    name VARCHAR(255) NOT NULL,
    color VARCHAR(7) DEFAULT '#000000',
    created_by BIGINT(20) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    type ENUM('income', 'expense', 'transfer') NOT NULL DEFAULT 'expense',
    PRIMARY KEY (id),
    KEY parent_id (parent_id),
    KEY created_by (created_by)
);

CREATE TABLE IF NOT EXISTS cospend_accounts (
    id BIGINT(20) NOT NULL AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    private_name VARCHAR(255),
    description TEXT,
    created_by BIGINT(20) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    is_default BOOLEAN NOT NULL DEFAULT FALSE,
    visibility ENUM('private', 'friends', 'group') NOT NULL DEFAULT 'private',
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    is_virtual BOOLEAN NOT NULL DEFAULT FALSE,
    PRIMARY KEY (id),
    KEY created_by (created_by)
);

CREATE TABLE IF NOT EXISTS cospend_transactions (
    id BIGINT(20) NOT NULL AUTO_INCREMENT,
    group_id BIGINT(20),
    payer_id BIGINT(20) NOT NULL,
    category_id BIGINT(20),
    amount DECIMAL(10,2) NOT NULL,
    description TEXT,
    date DATE NOT NULL,
    type ENUM('income', 'expense', 'transfer') NOT NULL DEFAULT 'expense',
    transaction_type ENUM('group', 'p2p', 'personal') NOT NULL DEFAULT 'personal',
    created_by BIGINT(20) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY group_id (group_id),
    KEY payer_id (payer_id),
    KEY category_id (category_id),
    KEY created_by (created_by),
    KEY date (date)
);

CREATE TABLE IF NOT EXISTS cospend_transaction_splits (
    id BIGINT(20) NOT NULL AUTO_INCREMENT,
    transaction_id BIGINT(20) NOT NULL,
    member_id BIGINT(20) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    from_account_id BIGINT(20),
    to_account_id BIGINT(20),
    PRIMARY KEY (id),
    KEY transaction_id (transaction_id),
    KEY member_id (member_id),
    KEY from_account_id (from_account_id),
    KEY to_account_id (to_account_id)
);

CREATE TABLE IF NOT EXISTS cospend_transaction_meta (
    meta_id BIGINT(20) NOT NULL AUTO_INCREMENT,
    transaction_id BIGINT(20) NOT NULL,
    meta_key VARCHAR(255) NOT NULL,
    meta_value LONGTEXT,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (meta_id),
    KEY transaction_id (transaction_id),
    KEY meta_key (meta_key(191)),
    UNIQUE KEY unique_meta (transaction_id, meta_key(191))
);

CREATE TABLE IF NOT EXISTS cospend_repayments (
    id BIGINT(20) NOT NULL AUTO_INCREMENT,
    repayment_transaction_id BIGINT(20) NOT NULL,
    split_id BIGINT(20) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY repayment_transaction_id (repayment_transaction_id),
    KEY split_id (split_id)
);

CREATE TABLE IF NOT EXISTS cospend_images (
    id BIGINT(20) NOT NULL AUTO_INCREMENT,
    entity_type ENUM('member', 'category', 'tag', 'group', 'account') NOT NULL,
    entity_id BIGINT(20) NOT NULL,
    type ENUM('url', 'icon') NOT NULL,
    content TEXT NOT NULL,
    created_by BIGINT(20) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY entity_unique (entity_type, entity_id),
    KEY entity_type (entity_type),
    KEY entity_id (entity_id),
    KEY created_by (created_by)
);

CREATE TABLE IF NOT EXISTS cospend_tags (
    id BIGINT(20) NOT NULL AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    color VARCHAR(7) DEFAULT '#000000',
    created_by BIGINT(20) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY created_by (created_by)
);

CREATE TABLE IF NOT EXISTS cospend_transaction_tags (
    transaction_id BIGINT(20) NOT NULL,
    tag_id BIGINT(20) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (transaction_id, tag_id),
    KEY tag_id (tag_id)
);

CREATE TABLE IF NOT EXISTS cospend_group_members (
    id BIGINT(20) NOT NULL AUTO_INCREMENT,
    group_id BIGINT(20) NOT NULL,
    member_id BIGINT(20) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    can_edit BOOLEAN NOT NULL DEFAULT FALSE,
    PRIMARY KEY (id),
    UNIQUE KEY group_member (group_id, member_id),
    KEY group_id (group_id),
    KEY member_id (member_id)
);