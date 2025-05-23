<?php

namespace WPCospend;

class Activator {
  /**
   * Activate the plugin.
   */
  public static function activate() {
    self::create_tables();
    self::create_roles();
    self::create_initial_members();
    self::create_system_defaults();
  }

  /**
   * Create the database tables.
   */
  private static function create_tables() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    $prefix = $wpdb->prefix;
    $table_groups = $prefix . 'cospend_groups';
    $table_members = $prefix . 'cospend_members';
    $table_categories = $prefix . 'cospend_categories';
    $table_accounts = $prefix . 'cospend_accounts';
    $table_transactions = $prefix . 'cospend_transactions';
    $table_transaction_splits = $prefix . 'cospend_transaction_splits';
    $table_transaction_meta = $prefix . 'cospend_transaction_meta';
    $table_repayments = $prefix . 'cospend_repayments';
    $table_images = $prefix . 'cospend_images';
    $table_tags = $prefix . 'cospend_tags';
    $table_transaction_tags = $prefix . 'cospend_transaction_tags';
    $table_group_members = $prefix . 'cospend_group_members';

    // Groups table
    $sql = "CREATE TABLE IF NOT EXISTS $table_groups (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            description text,
            currency varchar(3) DEFAULT 'INR',
            created_by bigint(20) NOT NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY created_by (created_by)
        ) $charset_collate;";
    dbDelta($sql);

    // Members table - generalized to support both WP users and external people
    $sql = "CREATE TABLE IF NOT EXISTS $table_members (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            wp_user_id bigint(20),
            name varchar(255) NOT NULL,
            created_by bigint(20) NOT NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY wp_user_id (wp_user_id),
            KEY created_by (created_by)
        ) $charset_collate;";
    dbDelta($sql);

    // Categories table - with parent-child support
    $sql = "CREATE TABLE IF NOT EXISTS $table_categories (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            parent_id bigint(20),
            name varchar(255) NOT NULL,
            color varchar(7) DEFAULT '#000000',
            created_by bigint(20) NOT NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            type ENUM('income', 'expense', 'transfer') NOT NULL DEFAULT 'expense',
            PRIMARY KEY  (id),
            KEY parent_id (parent_id),
            KEY created_by (created_by)
        ) $charset_collate;";
    dbDelta($sql);

    // Accounts table - for tracking money between users
    $sql = "CREATE TABLE IF NOT EXISTS $table_accounts (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            private_name varchar(255),
            description text,
            created_by bigint(20) NOT NULL,
            member_id bigint(20) NOT NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            is_default boolean NOT NULL DEFAULT false,
            visibility enum('private', 'friends', 'group') NOT NULL DEFAULT 'private',
            is_active boolean NOT NULL DEFAULT true,
            PRIMARY KEY  (id),
            KEY created_by (created_by),
            KEY member_id (member_id)
        ) $charset_collate;";
    dbDelta($sql);

    // Transactions table - supports individual, group, and P2P transactions
    $sql = "CREATE TABLE IF NOT EXISTS $table_transactions (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            group_id bigint(20),
            payer_id bigint(20) NOT NULL,
            category_id bigint(20),
            amount decimal(10,2) NOT NULL,
            description text,
            date date NOT NULL,
            type enum('income', 'expense', 'transfer') NOT NULL DEFAULT 'expense',
            transaction_type enum('group', 'p2p', 'personal') NOT NULL DEFAULT 'personal',
            created_by bigint(20) NOT NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY group_id (group_id),
            KEY payer_id (payer_id),
            KEY category_id (category_id),
            KEY created_by (created_by),
            KEY date (date)
        ) $charset_collate;";
    dbDelta($sql);

    // Transaction splits table - clean member-based tracking
    $sql = "CREATE TABLE IF NOT EXISTS $table_transaction_splits (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            transaction_id bigint(20) NOT NULL,
            member_id bigint(20) NOT NULL,
            amount decimal(10,2) NOT NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            from_account_id bigint(20),
            to_account_id bigint(20),
            PRIMARY KEY  (id),
            KEY transaction_id (transaction_id),
            KEY member_id (member_id),
            KEY from_account_id (from_account_id),
            KEY to_account_id (to_account_id)
        ) $charset_collate;";
    dbDelta($sql);

    // Transaction meta table - for additional transaction data
    $sql = "CREATE TABLE IF NOT EXISTS $table_transaction_meta (
            meta_id bigint(20) NOT NULL AUTO_INCREMENT,
            transaction_id bigint(20) NOT NULL,
            meta_key varchar(255) NOT NULL,
            meta_value longtext,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (meta_id),
            KEY transaction_id (transaction_id),
            KEY meta_key (meta_key(191)),
            UNIQUE KEY unique_meta (transaction_id, meta_key(191))
        ) $charset_collate;";
    dbDelta($sql);

    // Repayments table - for tracking repayments between users
    $sql = "CREATE TABLE IF NOT EXISTS $table_repayments (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            repayment_transaction_id bigint(20) NOT NULL,
            split_id bigint(20) NOT NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY repayment_transaction_id (repayment_transaction_id),
            KEY split_id (split_id)
        ) $charset_collate;";
    dbDelta($sql);

    // Avatars/Images table - for member, category, and tag images
    $sql = "CREATE TABLE IF NOT EXISTS $table_images (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            entity_type enum('member', 'category', 'tag', 'group', 'account') NOT NULL,
            entity_id bigint(20) NOT NULL,
            type enum('url', 'icon') NOT NULL,
            content text NOT NULL,
            created_by bigint(20) NOT NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY entity_unique (entity_type, entity_id),
            KEY entity_type (entity_type),
            KEY entity_id (entity_id),
            KEY created_by (created_by)
        ) $charset_collate;";
    dbDelta($sql);

    // Tags table
    $sql = "CREATE TABLE IF NOT EXISTS $table_tags (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            color varchar(7) DEFAULT '#000000',
            created_by bigint(20) NOT NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY created_by (created_by)
        ) $charset_collate;";
    dbDelta($sql);

    // Transaction tags table - many-to-many relationship
    $sql = "CREATE TABLE IF NOT EXISTS $table_transaction_tags (
            transaction_id bigint(20) NOT NULL,
            tag_id bigint(20) NOT NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (transaction_id, tag_id),
            KEY tag_id (tag_id)
        ) $charset_collate;";
    dbDelta($sql);

    // Group members table - many-to-many relationship between groups and members
    $sql = "CREATE TABLE IF NOT EXISTS $table_group_members (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            group_id bigint(20) NOT NULL,
            member_id bigint(20) NOT NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            can_edit boolean NOT NULL DEFAULT false,
            PRIMARY KEY  (id),
            UNIQUE KEY group_member (group_id, member_id),
            KEY group_id (group_id),
            KEY member_id (member_id)
        ) $charset_collate;";
    dbDelta($sql);
  }

  /**
   * Create custom roles.
   */
  private static function create_roles() {
    add_role(
      'cospend_manager',
      __('Cospend Manager', 'wp-cospend'),
      array(
        'read' => true,
        'manage_cospend' => true,
      )
    );
  }

  /**
   * Create initial members for existing users.
   */
  private static function create_initial_members() {
    require_once WP_COSPEND_PLUGIN_DIR . 'includes/class-member-manager.php';
    Member_Manager::create_members_for_existing_users();
  }

  /**
   * Create system default categories and tags.
   */
  private static function create_system_defaults() {
    require_once WP_COSPEND_PLUGIN_DIR . 'includes/class-system-defaults.php';
    System_Defaults::create_defaults();
  }
}
