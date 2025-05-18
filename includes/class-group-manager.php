<?php

namespace WPCospend;

use WP_Error;
use WPCospend\Image_Manager;

enum GroupReturnType: string {
  case Minimum = 'minimum';
  case WithAvatar = 'with_avatar';
  case WithAll = 'with_all';
}

class Group_Manager {
  /**
   * The default currency.
   */
  private static $default_currency = "INR";

  /**
   * Initialize the group manager.
   */
  public static function init() {
    // Add hooks for group management
    // TODO: Not implemented yet (disabled and not used)
    // add_action('user_register', array(__CLASS__, 'create_default_group_for_user'));
  }

  public static function get_error($error_code) {
    switch ($error_code) {
      case 'avatar_not_found':
        return new WP_Error('avatar_not_found', __('Avatar not found.', 'wp-cospend'), array('status' => 404));
      case 'group_not_found':
        return new WP_Error('group_not_found', __('Group not found.', 'wp-cospend'), array('status' => 404));
      case 'group_already_exists':
        return new WP_Error('group_already_exists', __('Group already exists.', 'wp-cospend'), array('status' => 400));
      case 'db_error':
        return new WP_Error('db_error', __('Database error.', 'wp-cospend'), array('status' => 500));
      case 'invalid_currency':
        return new WP_Error('invalid_currency', __('Invalid currency specified.', 'wp-cospend'), array('status' => 400));
      case 'group_has_transactions':
        return new WP_Error('group_has_transactions', __('Group has transactions. Cannot delete group with transactions.', 'wp-cospend'), array('status' => 400));
      case 'no_permission':
        return new WP_Error('no_permission', __('You do not have permission to perform this action.', 'wp-cospend'), array('status' => 403));
      case 'no_name':
        return new WP_Error('no_name', __('Name is required.', 'wp-cospend'), array('status' => 400));
      case 'invalid_avatar_type':
        return new WP_Error('invalid_avatar_type', __('Invalid avatar type.', 'wp-cospend'), array('status' => 400));
      case 'invalid_avatar_content':
        return new WP_Error('invalid_avatar_content', __('Invalid avatar content.', 'wp-cospend'), array('status' => 400));
      default:
        return new WP_Error('unknown_error', __('Unknown error.', 'wp-cospend'), array('status' => 500));
    }
  }

  /**
   * Get group avatar URL.
   *
   * @param int $group_id Group ID
   * @return array{type: string, content: string}|WP_Error Avatar data or WP_Error if not found
   */
  public static function get_avatar($group_id) {
    $avatar = Image_Manager::get_image(ImageEntityType::Group, $group_id, ImageReturnType::Minimum);
    if (is_wp_error($avatar)) {
      return self::get_error('avatar_not_found');
    }

    return $avatar;
  }

  /**
   * Get group data.
   *
   * @param object $group Group object
   * @param GroupReturnType $return_type The data type (minimum, with_avatar, with_all)
   * @return array Group data
   */
  private static function get_group_data($group, GroupReturnType $return_type = GroupReturnType::WithAvatar) {
    $group_data = array(
      'id' => $group->id,
      'name' => $group->name,
      'description' => $group->description,
      'currency' => $group->currency,
      'created_by' => $group->created_by,
    );

    if ($return_type === GroupReturnType::WithAvatar || $return_type === GroupReturnType::WithAll) {
      $group_data['avatar'] = self::get_avatar($group->id);
    }

    if ($return_type === GroupReturnType::WithAll) {
      $group_data['created_at'] = $group->created_at;
      $group_data['updated_at'] = $group->updated_at;
    }

    return $group_data;
  }

  /**
   * Create a default group for a new WordPress user.
   *
   * @param int $user_id The WordPress user ID
   */
  public static function create_default_group_for_user($user_id) {
    // TODO: Not implemented yet (disabled and not used)
    $user = get_userdata($user_id);
    if (!$user) {
      return;
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'cospend_groups';

    // Check if user already has a default group
    $existing = $wpdb->get_var($wpdb->prepare(
      "SELECT id FROM $table_name WHERE created_by = %d AND name = %s",
      $user_id,
      'Personal'
    ));

    if ($existing) {
      return;
    }

    // Create new default group
    $wpdb->insert(
      $table_name,
      array(
        'name' => 'Personal',
        'description' => 'Personal expenses group',
        'currency' => get_user_meta($user_id, 'cospend_default_currency', true) ?: 'INR',
        'created_by' => $user_id,
        'created_at' => current_time('mysql'),
        'updated_at' => current_time('mysql'),
      ),
      array('%s', '%s', '%s', '%d', '%s', '%s')
    );
  }

  /**
   * Create a new group.
   *
   * @param string $name Group name
   * @param string $description Group description
   * @param string $currency Group currency
   * @param int $created_by User ID who created this group
   * @return int|WP_Error The group ID if created, WP_Error otherwise
   */
  public static function create_group($name, $description, $currency, $created_by) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'cospend_groups';

    // TODO: Implement currency validation
    $currency = self::$default_currency;

    $result = $wpdb->insert(
      $table_name,
      array(
        'name' => $name,
        'description' => $description,
        'currency' => $currency,
        'created_by' => $created_by,
      ),
      array('%s', '%s', '%s', '%d')
    );

    return $result ? $wpdb->insert_id : self::get_error('db_error');
  }

