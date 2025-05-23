<?php

namespace WPCospend;

use WP_Error;
use WPCospend\Image_Manager;
use WPCospend\ImageEntityType;
use WPCospend\ImageReturnType;

enum AccountReturnType: string {
  case Minimum = 'minimum';
  case WithIcon = 'with_icon';
  case WithIconAndPrivateName = 'with_icon_and_private_name';
  case WithAll = 'with_all';
}

enum AccountVisibility: string {
  case Private = 'private';
  case Friends = 'friends';
  case Group = 'group';
}

class Account_Manager {
  /**
   * Initialize the account manager.
   */
  public static function init() {
    // Add hooks for account management
  }

  /**
   * Get an error.
   *
   * @param string $error_code The error code
   * @return WP_Error The error
   */
  public static function get_error($error_code) {
    switch ($error_code) {
      case 'account_exists':
        return new WP_Error('account_exists', __('An account with the same name and private name already exists.', 'wp-cospend'), array('status' => 400));
      case 'db_error':
        return new WP_Error('db_error', __('Database error.', 'wp-cospend'), array('status' => 500));
      case 'account_not_found':
        return new WP_Error('account_not_found', __('Account not found.', 'wp-cospend'), array('status' => 404));
      case 'no_changes':
        return new WP_Error('no_changes', __('No changes to update.', 'wp-cospend'), array('status' => 400));
      case 'no_permissions':
        return new WP_Error('no_permissions', __('You do not have permission to perform this action.', 'wp-cospend'), array('status' => 403));
      case 'no_name':
        return new WP_Error('no_name', __('Name is required.', 'wp-cospend'), array('status' => 400));
      case 'no_private_name':
        return new WP_Error('no_private_name', __('Private name is required.', 'wp-cospend'), array('status' => 400));
      case 'no_visibility':
        return new WP_Error('no_visibility', __('Visibility is required.', 'wp-cospend'), array('status' => 400));
      case 'invalid_visibility':
        return new WP_Error('invalid_visibility', __('Invalid visibility. Must be one of: private, friends, group.', 'wp-cospend'), array('status' => 400));
      case 'virtual_account':
        return new WP_Error('virtual_account', __('Virtual account cannot be edited.', 'wp-cospend'), array('status' => 400));
      case 'virtual_account_delete':
        return new WP_Error('virtual_account_delete', __('Virtual account cannot be deleted.', 'wp-cospend'), array('status' => 400));
      case 'no_default_account':
        return new WP_Error('no_default_account', __('No default account found.', 'wp-cospend'), array('status' => 400));
      case 'invalid_icon_type':
        return new WP_Error('invalid_icon_type', __('Invalid icon type. Must be one of: file, icon.', 'wp-cospend'), array('status' => 400));
      case 'invalid_icon_content':
        return new WP_Error('invalid_icon_content', __('Invalid icon content.', 'wp-cospend'), array('status' => 400));
      case 'account_has_transactions':
        return new WP_Error('account_has_transactions', __('Account has transactions. Cannot delete.', 'wp-cospend'), array('status' => 400));
      case 'invalid_scope':
        return new WP_Error('invalid_scope', __('Invalid scope. Must be one of: friend, group.', 'wp-cospend'), array('status' => 400));
      default:
        return new WP_Error('unknown_error', __('An unknown error occurred.', 'wp-cospend'), array('status' => 500));
    }
  }

  /**
   * Get account icon.
   *
   * @param int $account_id Account ID
   * @return string|null Icon URL or null if not found
   */
  public static function get_icon($account_id) {
    $icon = Image_Manager::get_image(ImageEntityType::Account, $account_id, ImageReturnType::Minimum);

    if (is_wp_error($icon)) {
      return $icon;
    }

    return $icon;
  }

