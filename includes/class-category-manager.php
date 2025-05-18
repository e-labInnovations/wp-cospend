<?php

namespace WPCospend;

use WP_Error;
use WPCospend\Image_Manager;

enum CategoryReturnType: string {
  case Minimum = 'minimum';
  case WithIcon = 'with_icon';
  case WithAll = 'with_all';
}

enum CategoryType: string {
  case Expense = 'expense';
  case Income = 'income';
  case Transfer = 'transfer';
}

class Category_Manager {
  /**
   * Initialize the category manager.
   */
  public static function init() {
    // Add hooks for category management
  }

  /**
   * Get an error.
   *
   * @param string $error_code The error code
   * @return WP_Error The error
   */
  public static function get_error($error_code) {
    switch ($error_code) {
      case 'category_exists':
        return new WP_Error('category_exists', __('A category with the same name already exists under the same parent.', 'wp-cospend'), array('status' => 400));
      case 'db_error':
        return new WP_Error('db_error', __('Database error.', 'wp-cospend'), array('status' => 500));
      case 'category_not_found':
        return new WP_Error('category_not_found', __('Category not found.', 'wp-cospend'), array('status' => 404));
      case 'category_has_transactions':
        return new WP_Error('category_has_transactions', __('Category has transactions. Cannot delete category with transactions.', 'wp-cospend'), array('status' => 400));
      case 'invalid_color':
        return new WP_Error('invalid_color', __('Invalid color.', 'wp-cospend'), array('status' => 400));
      case 'parent_not_found':
        return new WP_Error('parent_not_found', __('Parent category not found.', 'wp-cospend'), array('status' => 404));
      case 'parent_is_self':
        return new WP_Error('parent_is_self', __('Parent category cannot be the same as the category itself.', 'wp-cospend'), array('status' => 400));
      case 'parent_is_subcategory':
        return new WP_Error('parent_is_subcategory', __('Parent category cannot be a subcategory.', 'wp-cospend'), array('status' => 400));
      case 'no_changes':
        return new WP_Error('no_changes', __('No changes to update.', 'wp-cospend'), array('status' => 400));
      case 'no_permissions':
        return new WP_Error('no_permissions', __('You do not have permission to perform this action.', 'wp-cospend'), array('status' => 403));
      case 'no_name':
        return new WP_Error('no_name', __('Name is required.', 'wp-cospend'), array('status' => 400));
      case 'no_color':
        return new WP_Error('no_color', __('Color is required.', 'wp-cospend'), array('status' => 400));
      case 'invalid_color_format':
        return new WP_Error('invalid_color_format', __('Invalid color format. Use hex color code (e.g. #FF0000).', 'wp-cospend'), array('status' => 400));
      case 'no_icon':
        return new WP_Error('no_icon', __('Icon is required.', 'wp-cospend'), array('status' => 400));
      case 'invalid_icon_type':
        return new WP_Error('invalid_icon_type', __('Invalid icon type.', 'wp-cospend'), array('status' => 400));
      case 'invalid_icon_content':
        return new WP_Error('invalid_icon_content', __('Invalid icon content.', 'wp-cospend'), array('status' => 400));
      case 'invalid_type':
        return new WP_Error('invalid_type', __('Invalid type. Type must be one of the following: expense, income, transfer.', 'wp-cospend'), array('status' => 400));

      default:
        return new WP_Error('unknown_error', __('Unknown error.', 'wp-cospend'), array('status' => 500));
    }
  }

  /**
   * Get category icon.
   *
   * @param int $category_id Category ID
   * @return string|null Icon URL or null if not found
   */
  public static function get_icon($category_id) {
    $icon = Image_Manager::get_image(ImageEntityType::Category, $category_id, ImageReturnType::Minimum);

    if (is_wp_error($icon)) {
      return $icon;
    }

    return $icon;
  }

  /**
   * Get category data.
   *
   * @param object $category Category object
   * @param CategoryReturnType $return_type The data type (minimum, with_icon, with_all)
   * @return array Category data
   */
  private static function get_category_data($category, CategoryReturnType $return_type = CategoryReturnType::WithIcon) {
    $category_data = array(
      'id' => $category->id,
      'name' => $category->name,
      'color' => $category->color,
      'parent_id' => $category->parent_id,
      'created_by' => $category->created_by,
    );

    if ($return_type === CategoryReturnType::WithIcon || $return_type === CategoryReturnType::WithAll) {
      $category_data['icon'] = self::get_icon($category->id);
    }

    if ($return_type === CategoryReturnType::WithAll) {
      $category_data['created_at'] = $category->created_at;
      $category_data['updated_at'] = $category->updated_at;
    }

    return $category_data;
  }

  /**
   * Create a new category.
   *
   * @param string $name Category name
   * @param string $color Category color
   * @param int $parent_id Parent category ID (optional)
   * @param int $created_by User ID who created this category
   * @param string $icon Icon name (optional)
   * @return int|WP_Error The category ID if created, WP_Error otherwise
   */
  public static function create_category($name, $color, $parent_id, $created_by) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'cospend_categories';

    // Check if category with same name already exists
    $existing = $wpdb->get_var($wpdb->prepare(
      "SELECT id FROM $table_name WHERE name = %s AND (created_by = %d OR created_by = 0) AND parent_id " . ($parent_id ? "= %d" : "IS NULL"),
      $name,
      $created_by,
      $parent_id
    ));

