<?php

/**
 * Plugin Name: WP Cospend
 * Description: A plugin for managing expenses and transactions, similar to NextCloud Cospend.
 * Version: 1.0.0
 * Author: Mohammed Ashad
 * Author URI:  https://github.com/e-labInnovations
 * License: GPL-2.0+
 * Text Domain: wp-cospend
 * Domain Path: /languages
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
  exit;
}

// Define plugin constants.
define('WP_COSPEND_VERSION', '1.0.0');
define('WP_COSPEND_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WP_COSPEND_REST_NAMESPACE', 'wp-cospend/v1');

// Include necessary files.
require_once WP_COSPEND_PLUGIN_DIR . 'includes/class-wp-cospend.php';
require_once WP_COSPEND_PLUGIN_DIR . 'includes/class-wp-cospend-db.php';
require_once WP_COSPEND_PLUGIN_DIR . 'includes/class-wp-cospend-rest-api.php';
require_once WP_COSPEND_PLUGIN_DIR . 'includes/class-wp-cospend-error-handler.php';

// Initialize the plugin.
$wp_cospend = new WPCospend();
$wp_cospend->init();

// Activation hook
register_activation_hook(__FILE__, 'wp_cospend_activate');

function wp_cospend_activate() {
  WPCospend_DB::create_tables();
}