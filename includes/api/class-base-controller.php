<?php

namespace WPCospend\API;

use WP_REST_Controller;
use WP_REST_Server;
use WP_Error;

abstract class Base_Controller extends WP_REST_Controller {
  /**
   * The namespace of this controller's route.
   *
   * @var string
   */
  protected $namespace = 'wp-cospend/v1';

  /**
   * The base of this controller's route.
   *
   * @var string
   */
  protected $rest_base;

  /**
   * Register the routes for the objects of the controller.
   */
  abstract public function register_routes();

  /**
   * Check if a given request has access to get items.
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|bool
   */
  public function get_items_permissions_check($request) {
    return current_user_can('edit_posts');
  }

  /**
   * Check if a given request has access to get a specific item.
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|bool
   */
  public function get_item_permissions_check($request) {
    return current_user_can('edit_posts');
  }

  /**
   * Check if a given request has access to create items.
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|bool
   */
  public function create_item_permissions_check($request) {
    return current_user_can('edit_posts');
  }

  /**
   * Check if a given request has access to update a specific item.
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|bool
   */
  public function update_item_permissions_check($request) {
    return current_user_can('edit_posts');
  }

  /**
   * Check if a given request has access to delete a specific item.
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|bool
   */
  public function delete_item_permissions_check($request) {
    return current_user_can('edit_posts');
  }

  /**
   * Get the query params for collections.
   *
   * @return array
   */
  public function get_collection_params() {
    return array(
      'page'     => array(
        'description'       => __('Current page of the collection.', 'wp-cospend'),
        'type'             => 'integer',
        'default'          => 1,
        'sanitize_callback' => 'absint',
        'validate_callback' => 'rest_validate_request_arg',
        'minimum'          => 1,
      ),
      'per_page' => array(
        'description'       => __('Maximum number of items to be returned in result set.', 'wp-cospend'),
        'type'             => 'integer',
        'default'          => 10,
        'minimum'          => 1,
        'maximum'          => 100,
        'sanitize_callback' => 'absint',
        'validate_callback' => 'rest_validate_request_arg',
      ),
      'search'   => array(
        'description'       => __('Limit results to those matching a string.', 'wp-cospend'),
        'type'             => 'string',
        'sanitize_callback' => 'sanitize_text_field',
      ),
    );
  }
}
