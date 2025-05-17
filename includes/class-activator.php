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

    // Groups table
    $table_name = $wpdb->prefix . 'cospend_groups';
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
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
    $table_name = $wpdb->prefix . 'cospend_members';
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
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
    $table_name = $wpdb->prefix . 'cospend_categories';
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            parent_id bigint(20),
            name varchar(255) NOT NULL,
            color varchar(7) DEFAULT '#000000',
            created_by bigint(20) NOT NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY parent_id (parent_id),
            KEY created_by (created_by),
            CONSTRAINT fk_category_parent FOREIGN KEY (parent_id) REFERENCES {$wpdb->prefix}cospend_categories(id) ON DELETE CASCADE
        ) $charset_collate;";
    dbDelta($sql);

    // Transactions table - supports individual, group, and P2P transactions
    $table_name = $wpdb->prefix . 'cospend_transactions';
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            group_id bigint(20),
            payer_id bigint(20) NOT NULL,
            category_id bigint(20),
            amount decimal(10,2) NOT NULL,
            description text,
            date date NOT NULL,
            type enum('income', 'expense') NOT NULL,
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
    $table_name = $wpdb->prefix . 'cospend_transaction_splits';
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            transaction_id bigint(20) NOT NULL,
            member_id bigint(20) NOT NULL,
            amount decimal(10,2) NOT NULL,
            is_paid tinyint(1) NOT NULL DEFAULT 0,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY transaction_id (transaction_id),
            KEY member_id (member_id)
        ) $charset_collate;";
    dbDelta($sql);

    // Transaction meta table - for additional transaction data
    $table_name = $wpdb->prefix . 'cospend_transaction_meta';
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            meta_id bigint(20) NOT NULL AUTO_INCREMENT,
            transaction_id bigint(20) NOT NULL,
            meta_key varchar(255) NOT NULL,
            meta_value longtext,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (meta_id),
            KEY transaction_id (transaction_id),
            KEY meta_key (meta_key(191))
        ) $charset_collate;";
    dbDelta($sql);

    // Avatars/Images table - for member, category, and tag images
    $table_name = $wpdb->prefix . 'cospend_images';
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            entity_type enum('member', 'category', 'tag', 'group') NOT NULL,
            entity_id bigint(20) NOT NULL,
            type enum('url', 'icon', 'svg') NOT NULL,
            content text NOT NULL,
            created_by bigint(20) NOT NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY entity_type (entity_type),
            KEY entity_id (entity_id),
            KEY created_by (created_by)
        ) $charset_collate;";
    dbDelta($sql);

    // Tags table
    $table_name = $wpdb->prefix . 'cospend_tags';
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
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
    $table_name = $wpdb->prefix . 'cospend_transaction_tags';
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            transaction_id bigint(20) NOT NULL,
            tag_id bigint(20) NOT NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (transaction_id, tag_id),
            KEY tag_id (tag_id)
        ) $charset_collate;";
    dbDelta($sql);

    // Group members table - many-to-many relationship between groups and members
    $table_name = $wpdb->prefix . 'cospend_group_members';
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            group_id bigint(20) NOT NULL,
            member_id bigint(20) NOT NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            can_edit boolean NOT NULL DEFAULT false,
            PRIMARY KEY  (id),
            UNIQUE KEY group_member (group_id, member_id),
            KEY group_id (group_id),
            KEY member_id (member_id),
            CONSTRAINT fk_group_member_group FOREIGN KEY (group_id) REFERENCES {$wpdb->prefix}cospend_groups(id) ON DELETE CASCADE,
            CONSTRAINT fk_group_member_member FOREIGN KEY (member_id) REFERENCES {$wpdb->prefix}cospend_members(id) ON DELETE CASCADE
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
