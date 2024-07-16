<?php

if (!defined('ABSPATH')) {
    exit;
}

class WPCospend_DB {
    private $table_members;
    private $table_groups;
    private $table_group_members;
    private $table_expenses;
    private $table_transactions;
    private $table_attachments;
    private $table_payment_modes;

    public function __construct() {
        global $wpdb;
        $this->table_members       = $wpdb->prefix . 'cospend_members';
        $this->table_groups        = $wpdb->prefix . 'cospend_groups';
        $this->table_group_members = $wpdb->prefix . 'cospend_group_members';
        $this->table_expenses      = $wpdb->prefix . 'cospend_expenses';
        $this->table_transactions  = $wpdb->prefix . 'cospend_transactions';
        $this->table_attachments   = $wpdb->prefix . 'cospend_attachments';
        $this->table_payment_modes = $wpdb->prefix . 'cospend_payment_modes';
    }

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
                wp_user_id BIGINT(20) UNSIGNED UNIQUE,
                picture_id BIGINT(20) UNSIGNED,
                created_by BIGINT(20) UNSIGNED NOT NULL,
                PRIMARY KEY (id)
            ) $charset_collate;

            CREATE TABLE $table_groups (
                id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                name VARCHAR(200) NOT NULL,
                created_at DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
                created_by BIGINT(20) UNSIGNED NOT NULL,
                description VARCHAR(255),
                picture_id BIGINT(20) UNSIGNED,
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
                picture_id BIGINT(20) UNSIGNED,
                group_id BIGINT(20) UNSIGNED NOT NULL,
                PRIMARY KEY (id)
            ) $charset_collate;
        ";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($queries);
    }

    /**
     * Get all members.
     *
     * @return array|WP_Error An array of member objects on success, or WP_Error on failure.
     */
    public function get_all_members() {
        global $wpdb;

        $members = $wpdb->get_results("SELECT * FROM $this->table_members");

        if (null === $members) {
            return new WP_Error('members_not_found', 'No members found', array('status' => 404));
        }

        return $members;
    }

    /**
     * Check if a member with the given wp_user_id exists.
     *
     * @param int $wp_user_id The wp_user_id to check.
     * @return bool True if the member exists, false otherwise.
     */
    public function member_exists($wp_user_id) {
        global $wpdb;

        $exists = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $this->table_members WHERE wp_user_id = %d",
            $wp_user_id
        ));

        return $exists > 0;
    }

    /**
     * Get a member by wp_user_id.
     *
     * @param int $wp_user_id The wp_user_id to retrieve the member.
     * @return object|WP_Error The member object on success, or WP_Error on failure.
     */
    public function get_member_by_wp_user_id($wp_user_id) {
        global $wpdb;

        $member = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $this->table_members WHERE wp_user_id = %d",
            $wp_user_id
        ));

        if (null === $member) {
            return new WP_Error('member_not_found', 'Member not found', array('status' => 404));
        }

        return $member;
    }

    /**
     * Insert a new member into the database.
     *
     * @param array $data Member data.
     * @return int|WP_Error The new member ID on success, or WP_Error on failure.
     */
    public function insert_member($data) {
        global $wpdb;

        $inserted = $wpdb->insert(
            $this->table_members,
            $data,
            array(
                '%s', // display_name
                '%s', // member_email
                '%d', // wp_user_id
                '%d'  // picture_id
            )
        );

        if (false === $inserted) {
            return new WP_Error('db_insert_error', 'Could not insert member into the database', array('status' => 500));
        }

        return $wpdb->insert_id;
    }
}
