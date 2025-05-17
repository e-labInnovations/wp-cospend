<?php

namespace WPCospend;

class Image_Manager {
  /**
   * Allowed image types.
   */
  private static $allowed_types = array('png', 'jpg', 'jpeg', 'gif', 'svg');

  /**
   * Initialize the image manager.
   */
  public static function init() {
    // Add hooks for image management
    add_action('wp_ajax_wp_cospend_upload_image', array(__CLASS__, 'handle_image_upload'));
    add_action('wp_ajax_wp_cospend_delete_image', array(__CLASS__, 'handle_image_delete'));
  }

  /**
   * Get image for an entity.
   *
   * @param string $entity_type The entity type (category, tag, member)
   * @param int $entity_id The entity ID
   * @param string $type The image type (url, icon, svg)
   * @return array|null The image data or null if not found
   */
  public static function get_image($entity_type, $entity_id, $type = 'icon', $minimum_data = true) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'cospend_images';

    $image = $wpdb->get_row($wpdb->prepare(
      "SELECT * FROM $table_name WHERE entity_type = %s AND entity_id = %d AND type = %s",
      $entity_type,
      $entity_id,
      $type
    ));

    if ($minimum_data) {
      return array(
        'id' => $image->id,
        'type' => $image->type,
        'content' => $image->content,
      );
    }

