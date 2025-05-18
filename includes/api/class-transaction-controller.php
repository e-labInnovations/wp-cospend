<?php

namespace WPCospend\API;

use WP_REST_Controller;
use WP_REST_Server;
use WP_REST_Response;
use WP_Error;

class Transaction_Controller extends WP_REST_Controller {
  /**
   * Constructor.
   */
  public function __construct() {
    $this->namespace = 'wp-cospend/v1';
    $this->rest_base = 'transactions';
  }

  /**
   * Register the routes for the objects of the controller.
   */
  public function register_routes() {
    register_rest_route(
      $this->namespace,
      $this->rest_base,
      array(
        array(
          'methods' => \WP_REST_Server::READABLE,
          'callback' => array($this, 'get_transactions'),
          'permission_callback' => array($this, 'get_transactions_permissions_check'),
        ),
        array(
          'methods' => \WP_REST_Server::CREATABLE,
          'callback' => array($this, 'create_transaction'),
          'permission_callback' => array($this, 'create_transaction_permissions_check'),
        ),
      )
    );
  }

  /**
   * Check if user has permission to read transactions.
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return bool
   */
  public function get_transactions_permissions_check($request) {
    // TODO: Check if user has permission to read transactions.
    return true;
  }

  /**
   * Check if user has permission to create a transaction.
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return bool
   */
  public function create_transaction_permissions_check($request) {
    // TODO: Check if user has permission to create a transaction.
    return true;
  }

  /**
   * Get transactions.
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_REST_Response
   */
  public function get_transactions($request) {
    // TODO: Get transactions.
    return new WP_REST_Response(array('message' => 'Transactions fetched successfully'), 200);
  }
}
