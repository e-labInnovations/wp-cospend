<?php

if (!defined('ABSPATH')) {
  exit;
}

class WPCospend_REST_API {
  private $db;

  public function __construct() {
    $this->db = new WPCospend_DB();
  }

  public function init() {
    // Register routes for members, groups, expenses, transactions, and attachments.
    register_rest_route(WP_COSPEND_REST_NAMESPACE, '/members', array(
      'methods'  => 'GET',
      'callback' => array($this, 'get_members'),
      'permission_callback' => array($this, 'permission_access_cospend')
    ));

    register_rest_route(WP_COSPEND_REST_NAMESPACE, '/members', array(
      'methods'  => 'POST',
      'callback' => array($this, 'create_user'),
      'permission_callback' => array($this, 'permission_access_cospend')
    ));

    register_rest_route(WP_COSPEND_REST_NAMESPACE, '/members/me', array(
      'methods'  => 'GET',
      'callback' => array($this, 'get_current_user'),
      'permission_callback' => array($this, 'permission_access_cospend')
    ));

    register_rest_route(WP_COSPEND_REST_NAMESPACE, '/members/(?P<id>\d+)', array(
      'methods'  => 'PUT',
      'callback' => array($this, 'update_user'),
      'permission_callback' => array($this, 'permission_access_cospend')
    ));

    register_rest_route(WP_COSPEND_REST_NAMESPACE, '/members/(?P<id>\d+)', array(
      'methods'  => 'DELETE',
      'callback' => array($this, 'delete_user'),
      'permission_callback' => array($this, 'permission_access_cospend')
    ));

    // Add other routes for groups, expenses, transactions, attachments, etc.
  }

  public function get_members(WP_REST_Request $request) {
    $members = $this->db->get_all_members();

    if (is_wp_error($members)) {
      return $members;
    }

    return rest_ensure_response(array(
      'success' => true,
      'data' => $members
    ));
  }

  public function get_current_user($request) {
    $current_user = wp_get_current_user();
    if (!$current_user) {
      return new WP_Error('unauthorized', 'You are not logged in.', array('status' => 401));
    }

    if (!($this->db->member_exists($current_user->ID))) {
      $data = array(
        'display_name' => $current_user->display_name,
        'member_email' => $current_user->user_email,
        'wp_user_id'   => $current_user->ID,
        'picture_id'   => null
      );

      $member_id = $this->db->insert_member($data);

      if (is_wp_error($member_id)) {
        return $member_id;
      }
    }

    $data = $this->db->get_member_by_wp_user_id($current_user->ID);
    return rest_ensure_response(array(
      'success' => true,
      'data' => $data
    ));
  }

  public function create_user(WP_REST_Request $request) {
    $display_name = $request->get_param('display_name');
    $member_email = $request->get_param('member_email');
    $wp_user_id = $request->get_param('wp_user_id');
    $picture_id = $request->get_param('picture_id');

    // Validate required parameters
    if (empty($display_name)) {
      return WPCospend_Error_Handler::missing_parameter_error('display_name');
    }

    // Check if the WordPress user exists
    $user = get_userdata($wp_user_id);
    if ($wp_user_id && !$user) {
      return new WP_Error('wp_user_id_not_exist', 'A user with this wp_user_id does not exist', array('status' => 400));
    }

    // Check if the member already exists
    if ($this->db->member_exists($wp_user_id)) {
      return new WP_Error('member_exists', 'A member with this wp_user_id already exists', array('status' => 400));
    }

    // Prepare data for insertion
    $data = array(
      'display_name' => sanitize_text_field($display_name),
      'member_email' => !empty($member_email) ? sanitize_email($member_email) : null,
      'wp_user_id'   => !empty($wp_user_id) ? intval($wp_user_id) : null,
      'picture_id'   => !empty($picture_id) ? intval($picture_id) : null
    );

    // Insert member into the database
    $member_id = $this->db->insert_member($data);

    if (is_wp_error($member_id)) {
      return $member_id;
    }

    return rest_ensure_response(array(
      'success' => true,
      'member_id' => $member_id
    ));
  }

  public function update_user(WP_REST_Request $request) {
    // Implement code to update a user.
  }

  public function delete_user(WP_REST_Request $request) {
    // Implement code to delete a user.
  }

  // Add methods for other routes.

  public function permission_access_cospend($request) {
    if (is_user_logged_in()) {
      return true;
    } else {
      return WPCospend_Error_Handler::unauthorized_error();
    }
  }
}