    if ($existing) {
      return self::get_error('category_exists');
    }

    $result = $wpdb->insert(
      $table_name,
      array(
        'name' => $name,
        'color' => $color,
        'parent_id' => $parent_id,
        'created_by' => $created_by,
      ),
      array('%s', '%s', '%d', '%d')
    );

    if (!$result) {
      return self::get_error('db_error');
    }

    $category_id = $wpdb->insert_id;

    return $category_id;
  }

  /**
   * Update an existing category.
   *
   * @param int $category_id Category ID to update
   * @param array $data Array of fields to update
   * @return int|WP_Error The category ID if updated, WP_Error otherwise
   */
  public static function update_category($category_id, $data) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'cospend_categories';

    $update_data = array();
    $update_format = array();

    // Build update data
    if (isset($data['name'])) {
      $update_data['name'] = $data['name'];
      $update_format[] = '%s';
    }

    if (isset($data['color'])) {
      $update_data['color'] = $data['color'];
      $update_format[] = '%s';
    }

    if (isset($data['parent_id'])) {
      $update_data['parent_id'] = $data['parent_id'];
      $update_format[] = '%d';
    }

    // Always update the updated_at timestamp
    $update_data['updated_at'] = current_time('mysql');
    $update_format[] = '%s';

    if (empty($update_data)) {
      return self::get_error('no_changes');
    }

    $result = $wpdb->update(
      $table_name,
      $update_data,
      array('id' => $category_id),
      $update_format,
      array('%d')
    );

    if (!$result) {
      return self::get_error('db_error');
    }

    return $category_id;
  }

  /**
   * Delete a category.
   *
   * @param int $category_id Category ID to delete
   * @return bool|WP_Error True if deleted successfully, WP_Error otherwise
   */
  public static function delete_category($category_id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'cospend_categories';

    // Check if category has any transactions
    $transactions_table = $wpdb->prefix . 'cospend_transactions';
    $has_transactions = $wpdb->get_var($wpdb->prepare(
      "SELECT COUNT(*) FROM $transactions_table WHERE category_id = %d",
      $category_id
    ));

    if ($has_transactions > 0) {
      return self::get_error('category_has_transactions');
    }

    $result = $wpdb->delete(
      $table_name,
      array('id' => $category_id),
      array('%d')
    );

    if ($result === false) {
      return self::get_error('db_error');
    }

    return true;
  }

  /**
   * Get a category by ID.
   *
   * @param int $category_id Category ID
   * @param CategoryReturnType $return_type The data type (minimum, with_icon, with_all)
   * @return array|WP_Error Category data or WP_Error if not found
   */
  public static function get_category($category_id, CategoryReturnType $return_type = CategoryReturnType::WithIcon) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'cospend_categories';

    $category = $wpdb->get_row($wpdb->prepare(
      "SELECT * FROM $table_name WHERE id = %d",
      $category_id
    ));

    if (!$category) {
      return self::get_error('category_not_found');
    }

    return self::get_category_data($category, $return_type);
  }

  /**
   * Get all categories created by a user.
   *
   * @param int $user_id User ID
   * @param CategoryReturnType $return_type The data type (minimum, with_icon, with_all)
   * @return array|WP_Error Array of category objects or WP_Error if not found
   */
  public static function get_user_categories($user_id, CategoryReturnType $return_type = CategoryReturnType::WithIcon) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'cospend_categories';

    $categories = $wpdb->get_results($wpdb->prepare(
      "SELECT * FROM $table_name WHERE created_by = %d OR created_by = 0 ORDER BY name ASC",
      $user_id
    ));

    if (is_null($categories)) {
      return self::get_error('db_error');
    }

    $categories_data = [];

    foreach ($categories as $category) {
      $categories_data[] = self::get_category_data($category, $return_type);
    }

    return $categories_data;
  }

  /**
   * Get all categories (admin only).
   *
   * @param CategoryReturnType $return_type The data type (minimum, with_icon, with_all)
   * @return array|WP_Error Array of category objects or WP_Error if not found
   */
  public static function get_all_categories(CategoryReturnType $return_type = CategoryReturnType::WithIcon) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'cospend_categories';

    $categories = $wpdb->get_results(
      "SELECT * FROM $table_name ORDER BY name ASC"
    );

    if (is_null($categories)) {
      return self::get_error('db_error');
    }

    $categories_data = [];

    foreach ($categories as $category) {
      $categories_data[] = self::get_category_data($category, $return_type);
    }

    return $categories_data;
  }

  /**
   * Get child categories for a parent category.
   *
   * @param int $parent_id Parent category ID
   * @param CategoryReturnType $return_type The data type (minimum, with_icon, with_all)
   * @return array|WP_Error Array of child category objects or WP_Error if not found
   */
  public static function get_child_categories($parent_id, CategoryReturnType $return_type = CategoryReturnType::WithIcon) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'cospend_categories';

    $categories = $wpdb->get_results($wpdb->prepare(
      "SELECT * FROM $table_name WHERE parent_id = %d ORDER BY name ASC",
      $parent_id
    ));

    if (is_null($categories)) {
      return self::get_error('db_error');
    }

    $categories_data = [];

    foreach ($categories as $category) {
      $categories_data[] = self::get_category_data($category, $return_type);
    }

    return $categories_data;
  }
}
