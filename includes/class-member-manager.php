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

    $member_id = $result ? $wpdb->insert_id : false;

    // If member was created and has a WordPress user, add their avatar
    if ($member_id && $wp_user_id) {
      $avatar_url = get_avatar_url($wp_user_id, array('size' => 96));
      if ($avatar_url) {
        require_once WP_COSPEND_PLUGIN_DIR . 'includes/class-image-manager.php';
        Image_Manager::save_image('member', $member_id, 'url', $avatar_url, $created_by);
      }
    }

    return $member_id;
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

    if ($result !== false) {
      // Add user avatar as member image
      $avatar_url = get_avatar_url($wp_user_id, array('size' => 96));
      if ($avatar_url) {
        require_once WP_COSPEND_PLUGIN_DIR . 'includes/class-image-manager.php';
        Image_Manager::save_image('member', $member_id, 'url', $avatar_url, get_current_user_id());
      }
    }

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

  /**
   * Get member avatar URL.
   *
   * @param int $member_id Member ID
   * @return string|null Avatar URL or null if not found
   */
  public static function get_member_avatar($member_id) {
    require_once WP_COSPEND_PLUGIN_DIR . 'includes/class-image-manager.php';

    // Try to get URL type first
    /** @var object|null $image */
    $image = Image_Manager::get_image('member', $member_id, 'url');
    if ($image) {
      return $image->content;
    }

    // If no URL, try to get icon type
    /** @var object|null $image */
    $image = Image_Manager::get_image('member', $member_id, 'icon');
    if ($image) {
      return $image->content;
    }

    return null;
  }

  /**
   * Update member avatar.
   *
   * @param int $member_id Member ID
   * @param string $avatar_url New avatar URL
   * @param int $user_id User ID who is updating the avatar
   * @return bool True if updated successfully, false otherwise
   */
  public static function update_member_avatar($member_id, $avatar_url, $user_id) {
    require_once WP_COSPEND_PLUGIN_DIR . 'includes/class-image-manager.php';
    return Image_Manager::save_image('member', $member_id, 'url', $avatar_url, $user_id) !== false;
  }
}
