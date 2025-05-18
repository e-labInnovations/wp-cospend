<?php

namespace WPCospend;

use WP_Error;
use WPCospend\Image_Manager;

enum MemberReturnType: string {
  case Minimum = 'minimum';
  case WithAvatar = 'with_avatar';
  case WithWpUser = 'with_wp_user';
  case WithAvatarAndWpUser = 'with_avatar_and_wp_user';
  case WithAll = 'with_all';
}

class Member_Manager {
  /**
   * The default avatar icon.
   */
  private static $default_avatar_icon = "circle-user";

  /**
   * The default currency meta key.
   */
  public static $default_currency_meta_key = "cospend_default_currency";

  /**
   * The default currency.
   */
  private static $default_currency = "INR";

  /**
   * Initialize the member manager.
   */
  public static function init() {
    // Add hooks for member management
    add_action('user_register', array(__CLASS__, 'create_member_for_user'));

    // TODO: Not implemented yet (disabled and not used)
    // add_action('delete_user', array(__CLASS__, 'unlink_member_for_user'));
  }

  /**
   * Get an error.
   *
   * @param string $error_code The error code
   * @return WP_Error The error
   */
  public static function get_error($error_code) {
    switch ($error_code) {
      case 'not_found':
        return new WP_Error('not_found', 'Member not found', array('status' => 404));
      case 'db_error':
        return new WP_Error('db_error', 'Database error', array('status' => 500));
      case 'avatar_not_found':
        return new WP_Error('avatar_not_found', 'Avatar not found', array('status' => 404));
      case 'member_not_found':
        return new WP_Error('member_not_found', 'Member not found', array('status' => 404));
      case 'wp_user_not_found':
        return new WP_Error('wp_user_not_found', 'WordPress user not found', array('status' => 404));
      case 'missing_name':
        return new WP_Error('missing_name', 'Name is required', array('status' => 400));
      case 'invalid_avatar_type':
        return new WP_Error('invalid_avatar_type', 'Invalid avatar type', array('status' => 400));
      case 'invalid_avatar_content':
        return new WP_Error('invalid_avatar_content', 'Invalid avatar content', array('status' => 400));
      case 'duplicate_name':
        return new WP_Error('duplicate_name', 'A member with this name already exists in your list', array('status' => 400));
      case 'member_in_use':
        return new WP_Error('member_in_use', 'This member is in use and cannot be deleted', array('status' => 400));
      case 'invalid_email':
        return new WP_Error('invalid_email', 'Invalid email address', array('status' => 400));
      case 'user_not_found':
        return new WP_Error('user_not_found', 'User not found', array('status' => 404));
      default:
        return new WP_Error('unknown_error', 'Unknown error', array('status' => 500));
    }
  }

  private static function get_member_data(object $member, MemberReturnType $return_type = MemberReturnType::WithAvatarAndWpUser) {
    $member_data = array(
      'id' => $member->id,
      'name' => $member->name,
      'created_by' => $member->created_by,
    );

    if ($return_type === MemberReturnType::WithAvatar || $return_type === MemberReturnType::WithAvatarAndWpUser || $return_type === MemberReturnType::WithAll) {
      $member_data['avatar'] = self::get_avatar($member->id);
    }

    if ($return_type === MemberReturnType::WithWpUser || $return_type === MemberReturnType::WithAvatarAndWpUser || $return_type === MemberReturnType::WithAll) {
      $member_data['wp_user'] = self::get_wp_user($member->wp_user_id);
    }

    if ($return_type === MemberReturnType::WithAll) {
      $member_data['created_at'] = $member->created_at;
      $member_data['updated_at'] = $member->updated_at;
    }

    return $member_data;
  }

  /**
   * Get member by ID.
   *
   * @param int $member_id Member ID
   * @param MemberReturnType $return_type The data type (minimum, with_avatar, with_all)
   * @return array|WP_Error Member details or WP_Error if not found
   */
  public static function get_member($member_id, MemberReturnType $return_type = MemberReturnType::WithAvatarAndWpUser) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'cospend_members';

