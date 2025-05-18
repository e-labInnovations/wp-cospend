<?php

namespace WPCospend;

use WP_Error;

enum TagsReturnType: string {
  case Minimum = 'minimum';
  case WithIcon = 'with_icon';
  case WithAll = 'with_all';
}

class Tags_Manager {
  /**
   * Initialize the tags manager.
   */
  public static function init() {
    // Add hooks for tag management
  }

  /**
   * Get an error.
   *
   * @param string $error_code The error code
   * @return WP_Error The error
   */
  public static function get_error($error_code) {
    switch ($error_code) {
      case 'tag_exists':
        return new WP_Error('tag_exists', 'Tag already exists');
      case 'db_error':
        return new WP_Error('db_error', 'Database error');
      case 'no_name':
        return new WP_Error('no_name', 'Tag name is required');
      case 'no_color':
        return new WP_Error('no_color', 'Tag color is required');
      case 'invalid_color_format':
        return new WP_Error('invalid_color_format', 'Invalid color format');
      case 'invalid_icon_type':
        return new WP_Error('invalid_icon_type', 'Invalid icon type');
      case 'invalid_icon_content':
        return new WP_Error('invalid_icon_content', 'Invalid icon content');
      case 'no_access':
        return new WP_Error('no_access', 'You do not have access to this tag');
      case 'tag_not_found':
        return new WP_Error('tag_not_found', 'Tag not found');
      case 'tag_has_transactions':
        return new WP_Error('tag_has_transactions', 'Tag has transactions and cannot be deleted');
      case 'no_changes':
        return new WP_Error('no_changes', 'No changes to update');
      default:
        return new WP_Error('invalid_error_code', 'Invalid error code');
    }
  }

  /**
   * Get tag icon.
   *
   * @param int $tag_id Tag ID
   * @return array|WP_Error Icon data or WP_Error if not found
   */
  public static function get_icon($tag_id) {
    $icon = Image_Manager::get_image(ImageEntityType::Tag, $tag_id, ImageReturnType::Minimum);
    if (is_wp_error($icon)) {
      return $icon;
    }

    return $icon;
  }

  /**
   * Get tag data.
   *
   * @param object $tag Tag object
   * @param TagsReturnType $return_type The data type (minimum, with_icon, with_all)
   * @return array Tag data
   */
  private static function get_tag_data($tag, TagsReturnType $return_type = TagsReturnType::WithIcon) {
    $tag_data = array(
      'id' => $tag->id,
      'name' => $tag->name,
      'color' => $tag->color,
      'created_by' => $tag->created_by,
    );

    if ($return_type === TagsReturnType::WithIcon || $return_type === TagsReturnType::WithAll) {
      $tag_data['icon'] = self::get_icon($tag->id);
    }

    if ($return_type === TagsReturnType::WithAll) {
      $tag_data['created_at'] = $tag->created_at;
      $tag_data['updated_at'] = $tag->updated_at;
    }

    return $tag_data;
  }

  /**
   * Get a tag.
   *
   * @param int $tag_id Tag ID
   * @param TagsReturnType $return_type The data type (minimum, with_icon, with_all)
   * @return array|WP_Error Tag data or WP_Error if not found
   */
  public static function get_tag($tag_id, TagsReturnType $return_type = TagsReturnType::WithIcon) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'cospend_tags';

    $tag = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $tag_id));

    if (!$tag) {
      return self::get_error('tag_not_found');
    }

    return self::get_tag_data($tag, $return_type);
  }

  /**
   * Get all tags.
   *
   * @param TagsReturnType $return_type The data type (minimum, with_icon, with_all)
   * @return array|WP_Error Tag data or WP_Error if not found
   */
  public static function get_all_tags(TagsReturnType $return_type = TagsReturnType::WithIcon) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'cospend_tags';

    $tags = $wpdb->get_results("SELECT * FROM $table_name");

    if (!$tags) {
      return self::get_error('no_tags');
    }

    $tags_data = array();

    foreach ($tags as $tag) {
      $tags_data[] = self::get_tag_data($tag, $return_type);
    }

    return $tags_data;
  }

  /**
   * Get user tags.
   *
   * @param int $user_id User ID
   * @param TagsReturnType $return_type The data type (minimum, with_icon, with_all)
   * @return array|WP_Error Tag data or WP_Error if not found
   */
  public static function get_user_tags($user_id, TagsReturnType $return_type = TagsReturnType::WithIcon) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'cospend_tags';

    $tags = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE (created_by = %d OR created_by = 0)", $user_id));

    if (!$tags) {
      return self::get_error('no_tags');
    }

    $tags_data = array();

    foreach ($tags as $tag) {
      $tags_data[] = self::get_tag_data($tag, $return_type);
    }

    return $tags_data;
  }

  /**
   * Create a new tag.
   *
   * @param string $name Tag name
   * @param string $color Tag color
   * @param int $created_by User ID who created this tag
   * @return int|WP_Error The tag ID if created, WP_Error otherwise
   */
  public static function create_tag($name, $color, $created_by) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'cospend_tags';

    // Check if tag with same name already exists
    $existing = $wpdb->get_var($wpdb->prepare(
      "SELECT id FROM $table_name WHERE name = %s AND (created_by = %d OR created_by = 0)",
      $name,
      $created_by
    ));

    if ($existing) {
      return self::get_error('tag_exists');
    }

    $result = $wpdb->insert(
      $table_name,
      array(
        'name' => $name,
        'color' => $color,
        'created_by' => $created_by,
      ),
      array('%s', '%s', '%d')
    );

    if (!$result) {
      return self::get_error('db_error');
    }

    $tag_id = $wpdb->insert_id;

    return $tag_id;
  }

  /**
   * Update a tag.
   *
   * @param int $tag_id Tag ID
   * @param array $data Tag data
   * @return int|WP_Error The tag ID if updated, WP_Error otherwise
   */
  public static function update_tag($tag_id, $data) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'cospend_tags';

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

    if (empty($update_data)) {
      return self::get_error('no_changes');
    }

    $result = $wpdb->update(
      $table_name,
      $update_data,
      array('id' => $tag_id),
      $update_format,
      array('%d')
    );

    if (!$result) {
      return self::get_error('db_error');
    }

    return $tag_id;
  }

  /**
   * Delete a tag.
   *
   * @param int $tag_id Tag ID
   * @return bool|WP_Error True if deleted successfully, WP_Error otherwise
   */
  public static function delete_tag($tag_id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'cospend_tags';

    // Check if tag has any transactions
    $transactions_table = $wpdb->prefix . 'cospend_transaction_tags';
    $has_transactions = $wpdb->get_var($wpdb->prepare(
      "SELECT COUNT(*) FROM $transactions_table WHERE tag_id = %d",
      $tag_id
    ));

    if ($has_transactions > 0) {
      return self::get_error('tag_has_transactions');
    }

    $result = $wpdb->delete($table_name, array('id' => $tag_id), array('%d'));

    if (!$result) {
      return self::get_error('db_error');
    }

    return true;
  }

  /**
   * Add a tag to a transaction.
   *
   * @param int $transaction_id Transaction ID
   * @param int $tag_id Tag ID
   * @return bool|WP_Error True if added successfully, WP_Error otherwise
   */
  public static function add_tag_to_transaction($transaction_id, $tag_id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'cospend_transaction_tags';

    $result = $wpdb->insert($table_name, array('transaction_id' => $transaction_id, 'tag_id' => $tag_id), array('%d', '%d'));

    if (!$result) {
      return self::get_error('db_error');
    }

    return true;
  }
}
