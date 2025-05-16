<?php

namespace WPCospend;

class Image_Manager {
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
  public static function get_image($entity_type, $entity_id, $type = 'icon') {
    global $wpdb;
    $table_name = $wpdb->prefix . 'cospend_images';

    $image = $wpdb->get_row($wpdb->prepare(
      "SELECT * FROM $table_name WHERE entity_type = %s AND entity_id = %d AND type = %s",
      $entity_type,
      $entity_id,
      $type
    ));

    return $image;
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
}
