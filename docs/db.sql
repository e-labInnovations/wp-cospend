CREATE TABLE wp_cospend_members (
    id BIGINT(20) UNSIGNED AUTO_INCREMENT,
    display_name VARCHAR(250) NOT NULL,
    member_email VARCHAR(100),
    wp_user_id INT(20) UNSIGNED UNIQUE,
    picture_id BIGINT(20) UNSIGNED,
    PRIMARY KEY id(id)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci;

CREATE TABLE wp_cospend_groups (
    id BIGINT(20) UNSIGNED AUTO_INCREMENT,
    name VARCHAR(200) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
    created_by INT(20) UNSIGNED NOT NULL,
    description VARCHAR(255),
    picture_id INT(20) UNSIGNED,
    owner_id BIGINT(20) UNSIGNED NOT NULL,
    PRIMARY KEY id(id)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci;

CREATE TABLE wp_cospend_group_members (
    id INT(20) UNSIGNED AUTO_INCREMENT,
    group_id INT(20) UNSIGNED NOT NULL,
    user_id INT(20) UNSIGNED NOT NULL,
    PRIMARY KEY id(id),
    FOREIGN KEY (group_id) REFERENCES wp_cospend_groups(id),
    FOREIGN KEY (user_id) REFERENCES wp_cospend_members(id)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci;

CREATE TABLE wp_cospend_expenses (
    id BIGINT(20) UNSIGNED AUTO_INCREMENT,
    group_id BIGINT(20) UNSIGNED NOT NULL,
    payer_id BIGINT(20) UNSIGNED NOT NULL,
    amount DECIMAL(10, 2) NOT NULL DEFAULT 0,
    added_at DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
    note LONGTEXT,
    icon VARCHAR(200) NOT NULL,
    transaction_method_id BIGINT(20) UNSIGNED NOT NULL,
    PRIMARY KEY id(id),
    FOREIGN KEY (group_id) REFERENCES wp_cospend_groups(id),
    FOREIGN KEY (payer_id) REFERENCES wp_cospend_members(id),
    FOREIGN KEY (transaction_method_id) REFERENCES wp_cospend_transaction_methods(id)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci;

CREATE TABLE wp_cospend_transactions (
    id BIGINT(20) UNSIGNED AUTO_INCREMENT,
    expense_id BIGINT(20) UNSIGNED NOT NULL,
    from_member_id BIGINT(20) UNSIGNED NOT NULL,
    to_member_id BIGINT(20) UNSIGNED NOT NULL,
    amount DECIMAL(10, 2) NOT NULL DEFAULT 0,
    added_at DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
    transaction_method BIGINT(20) UNSIGNED NOT NULL,
    PRIMARY KEY id(id),
    FOREIGN KEY (expense_id) REFERENCES wp_cospend_expenses(id),
    FOREIGN KEY (from_member_id) REFERENCES wp_cospend_members(id),
    FOREIGN KEY (to_member_id) REFERENCES wp_cospend_members(id)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci;

CREATE TABLE wp_cospend_attachments (
    id BIGINT(20) UNSIGNED AUTO_INCREMENT,
    expense_id BIGINT(20) UNSIGNED NOT NULL,
    wp_media_id BIGINT(20) UNSIGNED NOT NULL,
    PRIMARY KEY id(id),
    FOREIGN KEY (expense_id) REFERENCES wp_cospend_expenses(id)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci;

CREATE TABLE wp_cospend_transaction_methods (
    id BIGINT(20) UNSIGNED AUTO_INCREMENT,
    name VARCHAR(200) NOT NULL,
    description VARCHAR(255),
    icon VARCHAR(100) NOT NULL,
    picture_id INT(20) UNSIGNED,
    group_id BIGINT(20) UNSIGNED NOT NULL,
    PRIMARY KEY id(id),
    FOREIGN KEY (group_id) REFERENCES wp_cospend_groups(id)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci;