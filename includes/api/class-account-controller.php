<?php

namespace WPCospend\API;

use WP_REST_Controller;
use WP_REST_Server;
use WP_Error;
use WPCospend\Account_Manager;
use WPCospend\AccountVisibility;
use WPCospend\Image_Manager;
use WPCospend\ImageEntityType;
use WPCospend\Member_Manager;

class Account_Controller extends WP_REST_Controller {
  /**
   * Constructor.
   */
  public function __construct() {
    $this->namespace = 'wp-cospend/v1';
    $this->rest_base = 'accounts';
  }

  /**
   * Register routes.
   */
  public function register_routes() {
    // Admin routes
    register_rest_route(
      $this->namespace,
      '/admin/' . $this->rest_base,
      array(
        array(
          'methods' => WP_REST_Server::READABLE,
          'callback' => array($this, 'get_all_items'),
          'permission_callback' => array($this, 'admin_permissions_check'),
        ),
      )
    );

    // Get accounts of a member
    register_rest_route(
      $this->namespace,
      '/members/' . $this->rest_base,
      array(
        array(
          'methods' => WP_REST_Server::READABLE,
          'callback' => array($this, 'get_user_items'),
          'permission_callback' => array($this, 'get_items_permissions_check'),
        ),
      )
    );

    // Regular user routes
    register_rest_route(
      $this->namespace,
      '/' . $this->rest_base,
      array(
        array(
          'methods' => WP_REST_Server::READABLE,
          'callback' => array($this, 'get_items'),
          'permission_callback' => array($this, 'get_items_permissions_check'),
        ),
        array(
          'methods' => WP_REST_Server::CREATABLE,
          'callback' => array($this, 'create_item'),
          'permission_callback' => array($this, 'create_item_permissions_check'),
          'args' => $this->get_endpoint_args_for_item_schema(WP_REST_Server::CREATABLE),
        ),
      )
    );

    // Tag routes
    register_rest_route(
      $this->namespace,
      '/' . $this->rest_base . '/(?P<id>[\d]+)',
      array(
        array(
          'methods' => WP_REST_Server::READABLE,
          'callback' => array($this, 'get_item'),
          'permission_callback' => array($this, 'get_item_permissions_check'),
          'args' => array(
            'id' => array(
              'description' => __('Unique identifier for the account.', 'wp-cospend'),
              'type' => 'integer',
            ),
          ),
        ),
        array(
          'methods' => WP_REST_Server::EDITABLE,
          'callback' => array($this, 'update_item'),
          'permission_callback' => array($this, 'update_item_permissions_check'),
          'args' => $this->get_endpoint_args_for_item_schema(WP_REST_Server::EDITABLE),
        ),
        array(
          'methods' => WP_REST_Server::DELETABLE,
          'callback' => array($this, 'delete_item'),
          'permission_callback' => array($this, 'delete_item_permissions_check'),
          'args' => array(
            'id' => array(
              'description' => __('Unique identifier for the account.', 'wp-cospend'),
              'type' => 'integer',
            ),
          ),
        ),
      )
    );
  }

  /**
   * Check if the user has admin permissions.
   *
   * @return bool True if the user has admin permissions, false otherwise.
   */
  public function admin_permissions_check() {
    return current_user_can('manage_options');
  }

  /**
   * Check if a given request has access to read categories.
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|bool
   */
  public function get_items_permissions_check($request) {
    return is_user_logged_in();
  }

  /**
   * Check if a given request has access to create a category.
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|bool
   */
  public function create_item_permissions_check($request) {
    return is_user_logged_in();
  }

  /**
   * Check if a given request has access to read a category.
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|bool
   */
  public function get_item_permissions_check($request) {
    if (!is_user_logged_in()) {
      return false;
    }

    $account = Account_Manager::get_account($request->get_param('id'));
    if (is_wp_error($account)) {
      return $account;
    }

    // Admin can access any accounts
    if (current_user_can('manage_options')) {
      return true;
    }

    // ToDo: Implement this logic carefully
    return true;
  }

  /**
   * Check if a given request has access to update a category.
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|bool
   */
  public function update_item_permissions_check($request) {
    if (!is_user_logged_in()) {
      return false;
    }

    $account = Account_Manager::get_account($request->get_param('id'));
    if (is_wp_error($account)) {
      return $account;
    }

    // Admin can access any category
    if (current_user_can('manage_options')) {
      return true;
    }

    // Won't allow edit for virtual account
    if ($account['is_virtual'] === true) {
      return Account_Manager::get_error('virtual_account');
    }

    // Regular users can only modify accounts they created
    return $account && (int)$account['created_by'] === get_current_user_id();
  }

  /**
   * Check if a given request has access to delete a category.
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|bool
   */
  public function delete_item_permissions_check($request) {
    if (!is_user_logged_in()) {
      return false;
    }

    $account = Account_Manager::get_account($request->get_param('id'));
    if (is_wp_error($account)) {
      return $account;
    }

    // Admin can access any account
    if (current_user_can('manage_options')) {
      return true;
    }

    // Won't allow delete for virtual account
    if ($account['is_virtual'] === true) {
      return Account_Manager::get_error('virtual_account_delete');
    }

    // Regular users can only delete accounts they created
    return $account && (int)$account['created_by'] === get_current_user_id();
  }

  /**
   * Get all accounts (admin only).
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|WP_REST_Response
   */
  public function get_all_items($request) {
    $accounts = Account_Manager::get_all_accounts();

    if (is_wp_error($accounts)) {
      return $accounts;
    }

    return rest_ensure_response($accounts);
  }

  /**
   * Get a collection of accounts created by the current user.
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|WP_REST_Response
   */
  public function get_items($request) {
    $accounts = Account_Manager::get_user_accounts(get_current_user_id(), AccountVisibility::Private);

    if (is_wp_error($accounts)) {
      return $accounts;
    }

    return rest_ensure_response($accounts);
  }

  /**
   * Get a collection of accounts created by the current user.
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|WP_REST_Response
   */
  public function get_user_items($request) {
    $user_id = $request->get_param('user_id');
    $scope = $request->get_param('scope');
    $current_user_id = get_current_user_id();

    if ($scope !== 'friend' && $scope !== 'group') {
      return Account_Manager::get_error('invalid_scope');
    }

    // Check if the current user is a friend of the user
    // TODO: Implement this

    // Check if the current user is a group member of the user
    // TODO: Implement this
    $visibility = $scope === 'friend' ? AccountVisibility::Friends : AccountVisibility::Group;

    $accounts = Account_Manager::get_user_accounts($user_id, $visibility);

    if (is_wp_error($accounts)) {
      return $accounts;
    }

    return rest_ensure_response($accounts);
  }

  /**
   * Get an account by ID.
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|WP_REST_Response
   */
  public function get_item($request) {
    $account = Account_Manager::get_account($request->get_param('id'));

    if (is_wp_error($account)) {
      return $account;
    }

    return rest_ensure_response($account);
  }

