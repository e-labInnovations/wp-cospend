<?php

if (!defined('ABSPATH')) {
  exit;
}

class WPCospend_DB {

  public static function create_tables() {
    global $wpdb;

    $charset_collate = $wpdb->get_charset_collate();

    $tables = "
            CREATE TABLE {$wpdb->prefix}wp_cospend_users (
                id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                display_name VARCHAR(250) NOT NULL,
                user_email VARCHAR(100) NOT NULL,
                wp_user_id INT(20) UNSIGNED UNIQUE,
                picture_id BIGINT(20) UNSIGNED
            ) $charset_collate;

            CREATE TABLE {$wpdb->prefix}wp_cospend_groups (
                id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(200) NOT NULL,
                created_at DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
                created_by INT(20) UNSIGNED NOT NULL,
                description VARCHAR(255) NOT NULL,
                picture_id INT(20) UNSIGNED NOT NULL,
                owner_id BIGINT(20) UNSIGNED NOT NULL
            ) $charset_collate;

            CREATE TABLE {$wpdb->prefix}wp_cospend_group_members (
                id INT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                group_id INT(20) UNSIGNED NOT NULL,
                user_id INT(20) UNSIGNED NOT NULL,
                FOREIGN KEY (group_id) REFERENCES {$wpdb->prefix}wp_cospend_groups(id),
                FOREIGN KEY (user_id) REFERENCES {$wpdb->prefix}wp_cospend_users(id)
            ) $charset_collate;

            CREATE TABLE {$wpdb->prefix}wp_cospend_expenses (
                id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                group_id BIGINT(20) UNSIGNED NOT NULL,
                payer_id BIGINT(20) UNSIGNED NOT NULL,
                amount DECIMAL(10, 2) NOT NULL DEFAULT 0,
                date DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
                note LONGTEXT NOT NULL,
                icon VARCHAR(200) NOT NULL,
                transaction_method_id BIGINT(20) UNSIGNED NOT NULL,
                FOREIGN KEY (group_id) REFERENCES {$wpdb->prefix}wp_cospend_groups(id),
                FOREIGN KEY (payer_id) REFERENCES {$wpdb->prefix}wp_cospend_users(id),
                FOREIGN KEY (transaction_method_id) REFERENCES {$wpdb->prefix}wp_cospend_transaction_methods(id)
            ) $charset_collate;

            CREATE TABLE {$wpdb->prefix}wp_cospend_transactions (
                id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                expense_id BIGINT(20) UNSIGNED NOT NULL,
                from_user_id BIGINT(20) UNSIGNED NOT NULL,
                to_user_id BIGINT(20) UNSIGNED NOT NULL,
                amount DECIMAL(10, 2) NOT NULL DEFAULT 0,
                date DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
                FOREIGN KEY (expense_id) REFERENCES {$wpdb->prefix}wp_cospend_expenses(id),
                FOREIGN KEY (from_user_id) REFERENCES {$wpdb->prefix}wp_cospend_users(id),
                FOREIGN KEY (to_user_id) REFERENCES {$wpdb->prefix}wp_cospend_users(id)
            ) $charset_collate;

            CREATE TABLE {$wpdb->prefix}wp_cospend_attachments (
                id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                expense_id BIGINT(20) UNSIGNED NOT NULL,
                wp_media_id BIGINT(20) UNSIGNED NOT NULL,
                FOREIGN KEY (expense_id) REFERENCES {$wpdb->prefix}wp_cospend_expenses(id)
            ) $charset_collate;

            CREATE TABLE {$wpdb->prefix}wp_cospend_transaction_methods (
                id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(200) NOT NULL,
                description VARCHAR(255) NOT NULL,
                icon VARCHAR(100) NOT NULL,
                picture_id INT(20) UNSIGNED
            ) $charset_collate;
        ";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($tables);
  }
}
