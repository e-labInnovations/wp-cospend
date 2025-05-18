<?php

namespace WPCospend;

use WP_Error;
use WPCospend\File_Manager;

enum ImageReturnType: string {
  case Minimum = 'minimum';
  case WithId = 'with_id';
  case WithAll = 'with_all';
}

enum ImageType: string {
  case Url = 'url';
  case Icon = 'icon';
}

enum ImageEntityType: string {
  case Category = 'category';
  case Tag = 'tag';
  case Member = 'member';
  case Group = 'group';
  case Account = 'account';
}

class Image_Manager {
  /**
   * Initialize the image manager.
   */
  public static function init() {
  }

  /**
   * Get an error.
   *
   * @param string $error_code The error code
   * @return WP_Error The error
   */
  private static function get_error($error_code) {
    switch ($error_code) {
      case 'invalid_type':
        return new WP_Error('invalid_type', 'Invalid type', array('status' => 400));
      case 'invalid_return_type':
        return new WP_Error('invalid_return_type', 'Invalid return type', array('status' => 400));
      case 'db_error':
        return new WP_Error('db_error', 'Database error', array('status' => 500));
      case 'not_found':
        return new WP_Error('not_found', 'Image not found', array('status' => 404));
      default:
        return new WP_Error('unknown_error', 'Unknown error', array('status' => 500));
    }
  }

  /**
   * Get image for an entity.
   *
   * @param ImageEntityType $entity_type The entity type (category, tag, member)
   * @param int $entity_id The entity ID
   * @param ImageReturnType $return_type The data type (minimum, with_id, with_all)
   * @return array|WP_Error The image data or WP_Error if not found
   */
  public static function get_image(ImageEntityType $entity_type, int $entity_id, ImageReturnType $return_type) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'cospend_images';

    $image = $wpdb->get_row($wpdb->prepare(
      "SELECT * FROM $table_name WHERE entity_type = %s AND entity_id = %d",
      $entity_type->value,
      $entity_id
    ));

    if (!$image) {
      return self::get_error('not_found');
    }

    $output_data = array(
      'type' => $image->type,
      'content' => $image->content,
    );

    if ($return_type === ImageReturnType::WithId || $return_type === ImageReturnType::WithAll) {
      $output_data['id'] = $image->id;
    }

    if ($return_type === ImageReturnType::WithAll) {
      $output_data['id'] = $image->id;
      $output_data['updated_at'] = $image->updated_at;
      $output_data['created_at'] = $image->created_at;
    }

    return $output_data;
  }

  /**
   * Save image for an entity.
   *
   * @param ImageEntityType $entity_type The entity type (category, tag, member, group, account)
   * @param int $entity_id The entity ID
   * @param ImageType $type The image type (url, icon)
   * @param string $content The image content (file content, icon name)
   * @param int $created_by The user ID who created this image
   * @return int|WP_Error The image ID if created, WP_Error otherwise
   */
  private static function save_image(ImageEntityType $entity_type, int $entity_id, ImageType $type, string $content, int $created_by) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'cospend_images';

    // Check if image already exists
    $existing = $wpdb->get_var($wpdb->prepare(
      "SELECT id FROM $table_name WHERE entity_type = %s AND entity_id = %d",
      $entity_type->value,
      $entity_id
    ));

    if ($existing) {
      // Update existing image
      $result = $wpdb->update(
        $table_name,
        array(
          'content' => $content,
          'type' => $type->value,
        ),
        array(
          'entity_type' => $entity_type->value,
          'entity_id' => $entity_id,
        ),
        array('%s', '%s'),
        array('%s', '%d', '%s')
      );

      return $result !== false ? $existing : self::get_error('db_error');
    }

    // Insert new image
    $result = $wpdb->insert(
      $table_name,
      array(
        'entity_type' => $entity_type->value,
        'entity_id' => $entity_id,
        'type' => $type->value,
        'content' => $content,
        'created_by' => $created_by,
      ),
      array('%s', '%d', '%s', '%s', '%d')
    );

    return $result ? $wpdb->insert_id : self::get_error('db_error');
  }

  /**
   * Delete image for an entity.
   *
   * @param ImageEntityType $entity_type The entity type (category, tag, member)
   * @param int $entity_id The entity ID
   * @return bool|WP_Error True if deleted, WP_Error otherwise
   */
  public static function delete_image(ImageEntityType $entity_type, int $entity_id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'cospend_images';

    $result = $wpdb->delete(
      $table_name,
      array(
        'entity_type' => $entity_type->value,
        'entity_id' => $entity_id,
      ),
      array('%s', '%d')
    );

    return $result !== false ? true : self::get_error('db_error');
  }

  /**
   * Save image icon.
   *
   * @param ImageEntityType $entity_type The entity type (category, tag, member)
   * @param int $entity_id The entity ID
   * @param string $icon_name The icon name
   * @return int|WP_Error The image ID if created, WP_Error otherwise
   */
  public static function save_image_icon(ImageEntityType $entity_type, int $entity_id, string $icon_name) {
    return self::save_image($entity_type, $entity_id, ImageType::Icon, $icon_name, get_current_user_id());
  }

  /**
   * Save image URL.
   *
   * @param ImageEntityType $entity_type The entity type (category, tag, member)
   * @param int $entity_id The entity ID
   * @param string $url The URL
   * @return int|WP_Error The image ID if created, WP_Error otherwise
   */
  public static function save_image_url(ImageEntityType $entity_type, int $entity_id, string $url) {
    return self::save_image($entity_type, $entity_id, ImageType::Url, $url, get_current_user_id());
  }

  /**
   * Save image file.
   *
   * @param ImageEntityType $entity_type The entity type (category, tag, member)
   * @param int $entity_id The entity ID
   * @param string $file_key The key in $_FILES array
   * @return int|WP_Error The image ID if created, WP_Error otherwise
   */
  public static function save_image_file(ImageEntityType $entity_type, int $entity_id, string $file_key) {
    $file_url = File_Manager::upload_file($file_key, 'image', $entity_type->value, $entity_id);
    if (is_wp_error($file_url)) {
      return $file_url;
    }

    return self::save_image($entity_type, $entity_id, ImageType::Url, $file_url, get_current_user_id());
  }
}
