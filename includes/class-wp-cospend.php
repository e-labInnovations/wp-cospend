<?php

if (!defined('ABSPATH')) {
  exit;
}

class WPCospend {
  public function init() {
    add_action('rest_api_init', array($this, 'register_rest_routes'));
  }

  public function register_rest_routes() {
    // Register REST API routes.
    require_once WP_COSPEND_PLUGIN_DIR . 'includes/class-wp-cospend-rest-api.php';
    $rest_api = new WPCospend_REST_API();
    $rest_api->init();
  }
}