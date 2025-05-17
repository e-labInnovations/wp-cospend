<?php

namespace WPCospend;

class Member_Manager {
  /**
   * Initialize the member manager.
   */
  public static function init() {
    // Add hooks for member management
    add_action('user_register', array(__CLASS__, 'create_member_for_user'));
    add_action('delete_user', array(__CLASS__, 'delete_member_for_user'));
  }

  /**
   * Create a member for a new WordPress user.
   *
   * @param int $user_id The WordPress user ID
   */
  public static function create_member_for_user($user_id) {
    $user = get_userdata($user_id);
    if (!$user) {
      return;
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'cospend_members';

    // Check if member already exists
    $existing = $wpdb->get_var($wpdb->prepare(
      "SELECT id FROM $table_name WHERE wp_user_id = %d",
      $user_id
    ));

    if ($existing) {
      return;
    }

    // Create new member with the same user ID for both wp_user_id and created_by
    $wpdb->insert(
      $table_name,
      array(
        'wp_user_id' => $user_id,
        'name' => $user->display_name,
        'created_by' => $user_id, // Use the same user ID
        'created_at' => current_time('mysql'),
        'updated_at' => current_time('mysql'),
      ),
      array('%d', '%s', '%d', '%s', '%s')
    );

    $member_id = $wpdb->insert_id;

    // Add user avatar as member image
    if ($member_id) {
      $avatar_url = get_avatar_url($user_id, array('size' => 96));
      if ($avatar_url) {
        require_once WP_COSPEND_PLUGIN_DIR . 'includes/class-image-manager.php';
        Image_Manager::save_image('member', $member_id, 'url', $avatar_url, $user_id);
      } else {
        // If no avatar URL, use a default icon
        Image_Manager::save_image('member', $member_id, 'icon', 'user', $user_id);
      }
    }

    // Set default currency to INR if not already set
    $current_currency = get_user_meta($user_id, 'cospend_default_currency', true);
    if (empty($current_currency)) {
      update_user_meta($user_id, 'cospend_default_currency', 'INR');
    }
  }

  /**
   * Delete a member when their WordPress user is deleted.
   *
   * @param int $user_id The WordPress user ID
   */
  public static function delete_member_for_user($user_id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'cospend_members';

    // Check if member has any transactions
    $transactions_table = $wpdb->prefix . 'cospend_transactions';
    $splits_table = $wpdb->prefix . 'cospend_transaction_splits';

    $has_transactions = $wpdb->get_var($wpdb->prepare(
      "SELECT COUNT(*) FROM $transactions_table WHERE payer_id = %d",
      $user_id
    ));

    $has_splits = $wpdb->get_var($wpdb->prepare(
      "SELECT COUNT(*) FROM $splits_table WHERE member_id = %d",
      $user_id
    ));

    if ($has_transactions > 0 || $has_splits > 0) {
      // Instead of deleting, just remove the WordPress user association
      $wpdb->update(
        $table_name,
        array('wp_user_id' => null),
        array('wp_user_id' => $user_id),
        array('%d'),
        array('%d')
      );
    } else {
      // Safe to delete the member
      $wpdb->delete(
        $table_name,
        array('wp_user_id' => $user_id),
        array('%d')
      );
    }
  }

  /**
   * Create members for all existing WordPress users.
   */
  public static function create_members_for_existing_users() {
    $users = get_users();
    foreach ($users as $user) {
      // Set default currency to INR if not already set
      $current_currency = get_user_meta($user->ID, 'cospend_default_currency', true);
      if (empty($current_currency)) {
        update_user_meta($user->ID, 'cospend_default_currency', 'INR');
      }

      self::create_member_for_user($user->ID);
    }
  }

  /**
   * Get member avatar URL.
   *
   * @param int $member_id Member ID
   * @return string|null Avatar URL or null if not found
   */
  public static function get_avatar($member_id) {
    require_once WP_COSPEND_PLUGIN_DIR . 'includes/class-image-manager.php';

    $avatar = \WPCospend\Image_Manager::get_avatar($member_id, 'member');
    return $avatar;
  }

  /**
   * Get member WordPress user details.
   *
   * @param int $member_id Member ID
   * @return array|null Member WordPress user details or null if not found
   */
  public static function get_wp_user($wp_user_id) {
    if (!$wp_user_id) {
      return null;
    }

    $user = get_userdata($wp_user_id);
    if ($user) {
      return array(
        'id' => $user->ID,
        'username' => $user->user_login,
        'email' => $user->user_email,
        'display_name' => $user->display_name,
        'first_name' => $user->first_name,
        'last_name' => $user->last_name,
        'nickname' => $user->nickname,
        'roles' => $user->roles,
        'avatar_url' => get_avatar_url($user->ID, array('size' => 96)),
        'default_currency' => get_user_meta($user->ID, 'cospend_default_currency', true) ?: 'INR'
      );
    }

    return null;
  }

  /**
   * Get member by ID.
   *
   * @param int $member_id Member ID
   * @return object|null Member details or null if not found
   */
  public static function get_member($member_id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'cospend_members';

    $member = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $member_id));
    if ($member) {
      $member->avatar = self::get_avatar($member_id);
      return $member;
    }

    return null;
  }
}
