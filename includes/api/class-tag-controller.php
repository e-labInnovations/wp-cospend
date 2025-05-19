<?php

namespace WPCospend\API;

use WP_REST_Controller;
use WP_REST_Server;
use WP_Error;
use WPCospend\Tags_Manager;
use WPCospend\Image_Manager;
use WPCospend\ImageEntityType;

class Tag_Controller extends WP_REST_Controller {
  /**
   * Constructor.
   */
  public function __construct() {
    $this->namespace = 'wp-cospend/v1';
    $this->rest_base = 'tags';
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
              'description' => __('Unique identifier for the tag.', 'wp-cospend'),
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
              'description' => __('Unique identifier for the tag.', 'wp-cospend'),
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

    $tag = Tags_Manager::get_tag($request->get_param('id'));
    if (is_wp_error($tag)) {
      return $tag;
    }

    // Admin can access any category
    if (current_user_can('manage_options')) {
      return true;
    }

    // Regular users can only access tags they created or are the default tags
    return $tag && ((int)$tag['created_by'] === get_current_user_id() || (int)$tag['created_by'] === 0);
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

    $tag = Tags_Manager::get_tag($request->get_param('id'));
    if (is_wp_error($tag)) {
      return $tag;
    }

    // Admin can access any category
    if (current_user_can('manage_options')) {
      return true;
    }

    // Regular users can only modify tags they created
    return $tag && (int)$tag['created_by'] === get_current_user_id();
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

    $tag = Tags_Manager::get_tag($request->get_param('id'));
    if (is_wp_error($tag)) {
      return $tag;
    }

    // Admin can access any category
    if (current_user_can('manage_options')) {
      return true;
    }

    // Regular users can only delete tags they created
    return $tag && (int)$tag['created_by'] === get_current_user_id();
  }

  /**
   * Get all tags (admin only).
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|WP_REST_Response
   */
  public function get_all_items($request) {
    $tags = Tags_Manager::get_all_tags();

    if (is_wp_error($tags)) {
      return $tags;
    }

    return rest_ensure_response($tags);
  }

  /**
   * Get a collection of tags created by the current user.
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|WP_REST_Response
   */
  public function get_items($request) {
    $tags = Tags_Manager::get_user_tags(get_current_user_id());

    if (is_wp_error($tags)) {
      return $tags;
    }

    return rest_ensure_response($tags);
  }

  /**
   * Get one tag from the collection.
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|WP_REST_Response
   */
  public function get_item($request) {
    $tag = Tags_Manager::get_tag($request->get_param('id'));

    if (is_wp_error($tag)) {
      return $tag;
    }

    return rest_ensure_response($tag);
  }

  /**
   * Create one tag from the collection.
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|WP_REST_Response
   */
  public function create_item($request) {
    $params = $request->get_params();
    $name = sanitize_text_field($params['name']);
    $color = sanitize_text_field($params['color']) ?? '#FFFFFF';
    $icon_type = isset($params['icon_type']) ? sanitize_text_field($params['icon_type']) : null;
    $icon_content = isset($params['icon_content']) ? sanitize_text_field($params['icon_content']) : null;

    // Validate required fields
    if (empty($name)) {
      return Tags_Manager::get_error('no_name');
    }

    if (empty($color)) {
      return Tags_Manager::get_error('no_color');
    }

    // Validate color format
    if (!preg_match('/^#[a-f0-9]{6}$/i', $color)) {
      return Tags_Manager::get_error('invalid_color_format');
    }

    // check if icon_type is valid
    if ($icon_type !== null && !in_array($icon_type, array('file', 'icon'))) {
      return Tags_Manager::get_error('invalid_icon_type');
    }

    // check if icon_content is valid for icon type
    if ($icon_type === 'icon' && empty($icon_content)) {
      return Tags_Manager::get_error('invalid_icon_content');
    }

    // check if icon_content is valid for file type
    if ($icon_type === 'file' && !isset($_FILES['icon_file'])) {
      return Tags_Manager::get_error('invalid_icon_content');
    }

    $tag_id = Tags_Manager::create_tag(
      $name,
      $color,
      get_current_user_id()
    );

    if (is_wp_error($tag_id)) {
      return $tag_id;
    }

    // Handle icon
    if ($icon_type === 'file') {
      $icon_id = Image_Manager::save_image_file(ImageEntityType::Tag, $tag_id, 'icon_file');
    } else {
      $icon_id = Image_Manager::save_image_icon(ImageEntityType::Tag, $tag_id, $icon_content);
    }

    if (is_wp_error($icon_id)) {
      return $icon_id;
    }

    $tag = Tags_Manager::get_tag($tag_id);
    return rest_ensure_response($tag);
  }

  /**
   * Update one tag from the collection.
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|WP_REST_Response
   */
  public function update_item($request) {
    $tag_id = $request->get_param('id');
    $params = $request->get_params();

    // Check if tag exists
    $tag = Tags_Manager::get_tag($tag_id);
    if (is_wp_error($tag)) {
      return $tag;
    }

    $icon_type = isset($params['icon_type']) ? sanitize_text_field($params['icon_type']) : null;
    $icon_content = isset($params['icon_content']) ? sanitize_text_field($params['icon_content']) : null;

    $update_data = array();

    if (isset($params['name'])) {
      $update_data['name'] = sanitize_text_field($params['name']);
    }

    if (isset($params['color'])) {
      $color = sanitize_text_field($params['color']);
      if (!preg_match('/^#[a-f0-9]{6}$/i', $color)) {
        return Tags_Manager::get_error('invalid_color_format');
      }
      $update_data['color'] = $color;
    }

    // Handle icon
    if ($icon_type !== null && ($icon_content !== null || isset($_FILES['icon_file']))) {
      $result = Image_Manager::save_image_file(ImageEntityType::Tag, $tag_id, 'icon_file');

      if (is_wp_error($result)) {
        return $result;
      }
    }

    $result = Tags_Manager::update_tag($tag_id, $update_data);

    if (is_wp_error($result) && $result->get_error_code() !== 'no_changes') {
      return $result;
    }

    $tag = Tags_Manager::get_tag($tag_id);
    return rest_ensure_response($tag);
  }

  /**
   * Delete one category from the collection.
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|WP_REST_Response
   */
  public function delete_item($request) {
    $tag_id = $request->get_param('id');

    // Check if tag exists
    $tag = Tags_Manager::get_tag($tag_id);
    if (is_wp_error($tag)) {
      return $tag;
    }

    $result = Tags_Manager::delete_tag($tag_id);

    if (is_wp_error($result)) {
      return $result;
    }

    return rest_ensure_response(array(
      'message' => __('Tag deleted successfully.', 'wp-cospend'),
      'id' => $tag_id
    ));
  }
}
