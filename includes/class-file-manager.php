<?php

namespace WPCospend;

use WP_Error;

class File_Manager {
  /**
   * Allowed file types and their MIME types.
   */
  private static $allowed_types = array(
    'image' => array(
      'extensions' => array('png', 'jpg', 'jpeg', 'gif', 'svg'),
      'mime_types' => array(
        'image/png',
        'image/jpeg',
        'image/gif',
        'image/svg+xml'
      )
    ),
    'document' => array(
      'extensions' => array('pdf'),
      'mime_types' => array(
        'application/pdf',
      )
    )
  );

  /**
   * Maximum file size in bytes (5MB).
   */
  private static $max_file_size = 5 * 1024 * 1024;

  /**
   * Initialize the file manager.
   */
  public static function init() {
  }

  /**
   * Get an error.
   *
   * @param string $error_code Error code
   * @return WP_Error Error object
   */
  private static function get_error($error_code) {
    switch ($error_code) {
      case 'invalid_file_type':
        return new WP_Error('invalid_file_type', __('Invalid file type specified.', 'wp-cospend'), array('status' => 400));
      case 'invalid_mime_type':
        return new WP_Error('invalid_mime_type', __('Invalid file MIME type.', 'wp-cospend'), array('status' => 400));
      case 'file_too_large':
        return new WP_Error('file_too_large', __('File is too large. Maximum size is 5MB.', 'wp-cospend'), array('status' => 400));
      case 'upload_error':
        return new WP_Error('upload_error', __('Failed to save uploaded file.', 'wp-cospend'), array('status' => 500));
      default:
        return new WP_Error('unknown_error', __('Unknown error.', 'wp-cospend'), array('status' => 500));
    }
  }

  /**
   * Get upload directory for cospend files.
   *
   * @param string $type File type (images, documents)
   * @return string Upload directory path
   */
  public static function get_upload_dir($type = 'images') {
    $upload_dir = wp_upload_dir();
    $cospend_dir = $upload_dir['basedir'] . '/cospend//' . $type;

    // Create directory if it doesn't exist
    if (!file_exists($cospend_dir)) {
      wp_mkdir_p($cospend_dir);
    }

    return $cospend_dir;
  }

  /**
   * Get upload URL for cospend files.
   *
   * @param string $type File type (images, documents)
   * @return string Upload URL
   */
  public static function get_upload_url($type = 'images') {
    $upload_dir = wp_upload_dir();
    return $upload_dir['baseurl'] . '/cospend//' . $type;
  }

  /**
   * Validate uploaded file.
   *
   * @param array $file File data from $_FILES
   * @param string $type File type (image, document)
   * @return WP_Error|null Error if invalid, null if valid
   */
  public static function validate_file($file, $type = 'image') {
    if (!isset(self::$allowed_types[$type])) {
      return self::get_error('invalid_file_type');
    }

    // Check file type
    $file_type = wp_check_filetype($file['name']);
    if (!$file_type['ext'] || !in_array(strtolower($file_type['ext']), self::$allowed_types[$type]['extensions'])) {
      return self::get_error('invalid_file_type');
    }

    // Check MIME type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mime_type, self::$allowed_types[$type]['mime_types'])) {
      return self::get_error('invalid_mime_type');
    }

    // Check file size
    if ($file['size'] > self::$max_file_size) {
      return self::get_error('file_too_large');
    }

    return null;
  }

  /**
   * Upload a file.
   *
   * @param string $file_key File key in $_FILES
   * @param string $type File type (image, document)
   * @param string $entity_type Entity type (member, group, etc.)
   * @param int $entity_id Entity ID
   * @return string|WP_Error File URL on success, WP_Error on failure
   */
  public static function upload_file($file_key, $type, $entity_type, $entity_id) {
    $file = $_FILES[$file_key];
    // Validate file
    $error = self::validate_file($file, $type);
    if (is_wp_error($error)) {
      return $error;
    }

    // Generate unique filename
    $file_type = wp_check_filetype($file['name']);
    $extension = $file_type['ext'];
    $timestamp = time();
    $unique_id = uniqid();
    $file_name = $entity_type . '-' . $entity_id . '-' . $unique_id . '-' . $timestamp . '.' . $extension;

    // Determine upload directory based on file type
    $upload_dir = self::get_upload_dir($type === 'image' ? 'images' : 'documents');
    $file_path = $upload_dir . '/' . $file_name;

    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $file_path)) {
      return self::get_error('upload_error');
    }

    // Get file URL
    $upload_url = self::get_upload_url($type === 'image' ? 'images' : 'documents');
    $file_url = $upload_url . '/' . $file_name;

    return $file_url;
  }
}