    $member = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $member_id));

    if (!$member) {
      return self::get_error('member_not_found');
    }

    return self::get_member_data($member, $return_type);
  }

  /**
   * Get a member by name.
   *
   * @param string $name The member name
   * @param int $user_id The user ID
   * @return array|WP_Error Member details or WP_Error if not found
   */
  public static function get_member_by_name($name, $user_id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'cospend_members';

    $member = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE name = %s AND created_by = %d", $name, $user_id));

    if (!$member) {
      return self::get_error('member_not_found');
    }

    return self::get_member_data($member, MemberReturnType::WithAvatarAndWpUser);
  }

  /**
   * Get all members.
   *
   * @return array|WP_Error Array of member objects or WP_Error if not found
   */
  public static function get_all_members() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'cospend_members';

    $members = $wpdb->get_results("SELECT * FROM $table_name ORDER BY name ASC");

    if (is_null($members)) {
      return self::get_error('db_error');
    }

    $members_data = [];

    foreach ($members as $member) {
      $members_data[] = self::get_member_data($member, MemberReturnType::WithAvatarAndWpUser);
    }

    return $members_data;
  }

  /**
   * Get all members created by the current user.
   *
   * @param int $user_id The user ID
   * @return array|WP_Error Array of member objects or WP_Error if not found
   */
  public static function get_all_members_created_by($user_id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'cospend_members';

    $members = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE created_by = %d ORDER BY name ASC", $user_id));

    if (is_null($members)) {
      return self::get_error('db_error');
    }

    $members_data = [];

    foreach ($members as $member) {
      $members_data[] = self::get_member_data($member, MemberReturnType::WithAvatarAndWpUser);
    }

    return $members_data;
  }

  /**
   * Create a member.
   *
   * @param string $name The member name
   * @param int $wp_user_id The WordPress user ID
   * @param int $created_by The user ID of the creator
   */
  public static function create_member($name, $wp_user_id, $created_by = null) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'cospend_members';

    $wpdb->insert(
      $table_name,
      array(
        'name' => $name,
        'wp_user_id' => $wp_user_id,
        'created_by' => $created_by ?: get_current_user_id()
      ),
      array('%s', '%d', '%d')
    );

    $member_id = $wpdb->insert_id;

    return $member_id;
  }

  /**
   * Create a member for a new WordPress user.
   *
   * @param int $user_id The WordPress user ID
   */
  public static function create_member_for_user($user_id) {
    require_once WP_COSPEND_PLUGIN_DIR . 'includes/class-file-manager.php';
    require_once WP_COSPEND_PLUGIN_DIR . 'includes/class-image-manager.php';

    $user = get_userdata($user_id);
    if (!$user) {
      return;
    }

    $member_id = self::create_member($user->display_name, $user_id, $user_id);

    // Add user avatar as member image
    if ($member_id) {
      $avatar_url = get_avatar_url($user_id, array('size' => 96));
      if ($avatar_url) {
        Image_Manager::save_image_url(ImageEntityType::Member, $member_id, $avatar_url);
      } else {
        // If no avatar URL, use a default icon
        Image_Manager::save_image_icon(ImageEntityType::Member, $member_id, self::$default_avatar_icon);
      }
    }

    // Set default currency to INR if not already set
    $current_currency = get_user_meta($user_id, self::$default_currency_meta_key, true);
    if (empty($current_currency)) {
      update_user_meta($user_id, self::$default_currency_meta_key, self::$default_currency);
    }
  }

  /**
   * Update a member.
   *
   * @param int $member_id The member ID
   * @param array $data The data to update
   */
  public static function update_member($member_id, $data) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'cospend_members';

    $update_data = array();
    $update_format = array();

    // Build update data
    if (isset($data['name'])) {
      $update_data['name'] = $data['name'];
      $update_format[] = '%s';
    }

    if (isset($data['wp_user_id'])) {
      $update_data['wp_user_id'] = $data['wp_user_id'];
      $update_format[] = '%d';
    }

    if (empty($update_data)) {
      return false;
    }

    $result = $wpdb->update(
      $table_name,
      $update_data,
      array('id' => $member_id),
      $update_format,
      array('%d')
    );

    if ($result === false) {
      return self::get_error('db_error');
    }

    return true;
  }

  /**
   * Delete a member when their WordPress user is deleted.
   *
   * @param int $user_id The WordPress user ID
   */
  public static function unlink_member_for_user($user_id) {
    // TODO: Not implemented yet (disabled and not used)
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
      $current_currency = get_user_meta($user->ID, self::$default_currency_meta_key, true);
      if (empty($current_currency)) {
        update_user_meta($user->ID, self::$default_currency_meta_key, self::$default_currency);
      }

      self::create_member_for_user($user->ID);
    }
  }

  /**
   * Get member avatar URL.
   *
   * @param int $member_id Member ID
   * @return array{type: string, content: string}|WP_Error Avatar data or WP_Error if not found
   */
  public static function get_avatar($member_id) {
    $avatar = Image_Manager::get_image(ImageEntityType::Member, $member_id, ImageReturnType::Minimum);
    if (is_wp_error($avatar)) {
      return self::get_error('avatar_not_found');
    }

    return $avatar;
  }

  /**
   * Get member WordPress user details.
   *
   * @param int $member_id Member ID
   * @return array{id: int, username: string, email: string, display_name: string, avatar_url: string, default_currency: string}|null Member WordPress user details or null if not found
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
        'avatar_url' => get_avatar_url($user->ID, array('size' => 96)),
        'default_currency' => get_user_meta($user->ID, self::$default_currency_meta_key, true) ?: self::$default_currency
      );
    }

    return null;
  }

  /**
   * Get all members in a group.
   *
   * @param int $group_id Group ID
   * @param MemberReturnType $return_type The data type (minimum, with_avatar, with_all)
   * @return array|WP_Error Array of member objects or WP_Error if not found
   */
  public static function get_group_members($group_id, MemberReturnType $return_type = MemberReturnType::WithAvatarAndWpUser) {
    global $wpdb;
    $members_table = $wpdb->prefix . 'cospend_members';
    $group_members_table = $wpdb->prefix . 'cospend_group_members';

    $members = $wpdb->get_results($wpdb->prepare(
      "SELECT m.*, gm.can_edit FROM $members_table m
      INNER JOIN $group_members_table gm ON m.id = gm.member_id
      WHERE gm.group_id = %d
      ORDER BY m.name ASC",
      $group_id
    ));

    if (is_null($members)) {
      return self::get_error('db_error');
    }

    $members_data = [];

    foreach ($members as $member) {
      $member_data = self::get_member_data($member, $return_type);
      $member_data['can_edit'] = $member->can_edit;
      $members_data[] = $member_data;
    }

    return $members_data;
  }

  /**
   * Delete a member.
   *
   * @param int $member_id Member ID
   */
  public static function delete_member($member_id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'cospend_members';
    $transactions_table = $wpdb->prefix . 'cospend_transactions';
    $splits_table = $wpdb->prefix . 'cospend_transaction_splits';

    $has_transactions = $wpdb->get_var($wpdb->prepare(
      "SELECT COUNT(*) FROM $transactions_table WHERE payer_id = %d",
      $member_id
    ));

    $has_splits = $wpdb->get_var($wpdb->prepare(
      "SELECT COUNT(*) FROM $splits_table WHERE member_id = %d",
      $member_id
    ));

    if ($has_transactions > 0 || $has_splits > 0) {
      return self::get_error('member_in_use');
    }

    $result = $wpdb->delete($table_name, array('id' => $member_id), array('%d'));

    if ($result === false) {
      return self::get_error('db_error');
    }

    return true;
  }
}
