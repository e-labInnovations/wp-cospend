<?php

namespace WPCospend;

use WP_Error;

class Category_Manager {
  /**
   * Initialize the category manager.
   */
  public static function init() {
    // Add hooks for category management
  }

  /**
   * Get category icon.
   *
   * @param int $category_id Category ID
   * @return string|null Icon URL or null if not found
   */
  public static function get_icon($category_id) {
    require_once WP_COSPEND_PLUGIN_DIR . 'includes/class-image-manager.php';

    $icon = \WPCospend\Image_Manager::get_icon($category_id, 'category');
    return $icon;
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
      "SELECT id FROM $table_name WHERE name = %s AND created_by = %d AND parent_id " . ($parent_id ? "= %d" : "IS NULL"),
      $name,
      $created_by,
      $parent_id
    ));

    if ($existing) {
      return new WP_Error(
        'category_exists',
        __('A category with the same name already exists under the same parent.', 'wp-cospend'),
        array('status' => 400)
      );
    }

    $result = $wpdb->insert(
      $table_name,
      array(
        'name' => $name,
        'color' => $color,
        'parent_id' => $parent_id,
        'created_by' => $created_by,
        'created_at' => current_time('mysql'),
        'updated_at' => current_time('mysql'),
      ),
      array('%s', '%s', '%d', '%d', '%s', '%s')
    );

    if (!$result) {
      return new WP_Error(
        'db_error',
        __('Error creating category.', 'wp-cospend'),
        array('status' => 500)
      );
    }

    $category_id = $wpdb->insert_id;

    return $category_id;
  }

  /**
   * Update an existing category.
   *
   * @param int $category_id Category ID to update
   * @param array $data Array of fields to update
   * @return bool True if updated successfully, false otherwise
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
      return false;
    }

    $result = $wpdb->update(
      $table_name,
      $update_data,
      array('id' => $category_id),
      $update_format,
      array('%d')
    );

    return $result !== false;
  }

  /**
   * Delete a category.
   *
   * @param int $category_id Category ID to delete
   * @return bool True if deleted successfully, false otherwise
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
      return false;
    }

    $result = $wpdb->delete(
      $table_name,
      array('id' => $category_id),
      array('%d')
    );

    return $result !== false;
  }

  /**
   * Get a category by ID.
   *
   * @param int $category_id Category ID
   * @return object|null Category object or null if not found
   */
  public static function get_category($category_id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'cospend_categories';

    $category = $wpdb->get_row($wpdb->prepare(
      "SELECT * FROM $table_name WHERE id = %d",
      $category_id
    ));

    if (!$category) {
      return null;
    }

    $category->icon = self::get_icon($category_id);

    return $category;
  }

  /**
   * Get all categories created by a user.
   *
   * @param int $user_id User ID
   * @return array Array of category objects
   */
  public static function get_user_categories($user_id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'cospend_categories';

    $categories = $wpdb->get_results($wpdb->prepare(
      "SELECT * FROM $table_name WHERE created_by = %d OR created_by = 0 ORDER BY name ASC",
      $user_id
    ));

    // Add icons to categories
    foreach ($categories as $category) {
      $category->icon = self::get_icon($category->id);
    }

    return $categories;
  }

  /**
   * Get all categories (admin only).
   *
   * @return array Array of category objects
   */
  public static function get_all_categories() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'cospend_categories';

    $categories = $wpdb->get_results(
      "SELECT * FROM $table_name ORDER BY name ASC"
    );

    // Add icons to categories
    foreach ($categories as $category) {
      $category->icon = self::get_icon($category->id);
    }

    return $categories;
  }

  /**
   * Get child categories for a parent category.
   *
   * @param int $parent_id Parent category ID
   * @return array Array of child category objects
   */
  public static function get_child_categories($parent_id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'cospend_categories';

    $categories = $wpdb->get_results($wpdb->prepare(
      "SELECT * FROM $table_name WHERE parent_id = %d ORDER BY name ASC",
      $parent_id
    ));

    // Add icons to categories
    foreach ($categories as $category) {
      $category->icon = self::get_icon($category->id);
    }

    return $categories;
  }
}
