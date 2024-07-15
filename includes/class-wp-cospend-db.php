<?php

if (!defined('ABSPATH')) {
    exit;
}

class WPCospend_DB {
    public static function create_tables() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        $table_members       = $wpdb->prefix . 'cospend_members';
        $table_groups        = $wpdb->prefix . 'cospend_groups';
        $table_group_members = $wpdb->prefix . 'cospend_group_members';
        $table_expenses      = $wpdb->prefix . 'cospend_expenses';
        $table_transactions  = $wpdb->prefix . 'cospend_transactions';
        $table_attachments   = $wpdb->prefix . 'cospend_attachments';
        $table_payment_modes = $wpdb->prefix . 'cospend_payment_modes';

        $queries = "
            CREATE TABLE $table_members (
                id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                display_name VARCHAR(250) NOT NULL,
                member_email VARCHAR(100),
                wp_member_id INT(20) UNSIGNED UNIQUE,
                picture_id BIGINT(20) UNSIGNED,
                PRIMARY KEY (id)
            ) $charset_collate;

            CREATE TABLE $table_groups (
                id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                name VARCHAR(200) NOT NULL,
                created_at DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
                created_by INT(20) UNSIGNED NOT NULL,
                description VARCHAR(255),
                picture_id INT(20) UNSIGNED,
                owner_id BIGINT(20) UNSIGNED NOT NULL,
                PRIMARY KEY (id)
            ) $charset_collate;

            CREATE TABLE $table_group_members (
                id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                group_id BIGINT(20) UNSIGNED NOT NULL,
                member_id BIGINT(20) UNSIGNED NOT NULL,
                PRIMARY KEY (id)
            ) $charset_collate;

            CREATE TABLE $table_expenses (
                id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                group_id BIGINT(20) UNSIGNED NOT NULL,
                payer_id BIGINT(20) UNSIGNED NOT NULL,
                amount DECIMAL(10, 2) NOT NULL DEFAULT 0,
                date DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
                note LONGTEXT,
                category VARCHAR(200) NOT NULL,
                payment_mode_id BIGINT(20) UNSIGNED NOT NULL,
                PRIMARY KEY (id)
            ) $charset_collate;

            CREATE TABLE $table_transactions (
                id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                expense_id BIGINT(20) UNSIGNED NOT NULL,
                from_member_id BIGINT(20) UNSIGNED NOT NULL,
                to_member_id BIGINT(20) UNSIGNED NOT NULL,
                amount DECIMAL(10, 2) NOT NULL DEFAULT 0,
                date DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
                payment_mode_id BIGINT(20) UNSIGNED NOT NULL,
                PRIMARY KEY (id)
            ) $charset_collate;

            CREATE TABLE $table_attachments (
                id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                expense_id BIGINT(20) UNSIGNED NOT NULL,
                wp_media_id BIGINT(20) UNSIGNED NOT NULL,
                PRIMARY KEY (id)
            ) $charset_collate;

            CREATE TABLE $table_payment_modes (
                id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                name VARCHAR(200) NOT NULL,
                description VARCHAR(255),
                picture_id INT(20) UNSIGNED,
                group_id BIGINT(20) UNSIGNED NOT NULL,
                PRIMARY KEY (id)
            ) $charset_collate;
        ";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($queries);
    }
}