  /**
   * Create an account.
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|WP_REST_Response
   */
  public function create_item($request) {
    $params = $request->get_params();
    $name = sanitize_text_field($params['name']);
    $description = sanitize_text_field($params['description']);
    $created_by = get_current_user_id();
    $private_name = sanitize_text_field($params['private_name']);
    $visibility = sanitize_text_field($params['visibility']);
    $icon_type = isset($params['icon_type']) ? sanitize_text_field($params['icon_type']) : null;
    $icon_content = isset($params['icon_content']) ? sanitize_text_field($params['icon_content']) : null;
    $is_default = false;
    $is_active = true;

    if (empty($name)) {
      return Account_Manager::get_error('no_name');
    }

    if (empty($private_name)) {
      return Account_Manager::get_error('no_private_name');
    }

    if (empty($visibility)) {
      return Account_Manager::get_error('no_visibility');
    }

    if ($visibility !== AccountVisibility::Group->value && $visibility !== AccountVisibility::Friends->value && $visibility !== AccountVisibility::Private->value) {
      return Account_Manager::get_error('invalid_visibility');
    }

    // check if icon_type is valid
    if ($icon_type !== null && !in_array($icon_type, array('file', 'icon'))) {
      return Account_Manager::get_error('invalid_icon_type');
    }

    // check if icon_content is valid for icon type
    if ($icon_type === 'icon' && empty($icon_content)) {
      return Account_Manager::get_error('invalid_icon_content');
    }

    // check if icon_content is valid for file type
    if ($icon_type === 'file' && !isset($_FILES['icon_file'])) {
      return Account_Manager::get_error('invalid_icon_content');
    }

    $member = Member_Manager::get_current_user_member();
    if (is_wp_error($member)) {
      return $member;
    }
    $member_id = $member['id'];

    $visibility_value = AccountVisibility::from($visibility);

    $account_id = Account_Manager::create_account($name, $description, $created_by, $member_id, $private_name, $is_default, $visibility_value, $is_active);

    if (is_wp_error($account_id)) {
      return $account_id;
    }

    // Handle icon
    if ($icon_type === 'file') {
      $icon_id = Image_Manager::save_image_file(ImageEntityType::Account, $account_id, 'icon_file');
    } else {
      $icon_id = Image_Manager::save_image_icon(ImageEntityType::Account, $account_id, $icon_content);
    }

    if (is_wp_error($icon_id)) {
      return $icon_id;
    }

    $default_account = Account_Manager::get_user_default_account(get_current_user_id());
    if (is_wp_error($default_account)) {
      return $default_account;
    }

    // If the default account is virtual, we need to set the new account as default
    if ($default_account['is_virtual'] === true) {
      Account_Manager::set_user_default_account(get_current_user_id(), $account_id);
    }

    $account = Account_Manager::get_account($account_id);
    if (is_wp_error($account)) {
      return $account;
    }

    return rest_ensure_response($account);
  }

  /**
   * Update an account.
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|WP_REST_Response
   */
  public function update_item($request) {
    $account_id = $request->get_param('id');
    $params = $request->get_params();

    $account = Account_Manager::get_account($account_id);
    if (is_wp_error($account)) {
      return $account;
    }

    // checking if the account is virtual is already handled in the permissions check

    $icon_type = isset($params['icon_type']) ? sanitize_text_field($params['icon_type']) : null;
    $icon_content = isset($params['icon_content']) ? sanitize_text_field($params['icon_content']) : null;

    $update_data = array();

    if (isset($params['name'])) {
      $update_data['name'] = sanitize_text_field($params['name']);
    }

    if (isset($params['description'])) {
      $update_data['description'] = sanitize_text_field($params['description']);
    }

    if (isset($params['private_name'])) {
      $update_data['private_name'] = sanitize_text_field($params['private_name']);
    }

    if (isset($params['visibility'])) {
      $visibility = sanitize_text_field($params['visibility']);
      if ($visibility !== AccountVisibility::Group->value && $visibility !== AccountVisibility::Friends->value && $visibility !== AccountVisibility::Private->value) {
        return Account_Manager::get_error('invalid_visibility');
      }

      $update_data['visibility'] = AccountVisibility::from($visibility);
    }

    // Handle icon
    if ($icon_type !== null && ($icon_content !== null || isset($_FILES['icon_file']))) {
      $result = Image_Manager::save_image_file(ImageEntityType::Account, $account_id, 'icon_file');

      if (is_wp_error($result)) {
        return $result;
      }
    }

    $result = Account_Manager::update_account($account_id, $update_data);

    if (is_wp_error($result) && $result->get_error_code() !== 'no_changes') {
      return $result;
    }

    // check if it setting the account as default
    if (isset($params['is_default']) && $params['is_default'] === true) {
      $default_account = Account_Manager::get_user_default_account(get_current_user_id());
      if (is_wp_error($default_account)) {
        return $default_account;
      }

      Account_Manager::set_user_default_account(get_current_user_id(), $account_id);
    }

    $account = Account_Manager::get_account($account_id);
    if (is_wp_error($account)) {
      return $account;
    }

    return rest_ensure_response($account);
  }

  /**
   * Delete an account.
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|WP_REST_Response
   */
  public function delete_item($request) {
    $account_id = $request->get_param('id');

    $account = Account_Manager::get_account($account_id);
    if (is_wp_error($account)) {
      return $account;
    }

    // checking if the account is virtual is already handled in the permissions check

    $result = Account_Manager::delete_account($account_id);
    if (is_wp_error($result)) {
      return $result;
    }

    return rest_ensure_response(array(
      'message' => 'Account deleted successfully',
      'id' => $account_id
    ));
  }
}
