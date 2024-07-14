<?php

if (!defined('ABSPATH')) {
  exit;
}

class WPCospend_REST_API {
  public function init() {
    // Register routes for users, groups, expenses, transactions, and attachments.
    register_rest_route(WP_COSPEND_REST_NAMESPACE, '/users', array(
      'methods'  => 'GET',
      'callback' => array($this, 'get_users'),
    ));

    register_rest_route(WP_COSPEND_REST_NAMESPACE, '/users', array(
      'methods'  => 'POST',
      'callback' => array($this, 'create_user'),
    ));

    register_rest_route(WP_COSPEND_REST_NAMESPACE, '/users/(?P<id>\d+)', array(
      'methods'  => 'PUT',
      'callback' => array($this, 'update_user'),
    ));

    register_rest_route(WP_COSPEND_REST_NAMESPACE, '/users/(?P<id>\d+)', array(
      'methods'  => 'DELETE',
      'callback' => array($this, 'delete_user'),
    ));

    // Add other routes for groups, expenses, transactions, attachments, etc.
  }

  public function get_users(WP_REST_Request $request) {
    // Dummy user data
    $users = array(
      array(
        'id' => 1,
        'display_name' => 'John Doe',
        'user_email' => 'john.doe@example.com',
        'wp_user_id' => 101,
        'picture_id' => 1001,
      ),
      array(
        'id' => 2,
        'display_name' => 'Jane Smith',
        'user_email' => 'jane.smith@example.com',
        'wp_user_id' => 102,
        'picture_id' => 1002,
      ),
      array(
        'id' => 3,
        'display_name' => 'Alice Johnson',
        'user_email' => 'alice.johnson@example.com',
        'wp_user_id' => null,
        'picture_id' => null,
      )
    );

    // Return the response
    return new WP_REST_Response($users, 200);
  }

  public function create_user(WP_REST_Request $request) {
    // Implement code to create a user.
  }

  public function update_user(WP_REST_Request $request) {
    // Implement code to update a user.
  }

  public function delete_user(WP_REST_Request $request) {
    // Implement code to delete a user.
  }

  // Add methods for other routes.
}