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

CREATE TABLE wp_cospend_accounts (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    wp_user_id BIGINT UNSIGNED NOT NULL,
    default_currency VARCHAR(3) DEFAULT 'USD',
    language VARCHAR(5) DEFAULT 'en',
    timezone VARCHAR(50) DEFAULT 'UTC',
    notification_preferences JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY unique_wp_user (wp_user_id),
    FOREIGN KEY (wp_user_id) REFERENCES wp_users(ID)
);

CREATE TABLE wp_cospend_avatars (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    type ENUM('image', 'icon', 'svg') NOT NULL,
    content TEXT NOT NULL,
    category ENUM('system', 'custom') DEFAULT 'custom',
    created_by BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    FOREIGN KEY (created_by) REFERENCES wp_users(ID)
);

CREATE TABLE wp_cospend_categories (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    avatar_id BIGINT UNSIGNED,
    color VARCHAR(7),
    is_system BOOLEAN DEFAULT FALSE,
    created_by BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    FOREIGN KEY (created_by) REFERENCES wp_users(ID),
    FOREIGN KEY (avatar_id) REFERENCES wp_cospend_avatars(id)
);

CREATE TABLE wp_cospend_tags (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    avatar_id BIGINT UNSIGNED,
    color VARCHAR(7),
    is_system BOOLEAN DEFAULT FALSE,
    created_by BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    FOREIGN KEY (created_by) REFERENCES wp_users(ID),
    FOREIGN KEY (avatar_id) REFERENCES wp_cospend_avatars(id)
);

CREATE TABLE wp_cospend_transaction_tags (
    transaction_id BIGINT UNSIGNED NOT NULL,
    tag_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (transaction_id, tag_id),
    FOREIGN KEY (transaction_id) REFERENCES wp_cospend_transactions(id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES wp_cospend_tags(id) ON DELETE RESTRICT
);

-- Custom Tables
CREATE TABLE wp_cospend_groups (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    avatar_id BIGINT UNSIGNED,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (id),
    FOREIGN KEY (created_by) REFERENCES wp_users(ID),
    FOREIGN KEY (avatar_id) REFERENCES wp_cospend_avatars(id)
);

CREATE TABLE wp_cospend_members (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    group_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255),
    avatar_id BIGINT UNSIGNED,
    wp_user_id BIGINT UNSIGNED DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    FOREIGN KEY (group_id) REFERENCES wp_cospend_groups(id),
    FOREIGN KEY (wp_user_id) REFERENCES wp_users(ID),
    FOREIGN KEY (avatar_id) REFERENCES wp_cospend_avatars(id)
);

CREATE TABLE wp_cospend_group_members (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    group_id BIGINT UNSIGNED NOT NULL,
    member_id BIGINT UNSIGNED NOT NULL,
    role ENUM('admin', 'member') DEFAULT 'member',
    joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    FOREIGN KEY (group_id) REFERENCES wp_cospend_groups(id),
    FOREIGN KEY (member_id) REFERENCES wp_cospend_members(id),
    UNIQUE KEY unique_group_member (group_id, member_id)
);

CREATE TABLE wp_cospend_transactions (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    group_id BIGINT UNSIGNED NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    description TEXT,
    date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by BIGINT UNSIGNED NOT NULL,
    category_id BIGINT UNSIGNED,
    currency VARCHAR(3) DEFAULT 'USD',
    PRIMARY KEY (id),
    FOREIGN KEY (group_id) REFERENCES wp_cospend_groups(id),
    FOREIGN KEY (created_by) REFERENCES wp_users(ID),
    FOREIGN KEY (category_id) REFERENCES wp_cospend_categories(id)
);

CREATE TABLE wp_cospend_transaction_splits (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    transaction_id BIGINT UNSIGNED NOT NULL,
    member_id BIGINT UNSIGNED NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    is_paid BOOLEAN DEFAULT FALSE,
    PRIMARY KEY (id),
    FOREIGN KEY (transaction_id) REFERENCES wp_cospend_transactions(id),
    FOREIGN KEY (member_id) REFERENCES wp_cospend_members(id)
); 