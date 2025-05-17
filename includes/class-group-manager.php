<?php

namespace WPCospend;

class Group_Manager {
  /**
   * Initialize the group manager.
   */
  public static function init() {
    // Add hooks for group management
    add_action('user_register', array(__CLASS__, 'create_default_group_for_user'));
  }

  /**
   * Get group avatar URL.
   *
   * @param int $group_id Group ID
   * @return string|null Avatar URL or null if not found
   */
  public static function get_avatar($group_id) {
    require_once WP_COSPEND_PLUGIN_DIR . 'includes/class-image-manager.php';

    $avatar = \WPCospend\Image_Manager::get_avatar($group_id, 'group');
    return $avatar;
  }

  /**
   * Create a default group for a new WordPress user.
   *
   * @param int $user_id The WordPress user ID
   */
  public static function create_default_group_for_user($user_id) {
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
   * @return int|false The group ID if created, false otherwise
   */
  public static function create_group($name, $description, $currency, $created_by) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'cospend_groups';

    // Validate currency
    if (!in_array($currency, array('INR', 'USD', 'EUR', 'GBP'))) {
      return false;
    }

    $result = $wpdb->insert(
      $table_name,
      array(
        'name' => $name,
        'description' => $description,
        'currency' => $currency,
        'created_by' => $created_by,
        'created_at' => current_time('mysql'),
        'updated_at' => current_time('mysql'),
      ),
      array('%s', '%s', '%s', '%d', '%s', '%s')
    );

    return $result ? $wpdb->insert_id : false;
  }

  /**
   * Update an existing group.
   *
   * @param int $group_id Group ID to update
   * @param array $data Array of fields to update
   * @return bool True if updated successfully, false otherwise
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

    if (isset($data['currency'])) {
      $update_data['currency'] = $data['currency'];
      $update_format[] = '%s';
    }

    // Always update the updated_at timestamp
    $update_data['updated_at'] = current_time('mysql');
    $update_format[] = '%s';

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

    return $result !== false;
  }

  /**
   * Delete a group.
   *
   * @param int $group_id Group ID to delete
   * @return bool True if deleted successfully, false otherwise
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
      return false;
    }

    $result = $wpdb->delete(
      $table_name,
      array('id' => $group_id),
      array('%d')
    );

    return $result !== false;
  }

  /**
   * Get a group by ID.
   *
   * @param int $group_id Group ID
   * @return object|null Group object or null if not found
   */
  public static function get_group($group_id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'cospend_groups';

    $group = $wpdb->get_row($wpdb->prepare(
      "SELECT * FROM $table_name WHERE id = %d",
      $group_id
    ));

    if (!$group) {
      return null;
    }

    $group->avatar = self::get_avatar($group_id);

    return $group;
  }

  /**
   * Get all groups created by a user.
   *
   * @param int $user_id User ID
   * @return array Array of group objects
   */
  public static function get_user_groups($user_id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'cospend_groups';

    return $wpdb->get_results($wpdb->prepare(
      "SELECT * FROM $table_name WHERE created_by = %d ORDER BY name ASC",
      $user_id
    ));
  }

  /**
   * Get all groups (admin only).
   *
   * @return array Array of group objects
   */
  public static function get_all_groups() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'cospend_groups';

    return $wpdb->get_results(
      "SELECT * FROM $table_name ORDER BY name ASC"
    );
  }

  /**
   * Add a member to a group.
   *
   * @param int $group_id Group ID
   * @param int $member_id Member ID
   * @return bool True if added successfully, false otherwise
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
        'created_at' => current_time('mysql'),
        'can_edit' => $can_edit,
      ),
      array('%d', '%d', '%s', '%s')
    );

    return $result !== false;
  }

  /**
   * Remove a member from a group.
   *
   * @param int $group_id Group ID
   * @param int $member_id Member ID
   * @return bool True if removed successfully, false otherwise
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

    return $result !== false;
  }

  /**
   * Get all members in a group.
   *
   * @param int $group_id Group ID
   * @return array Array of member objects
   */
  public static function get_group_members($group_id) {
    global $wpdb;
    $members_table = $wpdb->prefix . 'cospend_members';
    $group_members_table = $wpdb->prefix . 'cospend_group_members';

    return $wpdb->get_results($wpdb->prepare(
      "SELECT m.* FROM $members_table m
      INNER JOIN $group_members_table gm ON m.id = gm.member_id
      WHERE gm.group_id = %d
      ORDER BY m.name ASC",
      $group_id
    ));
  }
}
