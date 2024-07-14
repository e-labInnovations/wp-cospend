<?php

if (!defined('ABSPATH')) {
    exit;
}

class WPCospend_DB {
    public static function create_tables() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        $table_users                = $wpdb->prefix . 'cospend_users';
        $table_groups               = $wpdb->prefix . 'cospend_groups';
        $table_group_members        = $wpdb->prefix . 'cospend_group_members';
        $table_expenses             = $wpdb->prefix . 'cospend_expenses';
        $table_transactions         = $wpdb->prefix . 'cospend_transactions';
        $table_attachments          = $wpdb->prefix . 'cospend_attachments';
        $table_transaction_methods  = $wpdb->prefix . 'cospend_transaction_methods';


        $tables = "
            CREATE TABLE $table_users (
                id BIGINT(20) UNSIGNED AUTO_INCREMENT,
                display_name VARCHAR(250) NOT NULL,
                user_email VARCHAR(100) NOT NULL,
                wp_user_id INT(20) UNSIGNED UNIQUE,
                picture_id BIGINT(20) UNSIGNED
            ) $charset_collate;

            CREATE TABLE $table_groups (
                id BIGINT(20) UNSIGNED AUTO_INCREMENT,
                name VARCHAR(200) NOT NULL,
                created_at DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
                created_by INT(20) UNSIGNED NOT NULL,
                description VARCHAR(255) NOT NULL,
                picture_id INT(20) UNSIGNED NOT NULL,
                owner_id BIGINT(20) UNSIGNED NOT NULL
            ) $charset_collate;

            CREATE TABLE $table_group_members (
                id INT(20) UNSIGNED AUTO_INCREMENT,
                group_id INT(20) UNSIGNED NOT NULL,
                user_id INT(20) UNSIGNED NOT NULL
            ) $charset_collate;

            CREATE TABLE $table_expenses (
                id BIGINT(20) UNSIGNED AUTO_INCREMENT,
                group_id BIGINT(20) UNSIGNED NOT NULL,
                payer_id BIGINT(20) UNSIGNED NOT NULL,
                amount DECIMAL(10, 2) NOT NULL DEFAULT 0,
                date DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
                note LONGTEXT NOT NULL,
                icon VARCHAR(200) NOT NULL,
                transaction_method_id BIGINT(20) UNSIGNED NOT NULL
            ) $charset_collate;

            CREATE TABLE $table_transactions (
                id BIGINT(20) UNSIGNED AUTO_INCREMENT,
                expense_id BIGINT(20) UNSIGNED NOT NULL,
                from_user_id BIGINT(20) UNSIGNED NOT NULL,
                to_user_id BIGINT(20) UNSIGNED NOT NULL,
                amount DECIMAL(10, 2) NOT NULL DEFAULT 0,
                date DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00'
            ) $charset_collate;

            CREATE TABLE $table_attachments (
                id BIGINT(20) UNSIGNED AUTO_INCREMENT,
                expense_id BIGINT(20) UNSIGNED NOT NULL,
                wp_media_id BIGINT(20) UNSIGNED NOT NULL
            ) $charset_collate;

            CREATE TABLE $table_transaction_methods (
                id BIGINT(20) UNSIGNED AUTO_INCREMENT,
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