  /**
   * Update an existing group.
   *
   * @param int $group_id Group ID to update
   * @param array $data Array of fields to update
   * @return bool|WP_Error True if updated successfully, WP_Error otherwise
   */
  public static function update_group($group_id, $data) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'cospend_groups';

    // Validate currency if provided
    // if (isset($data['currency']) && !in_array($data['currency'], array('INR', 'USD', 'EUR', 'GBP'))) {
    //   return false;
    // }

    $update_data = array();
    $update_format = array();

    // Build update data
    if (isset($data['name'])) {
      $update_data['name'] = $data['name'];
      $update_format[] = '%s';
    }

    if (isset($data['description'])) {
      $update_data['description'] = $data['description'];
      $update_format[] = '%s';
    }

    // if (isset($data['currency'])) {
    //   $update_data['currency'] = $data['currency'];
    //   $update_format[] = '%s';
    // }

    if (empty($update_data)) {
      return false;
    }

    $result = $wpdb->update(
      $table_name,
      $update_data,
      array('id' => $group_id),
      $update_format,
      array('%d')
    );

    if ($result === false) {
      return self::get_error('db_error');
    }

    return true;
  }

  /**
   * Delete a group.
   *
   * @param int $group_id Group ID to delete
   * @return bool|WP_Error True if deleted successfully, WP_Error otherwise
   */
  public static function delete_group($group_id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'cospend_groups';

    // Check if group has any transactions
    $transactions_table = $wpdb->prefix . 'cospend_transactions';
    $has_transactions = $wpdb->get_var($wpdb->prepare(
      "SELECT COUNT(*) FROM $transactions_table WHERE group_id = %d",
      $group_id
    ));

    if ($has_transactions > 0) {
      return self::get_error('group_has_transactions');
    }

    $result = $wpdb->delete(
      $table_name,
      array('id' => $group_id),
      array('%d')
    );

    if ($result === false) {
      return self::get_error('db_error');
    }

    return true;
  }

  /**
   * Get a group by ID.
   *
   * @param int $group_id Group ID
   * @return array|WP_Error Group object or WP_Error if not found
   */
  public static function get_group($group_id, GroupReturnType $return_type = GroupReturnType::WithAvatar) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'cospend_groups';

    $group = $wpdb->get_row($wpdb->prepare(
      "SELECT * FROM $table_name WHERE id = %d",
      $group_id
    ));

    if (!$group) {
      return self::get_error('group_not_found');
    }

    return self::get_group_data($group, $return_type);
  }

  /**
   * Get all groups created by a user.
   *
   * @param int $user_id User ID
   * @param GroupReturnType $return_type The data type (minimum, with_avatar, with_all)
   * @return array|WP_Error Array of group objects or WP_Error if not found
   */
  public static function get_user_groups($user_id, GroupReturnType $return_type = GroupReturnType::WithAvatar) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'cospend_groups';

    $groups = $wpdb->get_results($wpdb->prepare(
      "SELECT * FROM $table_name WHERE created_by = %d ORDER BY name ASC",
      $user_id
    ));

    if (is_null($groups)) {
      return self::get_error('db_error');
    }

    $groups_data = [];

    foreach ($groups as $group) {
      $groups_data[] = self::get_group_data($group, $return_type);
    }

    return $groups_data;
  }

  /**
   * Get all groups (admin only).
   *
   * @param GroupReturnType $return_type The data type (minimum, with_avatar, with_all)
   * @return array|WP_Error Array of group objects or WP_Error if not found
   */
  public static function get_all_groups(GroupReturnType $return_type = GroupReturnType::WithAvatar) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'cospend_groups';

    $groups = $wpdb->get_results(
      "SELECT * FROM $table_name ORDER BY name ASC"
    );

    if (is_null($groups)) {
      return self::get_error('db_error');
    }

    $groups_data = [];

    foreach ($groups as $group) {
      $groups_data[] = self::get_group_data($group, $return_type);
    }

    return $groups_data;
  }

  /**
   * Add a member to a group.
   *
   * @param int $group_id Group ID
   * @param int $member_id Member ID
   * @param bool $can_edit Whether the member can edit the group
   * @return bool|WP_Error True if added successfully, WP_Error otherwise
   */
  public static function add_member_to_group($group_id, $member_id, $can_edit = false) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'cospend_group_members';

    // Check if member is already in the group
    $existing = $wpdb->get_var($wpdb->prepare(
      "SELECT id FROM $table_name WHERE group_id = %d AND member_id = %d",
      $group_id,
      $member_id
    ));

    if ($existing) {
      return true; // Member already in group
    }

    $result = $wpdb->insert(
      $table_name,
      array(
        'group_id' => $group_id,
        'member_id' => $member_id,
        'can_edit' => $can_edit,
      ),
      array('%d', '%d', '%d')
    );

    if ($result === false) {
      return self::get_error('db_error');
    }

    return true;
  }

  /**
   * Remove a member from a group.
   *
   * @param int $group_id Group ID
   * @param int $member_id Member ID
   * @return bool|WP_Error True if removed successfully, WP_Error otherwise
   */
  public static function remove_member_from_group($group_id, $member_id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'cospend_group_members';

    $result = $wpdb->delete(
      $table_name,
      array(
        'group_id' => $group_id,
        'member_id' => $member_id,
      ),
      array('%d', '%d')
    );

    if ($result === false) {
      return self::get_error('db_error');
    }

    return true;
  }
}