  /**
   * Get account data.
   *
   * @param object $account Account object
   * @param AccountReturnType $return_type The data type (minimum, with_icon, with_all)
   * @return array Account data
   */
  private static function get_account_data($account, AccountReturnType $return_type = AccountReturnType::WithIcon) {
    $account_data = array(
      'id' => $account->id,
      'name' => $account->name,
      'description' => $account->description,
      'created_by' => $account->created_by,
      'member_id' => $account->member_id,
      'is_default' => $account->is_default,
      'visibility' => $account->visibility,
      'is_active' => $account->is_active,
    );

    if ($return_type === AccountReturnType::WithIcon || $return_type === AccountReturnType::WithIconAndPrivateName || $return_type === AccountReturnType::WithAll) {
      $account_data['icon'] = self::get_icon($account->id);
    }

    if ($return_type === AccountReturnType::WithIconAndPrivateName || $return_type === AccountReturnType::WithAll) {
      $account_data['private_name'] = $account->private_name;
    }

    if ($return_type === AccountReturnType::WithAll) {
      $account_data['created_at'] = $account->created_at;
      $account_data['updated_at'] = $account->updated_at;
    }

    return $account_data;
  }

  /**
   * Create a new account.
   *
   * @param string $name Account name
   * @param string $description Account description
   * @param int $created_by User ID who created this account
   * @param string $private_name Private name
   * @param bool $is_default Is default
   * @param AccountVisibility $visibility Visibility
   * @param bool $is_active Is active
   * @param bool $is_virtual Is virtual
   * @return int|WP_Error The account ID if created, WP_Error otherwise
   */
  public static function create_account($name, $description, $created_by, $member_id, $private_name, $is_default, AccountVisibility $visibility, $is_active) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'cospend_accounts';

    // Check if account with same name and private name already exists
    $existing = $wpdb->get_var($wpdb->prepare(
      "SELECT id FROM $table_name WHERE name = %s AND private_name = %s AND created_by = %d",
      $name,
      $private_name,
      $created_by
    ));

    if ($existing) {
      return self::get_error('account_exists');
    }

    $result = $wpdb->insert(
      $table_name,
      array(
        'name' => $name,
        'description' => $description,
        'created_by' => $created_by,
        'member_id' => $member_id,
        'private_name' => $private_name,
        'is_default' => $is_default,
        'visibility' => $visibility->value,
        'is_active' => $is_active,
      ),
      array('%s', '%s', '%d', '%d', '%s', '%d', '%s', '%d')
    );

    if (!$result) {
      return self::get_error('db_error');
    }

    $account_id = $wpdb->insert_id;