    return array(
      'id' => $image->id,
      'type' => $image->type,
      'content' => $image->content,
      'updated_at' => $image->updated_at,
      'created_at' => $image->created_at,
      'created_by' => $image->created_by,
    );
  }

  /**
   * Save image for an entity.
   *
   * @param string $entity_type The entity type (category, tag, member)
   * @param int $entity_id The entity ID
   * @param string $type The image type (url, icon, svg)
   * @param string $content The image content (URL, icon name, or SVG markup)
   * @param int $created_by The user ID who created this image
   * @return int|false The image ID if created, false otherwise
   */
  public static function save_image($entity_type, $entity_id, $type, $content, $created_by) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'cospend_images';

    // Check if image already exists
    $existing = $wpdb->get_var($wpdb->prepare(
      "SELECT id FROM $table_name WHERE entity_type = %s AND entity_id = %d AND type = %s",
      $entity_type,
      $entity_id,
      $type
    ));

    if ($existing) {
      // Update existing image
      $result = $wpdb->update(
        $table_name,
        array(
          'content' => $content,
          'updated_at' => current_time('mysql'),
        ),
        array(
          'entity_type' => $entity_type,
          'entity_id' => $entity_id,
          'type' => $type,
        ),
        array('%s', '%s'),
        array('%s', '%d', '%s')
      );

      return $result !== false ? $existing : false;
    } else {
      // Insert new image
      $result = $wpdb->insert(
        $table_name,
        array(
          'entity_type' => $entity_type,
          'entity_id' => $entity_id,
          'type' => $type,
          'content' => $content,
          'created_by' => $created_by,
          'created_at' => current_time('mysql'),
          'updated_at' => current_time('mysql'),
        ),
        array('%s', '%d', '%s', '%s', '%d', '%s', '%s')
      );

      return $result ? $wpdb->insert_id : false;
    }
  }

  /**
   * Delete image for an entity.
   *
   * @param string $entity_type The entity type (category, tag, member)
   * @param int $entity_id The entity ID
   * @param string $type The image type (url, icon, svg)
   * @return bool True if deleted, false otherwise
   */
  public static function delete_image($entity_type, $entity_id, $type) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'cospend_images';

    $result = $wpdb->delete(
      $table_name,
      array(
        'entity_type' => $entity_type,
        'entity_id' => $entity_id,
        'type' => $type,
      ),
      array('%s', '%d', '%s')
    );

    return $result !== false;
  }

  /**
   * Handle image upload via AJAX.
   */
  public static function handle_image_upload() {
    check_ajax_referer('wp_cospend_nonce', 'nonce');

    if (!current_user_can('manage_cospend')) {
      wp_send_json_error('Permission denied');
    }

    $entity_type = sanitize_text_field($_POST['entity_type']);
    $entity_id = intval($_POST['entity_id']);
    $type = sanitize_text_field($_POST['type']);

    if (!in_array($entity_type, array('category', 'tag', 'member'))) {
      wp_send_json_error('Invalid entity type');
    }

    if (!in_array($type, array('url', 'icon', 'svg'))) {
      wp_send_json_error('Invalid image type');
    }

    if (!isset($_FILES['image'])) {
      wp_send_json_error('No image uploaded');
    }

    require_once(ABSPATH . 'wp-admin/includes/image.php');
    require_once(ABSPATH . 'wp-admin/includes/file.php');
    require_once(ABSPATH . 'wp-admin/includes/media.php');

    $attachment_id = media_handle_upload('image', 0);

    if (is_wp_error($attachment_id)) {
      wp_send_json_error($attachment_id->get_error_message());
    }

    $image_url = wp_get_attachment_url($attachment_id);
    $image_id = self::save_image($entity_type, $entity_id, $type, $image_url, get_current_user_id());

    if (!$image_id) {
      wp_send_json_error('Failed to save image');
    }

    wp_send_json_success(array(
      'image_id' => $image_id,
      'url' => $image_url,
    ));
  }

  /**
   * Handle image deletion via AJAX.
   */
  public static function handle_image_delete() {
    check_ajax_referer('wp_cospend_nonce', 'nonce');

    if (!current_user_can('manage_cospend')) {
      wp_send_json_error('Permission denied');
    }

    $entity_type = sanitize_text_field($_POST['entity_type']);
    $entity_id = intval($_POST['entity_id']);
    $type = sanitize_text_field($_POST['type']);

    if (!in_array($entity_type, array('category', 'tag', 'member'))) {
      wp_send_json_error('Invalid entity type');
    }

    if (!in_array($type, array('url', 'icon', 'svg'))) {
      wp_send_json_error('Invalid image type');
    }

    $result = self::delete_image($entity_type, $entity_id, $type);

    if (!$result) {
      wp_send_json_error('Failed to delete image');
    }

    wp_send_json_success();
  }

  /**
   * Get upload directory for cospend images.
   *
   * @return string Upload directory path
   */
  private static function get_upload_dir() {
    $upload_dir = wp_upload_dir();
    $cospend_dir = $upload_dir['basedir'] . '/cospend-images';

    // Create directory if it doesn't exist
    if (!file_exists($cospend_dir)) {
      wp_mkdir_p($cospend_dir);
    }

    return $cospend_dir;
  }

  /**
   * Get upload URL for cospend images.
   *
   * @return string Upload URL
   */
  private static function get_upload_url() {
    $upload_dir = wp_upload_dir();
    return $upload_dir['baseurl'] . '/cospend-images';
  }

  /**
   * Validate uploaded file.
   *
   * @param array $file File data from $_FILES
   * @return WP_Error|null Error if invalid, null if valid
   */
  private static function validate_file($file) {
    // Check file type
    $file_type = wp_check_filetype($file['name']);
    if (!$file_type['ext'] || !in_array(strtolower($file_type['ext']), self::$allowed_types)) {
      return new \WP_Error(
        'invalid_file_type',
        sprintf(__('Invalid file type. Allowed types: %s', 'wp-cospend'), implode(', ', self::$allowed_types)),
        array('status' => 400)
      );
    }

    // Check file size (max 5MB)
    if ($file['size'] > 5 * 1024 * 1024) {
      return new \WP_Error(
        'file_too_large',
        __('File is too large. Maximum size is 5MB.', 'wp-cospend'),
        array('status' => 400)
      );
    }

    return null;
  }

  /**
   * Handle image file upload and save.
   *
   * @param string $entity_type The entity type (category, tag, member)
   * @param int $entity_id The entity ID
   * @param string $file_key The key in $_FILES array
   * @param int $created_by The user ID who created this image
   * @return array|WP_Error Array with image data on success, WP_Error on failure
   */
  public static function handle_file_upload($entity_type, $entity_id, $file_key, $created_by) {
    if (!isset($_FILES[$file_key])) {
      return new \WP_Error(
        'no_file',
        __('No file uploaded.', 'wp-cospend'),
        array('status' => 400)
      );
    }

    // Validate file
    $error = self::validate_file($_FILES[$file_key]);
    if (is_wp_error($error)) {
      return $error;
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'cospend_images';

    // Check if image already exists
    $existing = $wpdb->get_row($wpdb->prepare(
      "SELECT * FROM $table_name WHERE entity_type = %s AND entity_id = %d AND type = %s",
      $entity_type,
      $entity_id,
      'url'
    ));

    // Get file extension
    $file_type = wp_check_filetype($_FILES[$file_key]['name']);
    $extension = $file_type['ext'];
    $timestamp = time();

    if ($existing) {
      // Update existing image
      $file_name = $existing->id . '-' . $timestamp . '.' . $extension;
      $file_path = self::get_upload_dir() . '/' . $file_name;

      if (!move_uploaded_file($_FILES[$file_key]['tmp_name'], $file_path)) {
        return new \WP_Error(
          'upload_error',
          __('Failed to save uploaded file.', 'wp-cospend'),
          array('status' => 500)
        );
      }

      // Delete old file if exists
      $old_file = self::get_upload_dir() . '/' . basename($existing->content);
      if (file_exists($old_file)) {
        unlink($old_file);
      }

      // Update image record
      $file_url = self::get_upload_url() . '/' . $file_name;
      $wpdb->update(
        $table_name,
        array(
          'content' => $file_url,
          'updated_at' => current_time('mysql'),
        ),
        array('id' => $existing->id),
        array('%s', '%s'),
        array('%d')
      );

      return array(
        'id' => $existing->id,
        'type' => 'url',
        'content' => $file_url
      );
    } else {
      // Create new image record
      $wpdb->insert(
        $table_name,
        array(
          'entity_type' => $entity_type,
          'entity_id' => $entity_id,
          'type' => 'url',
          'content' => '', // Will be updated after file save
          'created_by' => $created_by,
          'created_at' => current_time('mysql'),
          'updated_at' => current_time('mysql'),
        ),
        array('%s', '%d', '%s', '%s', '%d', '%s', '%s')
      );

      $image_id = $wpdb->insert_id;
      if (!$image_id) {
        return new \WP_Error(
          'db_error',
          __('Failed to create image record.', 'wp-cospend'),
          array('status' => 500)
        );
      }

      // Save new file
      $file_name = $image_id . '-' . $timestamp . '.' . $extension;
      $file_path = self::get_upload_dir() . '/' . $file_name;

      if (!move_uploaded_file($_FILES[$file_key]['tmp_name'], $file_path)) {
        // Rollback image record if file move fails
        $wpdb->delete($table_name, array('id' => $image_id), array('%d'));
        return new \WP_Error(
          'upload_error',
          __('Failed to save uploaded file.', 'wp-cospend'),
          array('status' => 500)
        );
      }

      // Update image record with URL
      $file_url = self::get_upload_url() . '/' . $file_name;
      $wpdb->update(
        $table_name,
        array(
          'content' => $file_url,
          'updated_at' => current_time('mysql'),
        ),
        array('id' => $image_id),
        array('%s', '%s'),
        array('%d')
      );

      return array(
        'id' => $image_id,
        'type' => 'url',
        'content' => $file_url
      );
    }
  }
}
