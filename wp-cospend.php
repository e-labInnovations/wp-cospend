<?php

/**
 * Plugin Name: WP Cospend
 * Plugin URI: https://github.com/elabins/wp-cospend
 * Description: Manage your shared expenses easily
 * Version: 1.0.0
 * Author: Mohammed Ashad
 * Author URI: https://elabins.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wp-cospend
 * Domain Path: /languages
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
  die;
}

// Plugin version
define('WP_COSPEND_VERSION', '1.0.0');
define('WP_COSPEND_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WP_COSPEND_PLUGIN_URL', plugin_dir_url(__FILE__));

// Autoloader for plugin classes
spl_autoload_register(function ($class) {
  $prefix = 'WPCospend\\';
  $base_dir = WP_COSPEND_PLUGIN_DIR . 'includes/';

  $len = strlen($prefix);
  if (strncmp($prefix, $class, $len) !== 0) {
    return;
  }

  $relative_class = substr($class, $len);
  $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

  if (file_exists($file)) {
    require $file;
  }
});

// Activation hook
register_activation_hook(__FILE__, 'wp_cospend_activate');
function wp_cospend_activate() {
  require_once WP_COSPEND_PLUGIN_DIR . 'includes/class-activator.php';
  WPCospend\Activator::activate();
}

// Deactivation hook
register_deactivation_hook(__FILE__, 'wp_cospend_deactivate');
function wp_cospend_deactivate() {
  require_once WP_COSPEND_PLUGIN_DIR . 'includes/class-deactivator.php';
  WPCospend\Deactivator::deactivate();
}

// Initialize plugin
function wp_cospend_init() {
  // Load plugin text domain
  load_plugin_textdomain('wp-cospend', false, dirname(plugin_basename(__FILE__)) . '/languages');

  // Initialize file manager
  require_once WP_COSPEND_PLUGIN_DIR . 'includes/class-file-manager.php';
  WPCospend\File_Manager::init();

  // Initialize image manager
  require_once WP_COSPEND_PLUGIN_DIR . 'includes/class-image-manager.php';
  WPCospend\Image_Manager::init();

  // Initialize account manager
  require_once WP_COSPEND_PLUGIN_DIR . 'includes/class-account-manager.php';
  WPCospend\Account_Manager::init();

  // Initialize member manager
  require_once WP_COSPEND_PLUGIN_DIR . 'includes/class-member-manager.php';
  WPCospend\Member_Manager::init();

  // Initialize group manager
  require_once WP_COSPEND_PLUGIN_DIR . 'includes/class-group-manager.php';
  WPCospend\Group_Manager::init();

  // Initialize category manager
  require_once WP_COSPEND_PLUGIN_DIR . 'includes/class-category-manager.php';
  WPCospend\Category_Manager::init();

  // Initialize transaction manager
  require_once WP_COSPEND_PLUGIN_DIR . 'includes/class-transaction-manager.php';
  WPCospend\Transaction_Manager::init();

  // Initialize REST API
  add_action('rest_api_init', 'wp_cospend_register_rest_routes');
}
add_action('plugins_loaded', 'wp_cospend_init');

// Register REST API routes
function wp_cospend_register_rest_routes() {
  // Account endpoints
  require_once WP_COSPEND_PLUGIN_DIR . 'includes/api/class-account-controller.php';
  $account_controller = new WPCospend\API\Account_Controller();
  $account_controller->register_routes();

  // Member endpoints
  require_once WP_COSPEND_PLUGIN_DIR . 'includes/api/class-member-controller.php';
  $member_controller = new WPCospend\API\Member_Controller();
  $member_controller->register_routes();

  // Group endpoints
  require_once WP_COSPEND_PLUGIN_DIR . 'includes/api/class-group-controller.php';
  $group_controller = new WPCospend\API\Group_Controller();
  $group_controller->register_routes();

  // Category endpoints
  require_once WP_COSPEND_PLUGIN_DIR . 'includes/api/class-category-controller.php';
  $category_controller = new WPCospend\API\Category_Controller();
  $category_controller->register_routes();

  // Transaction endpoints
  require_once WP_COSPEND_PLUGIN_DIR . 'includes/api/class-transaction-controller.php';
  $transaction_controller = new WPCospend\API\Transaction_Controller();
  $transaction_controller->register_routes();
}

// Add plugin settings link
function wp_cospend_add_settings_link($links) {
  $settings_link = '<a href="admin.php?page=wp-cospend">' . __('Settings', 'wp-cospend') . '</a>';
  array_unshift($links, $settings_link);
  return $links;
}
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'wp_cospend_add_settings_link');