    return $account_id;
  }

  /**
   * Update an existing account.
   *
   * @param int $account_id Account ID
   * @param array $data Array of fields to update
   * @return int|WP_Error The account ID if updated, WP_Error otherwise
   */
  public static function update_account($account_id, $data) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'cospend_accounts';

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

    if (isset($data['private_name'])) {
      $update_data['private_name'] = $data['private_name'];
      $update_format[] = '%s';
    }

    if (isset($data['is_default'])) {
      $update_data['is_default'] = $data['is_default'];
      $update_format[] = '%d';
    }

    if (isset($data['visibility'])) {
      $update_data['visibility'] = $data['visibility']->value;
      $update_format[] = '%s';
    }

    if (isset($data['is_active'])) {
      $update_data['is_active'] = $data['is_active'];
      $update_format[] = '%d';
    }

    if (empty($update_data)) {
      return self::get_error('no_changes');
    }

    $result = $wpdb->update(
      $table_name,
      $update_data,
      array('id' => $account_id),
      $update_format,
      array('%d')
    );

    if (!$result) {
      return self::get_error('db_error');
    }

    return $account_id;
  }

  /**
   * Delete an account.
   *
   * @param int $account_id Account ID
   * @return bool|WP_Error True if deleted successfully, WP_Error otherwise
   */
  public static function delete_account($account_id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'cospend_accounts';

    // check account has no transactions
    $transactions = $wpdb->get_results($wpdb->prepare("SELECT id FROM $wpdb->prefix" . 'cospend_transactions_splits WHERE from_account_id = %d OR to_account_id = %d', $account_id, $account_id));
    if ($transactions) {
      return self::get_error('account_has_transactions');
    }

    $result = $wpdb->delete(
      $table_name,
      array('id' => $account_id),
      array('%d')
    );

    if (!$result) {
      return self::get_error('db_error');
    }

    return true;
  }

  /**
   * Get an account by ID.
   *
   * @param int $account_id Account ID
   * @param AccountReturnType $return_type The data type (minimum, with_icon, with_all)
   * @return array|WP_Error Account data or WP_Error if not found
   */
  public static function get_account($account_id, AccountReturnType $return_type = AccountReturnType::WithIcon) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'cospend_accounts';

    $account = $wpdb->get_row($wpdb->prepare(
      "SELECT * FROM $table_name WHERE id = %d",
      $account_id
    ));

    if (!$account) {
      return self::get_error('account_not_found');
    }

    return self::get_account_data($account, $return_type);
  }

  /**
   * Get all accounts. (Admin only)
   *
   * @param AccountReturnType $return_type The data type (minimum, with_icon, with_all)
   * @return array|WP_Error All accounts data or WP_Error
   */
  public static function get_all_accounts(AccountReturnType $return_type = AccountReturnType::WithIcon) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'cospend_accounts';

    $accounts = $wpdb->get_results("SELECT * FROM $table_name");

    if (!$accounts) {
      return self::get_error('no_accounts');
    }

    $accounts_data = array();

    foreach ($accounts as $account) {
      $accounts_data[] = self::get_account_data($account, $return_type);
    }

    return $accounts_data;
  }

  /**
   * Get all accounts for a user.
   *
   * @param int $user_id User ID
   * @param AccountReturnType $return_type The data type (minimum, with_icon, with_all)
   * @return array|WP_Error All accounts data or WP_Error
   */
  public static function get_user_accounts($user_id, AccountVisibility $visibility, AccountReturnType $return_type = AccountReturnType::WithIcon) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'cospend_accounts';
    $visibility_filter = '';

    if ($visibility === AccountVisibility::Private) {
      $visibility_filter = "";
    } else if ($visibility === AccountVisibility::Friends) {
      $visibility_filter = "AND (visibility = 'friends' OR visibility = 'group')";
    } else if ($visibility === AccountVisibility::Group) {
      $visibility_filter = "AND visibility = 'group'";
    }

    $accounts = $wpdb->get_results($wpdb->prepare(
      "SELECT * FROM $table_name WHERE created_by = %d $visibility_filter",
      $user_id
    ));

    if (!$accounts) {
      return self::get_error('no_accounts');
    }

    $accounts_data = array();

    foreach ($accounts as $account) {
      $accounts_data[] = self::get_account_data($account, $return_type);
    }

    return $accounts_data;
  }

  /**
   * Get account by id.
   *
   * @param int $account_id Account ID
   * @param AccountReturnType $return_type The data type (minimum, with_icon, with_all)
   * @return array|WP_Error Account data or WP_Error if not found
   */
  public static function get_account_by_id($account_id, AccountReturnType $return_type = AccountReturnType::WithIcon) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'cospend_accounts';

    $account = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $account_id));

    if (!$account) {
      return self::get_error('account_not_found');
    }

    return self::get_account_data($account, $return_type);
  }

  /**
   * Get user default account.
   *
   * @param int $user_id User ID
   * @return array|WP_Error Account data or WP_Error if not found
   */
  public static function get_user_default_account($user_id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'cospend_accounts';

    $account = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE created_by = %d AND is_default = 1", $user_id));

    if (!$account) {
      return self::get_error('no_default_account');
    }

    return self::get_account_data($account);
  }

  /**
   * Set user default account.
   *
   * @param int $user_id User ID
   * @param int $account_id Account ID
   * @return bool|WP_Error True if set successfully, WP_Error otherwise
   */
  public static function set_user_default_account($user_id, $account_id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'cospend_accounts';

    // Reset all other accounts to not default
    $result = $wpdb->update($table_name, array('is_default' => 0), array('created_by' => $user_id));

    // Set the new default account
    $result = $wpdb->update($table_name, array('is_default' => 1), array('id' => $account_id, 'created_by' => $user_id));

    if (!$result) {
      return self::get_error('db_error');
    }

    return true;
  }
}
