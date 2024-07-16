<?php

if (!defined('ABSPATH')) {
  exit; // Exit if accessed directly.
}

class WPCospend_Error_Handler {

  /**
   * Generate a WP_Error object for unauthorized access.
   *
   * @return WP_Error
   */
  public static function unauthorized_error() {
    return new WP_Error('unauthorized', 'Unauthorized access.', array('status' => 401));
  }

  /**
   * Generate a WP_Error object for missing parameter.
   *
   * @param string $param_name Name of the missing parameter.
   * @return WP_Error
   */
  public static function missing_parameter_error($param_name) {
    return new WP_Error('missing_parameter', "Missing parameter: $param_name.", array('status' => 400));
  }

  /**
   * Generate a WP_Error object for invalid parameter.
   *
   * @param string $param_name Name of the invalid parameter.
   * @return WP_Error
   */
  public static function invalid_parameter_error($param_name) {
    return new WP_Error('invalid_parameter', "Invalid parameter: $param_name.", array('status' => 400));
  }

  /**
   * Generate a WP_Error object for general server error.
   *
   * @param string $message Error message.
   * @return WP_Error
   */
  public static function server_error($message = 'An unexpected error occurred.') {
    return new WP_Error('server_error', $message, array('status' => 500));
  }

  /**
   * Generate a WP_Error object for not found error.
   *
   * @return WP_Error
   */
  public static function not_found_error() {
    return new WP_Error('not_found', 'Resource not found.', array('status' => 404));
  }
}
