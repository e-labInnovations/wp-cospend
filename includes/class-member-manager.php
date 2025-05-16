<?php

namespace WPCospend;

class Member_Manager {
  /**
   * Initialize the member manager.
   */
  public static function init() {
    // Hook into user registration only
    add_action('user_register', array(__CLASS__, 'create_member_for_user'));
  }

  /**
   * Create members for all existing WordPress users.
   * Called during plugin activation.
   */
  public static function create_members_for_existing_users() {
    $users = get_users(array('fields' => array('ID', 'display_name')));

    foreach ($users as $user) {
      self::create_member_for_user($user->ID);
    }
  }

  /**
   * Create a member entry for a WordPress user.
   *
   * @param int $user_id WordPress user ID
   * @return int|false The member ID if created, false otherwise
   */
  public static function create_member_for_user($user_id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'cospend_members';

    // Check if member already exists for this user
    $existing_member = $wpdb->get_row($wpdb->prepare(
      "SELECT id FROM $table_name WHERE wp_user_id = %d AND created_by = %d",
      $user_id,
      $user_id
    ));

    if ($existing_member) {
      return $existing_member->id;
    }

    // Get user data
    $user = get_userdata($user_id);
    if (!$user) {
      return false;
    }

    // Insert new member
    $result = $wpdb->insert(
      $table_name,
      array(
        'wp_user_id' => $user_id,
        'name' => $user->display_name,
        'created_by' => $user_id,
        'created_at' => current_time('mysql'),
        'updated_at' => current_time('mysql'),
      ),
      array('%d', '%s', '%d', '%s', '%s')
    );

    return $result ? $wpdb->insert_id : false;
  }

  /**
   * Create a new member with optional WordPress user linking.
   *
   * @param string $name Member name/alias
   * @param int $created_by User ID who created this member
   * @param int|null $wp_user_id Optional WordPress user ID to link
   * @return int|false The member ID if created, false otherwise
   */
  public static function create_member($name, $created_by, $wp_user_id = null) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'cospend_members';

    // If wp_user_id is provided, verify it exists
    if ($wp_user_id && !get_userdata($wp_user_id)) {
      return false;
    }

    $result = $wpdb->insert(
      $table_name,
      array(
        'wp_user_id' => $wp_user_id,
        'name' => $name,
        'created_by' => $created_by,
        'created_at' => current_time('mysql'),
        'updated_at' => current_time('mysql'),
      ),
      array('%d', '%s', '%d', '%s', '%s')
    );

    return $result ? $wpdb->insert_id : false;
  }

  /**
   * Link an existing member to a WordPress user.
   *
   * @param int $member_id Member ID to link
   * @param int $wp_user_id WordPress user ID to link to
   * @return bool True if linked successfully, false otherwise
   */
  public static function link_member_to_user($member_id, $wp_user_id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'cospend_members';

    // Verify user exists
    if (!get_userdata($wp_user_id)) {
      return false;
    }

    $result = $wpdb->update(
      $table_name,
      array(
        'wp_user_id' => $wp_user_id,
        'updated_at' => current_time('mysql'),
      ),
      array('id' => $member_id),
      array('%d', '%s'),
      array('%d')
    );

    return $result !== false;
  }

  /**
   * Get member display name for the current user.
   *
   * @param object $member Member object from database
   * @return string Formatted display name
   */
  public static function get_member_display_name($member) {
    $display_name = $member->name;

    if ($member->wp_user_id) {
      $user = get_userdata($member->wp_user_id);
      if ($user) {
        $display_name .= ' (' . $user->display_name . ')';
      }
    }

    return $display_name;
  }

  /**
   * Find members by email (for linking suggestions).
   *
   * @param string $email Email to search for
   * @return array Array of matching WordPress users
   */
  public static function find_users_by_email($email) {
    $users = get_users(array(
      'search' => $email,
      'search_columns' => array('user_email'),
      'fields' => array('ID', 'display_name', 'user_email'),
    ));

    return $users;
  }
}
