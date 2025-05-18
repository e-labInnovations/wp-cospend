<?php

namespace WPCospend\API;

use WP_REST_Controller;
use WP_REST_Server;
use WP_Error;
use WPCospend\Category_Manager;
use WPCospend\Image_Manager;
use WPCospend\ImageEntityType;

class Category_Controller extends WP_REST_Controller {
  /**
   * Constructor.
   */
  public function __construct() {
    $this->namespace = 'wp-cospend/v1';
    $this->rest_base = 'categories';
  }

  /**
   * Register the routes for the objects of the controller.
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

    // Category routes
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
              'description' => __('Unique identifier for the category.', 'wp-cospend'),
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
              'description' => __('Unique identifier for the category.', 'wp-cospend'),
              'type' => 'integer',
            ),
          ),
        ),
      )
    );

    // Child categories route
    register_rest_route(
      $this->namespace,
      '/' . $this->rest_base . '/(?P<id>[\d]+)/children',
      array(
        array(
          'methods' => WP_REST_Server::READABLE,
          'callback' => array($this, 'get_child_items'),
          'permission_callback' => array($this, 'get_item_permissions_check'),
          'args' => array(
            'id' => array(
              'description' => __('Unique identifier for the parent category.', 'wp-cospend'),
              'type' => 'integer',
            ),
          ),
        ),
      )
    );
  }

  /**
   * Check if user is admin.
   *
   * @return bool
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

    $category = Category_Manager::get_category($request->get_param('id'));
    if (is_wp_error($category)) {
      return $category;
    }

    // Admin can access any category
    if (current_user_can('manage_options')) {
      return true;
    }

    // Regular users can only access categories they created or are the default categories
    return $category && ((int)$category['created_by'] === get_current_user_id() || (int)$category['created_by'] === 0);
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

    $category = Category_Manager::get_category($request->get_param('id'));
    if (is_wp_error($category)) {
      return $category;
    }

    // Admin can access any category
    if (current_user_can('manage_options')) {
      return true;
    }

    // Regular users can only modify categories they created
    return $category && (int)$category['created_by'] === get_current_user_id();
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

    $category = Category_Manager::get_category($request->get_param('id'));
    if (is_wp_error($category)) {
      return $category;
    }

    // Admin can access any category
    if (current_user_can('manage_options')) {
      return true;
    }

    // Regular users can only delete categories they created
    return $category && (int)$category['created_by'] === get_current_user_id();
  }

  /**
   * Get all categories (admin only).
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|WP_REST_Response
   */
  public function get_all_items($request) {
    $categories = Category_Manager::get_all_categories();

    if (is_wp_error($categories)) {
      return $categories;
    }

    return rest_ensure_response($categories);
  }

  /**
   * Get a collection of categories created by the current user.
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|WP_REST_Response
   */
  public function get_items($request) {
    $categories = Category_Manager::get_user_categories(get_current_user_id());

    if (is_wp_error($categories)) {
      return $categories;
    }

    return rest_ensure_response($categories);
  }

  /**
   * Get one category from the collection.
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|WP_REST_Response
   */
  public function get_item($request) {
    $category = Category_Manager::get_category($request->get_param('id'));

    if (is_wp_error($category)) {
      return $category;
    }

    return rest_ensure_response($category);
  }

  /**
   * Create one category from the collection.
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|WP_REST_Response
   */
  public function create_item($request) {
    $params = $request->get_params();
    $name = sanitize_text_field($params['name']);
    $color = sanitize_text_field($params['color']) ?? '#FFFFFF';
    $parent_id = isset($params['parent_id']) ? intval($params['parent_id']) : null;
    $icon_type = isset($params['icon_type']) ? sanitize_text_field($params['icon_type']) : null;
    $icon_content = isset($params['icon_content']) ? sanitize_text_field($params['icon_content']) : null;
    $type = isset($params['type']) ? sanitize_text_field($params['type']) : null;

    // Validate required fields
    if (empty($name)) {
      return Category_Manager::get_error('no_name');
    }

    if (empty($color)) {
      return Category_Manager::get_error('no_color');
    }

    // Validate color format
    if (!preg_match('/^#[a-f0-9]{6}$/i', $color)) {
      return Category_Manager::get_error('invalid_color_format');
    }

    // validate category type
    if (!in_array($type, array('expense', 'income', 'transfer'))) {
      return Category_Manager::get_error('invalid_type');
    }

    // Check if parent category exists and user has access
    if ($parent_id) {
      $parent = Category_Manager::get_category($parent_id);
      if (is_wp_error($parent)) {
        return $parent;
      }

      // currenly we allow users to create subcategories in any category
      // if ((int)$parent->created_by !== get_current_user_id() && !current_user_can('manage_options')) {
      //   return new WP_Error(
      //     'permission_denied',
      //     __('You do not have permission to create a subcategory in this category.', 'wp-cospend'),
      //     array('status' => 403)
      //   );
      // }

      if ($parent['parent_id']) {
        return Category_Manager::get_error('parent_is_subcategory');
      }
    }

    // check if icon_type is valid
    if ($icon_type !== null && !in_array($icon_type, array('file', 'icon'))) {
      return Category_Manager::get_error('invalid_icon_type');
    }

    // check if icon_content is valid for icon type
    if ($icon_type === 'icon' && empty($icon_content)) {
      return Category_Manager::get_error('invalid_icon_content');
    }

    // check if icon_content is valid for file type
    if ($icon_type === 'file' && !isset($_FILES['icon_file'])) {
      return Category_Manager::get_error('invalid_icon_content');
    }

    $category_id = Category_Manager::create_category(
      $name,
      $color,
      $parent_id,
      get_current_user_id()
    );

    if (is_wp_error($category_id)) {
      return $category_id;
    }

    // Handle icon
    if ($icon_type === 'file') {
      $icon_id = Image_Manager::save_image_file(ImageEntityType::Category, $category_id, 'icon_file');
    } else {
      $icon_id = Image_Manager::save_image_icon(ImageEntityType::Category, $category_id, $icon_content);
    }

    if (is_wp_error($icon_id)) {
      return $icon_id;
    }

    $category = Category_Manager::get_category($category_id);
    return rest_ensure_response($category);
  }

  /**
   * Update one category from the collection.
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|WP_REST_Response
   */
  public function update_item($request) {
    $category_id = $request->get_param('id');
    $params = $request->get_params();

    // Check if category exists
    $category = Category_Manager::get_category($category_id);
    if (is_wp_error($category)) {
      return $category;
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
        return new WP_Error(
          'invalid_color',
          __('Invalid color format. Use hex color code (e.g. #FF0000).', 'wp-cospend'),
          array('status' => 400)
        );
      }
      $update_data['color'] = $color;
    }

    // validate category type if provided
    if (isset($params['type'])) {
      $type = sanitize_text_field($params['type']);
      if (!in_array($type, array('expense', 'income', 'transfer'))) {
        return Category_Manager::get_error('invalid_type');
      }
      $update_data['type'] = $type;
    }

    if (isset($params['parent_id'])) {
      $parent_id = intval($params['parent_id']);
      if ($parent_id) {
        $parent = Category_Manager::get_category($parent_id);
        if (is_wp_error($parent)) {
          return $parent;
        }

        if (((int)$parent['created_by'] !== get_current_user_id() || (int)$parent['created_by'] === 0) && !current_user_can('manage_options')) {
          return Category_Manager::get_error('no_permissions');
        }

        if ($parent['parent_id']) {
          return Category_Manager::get_error('parent_is_subcategory');
        }
      }
      $update_data['parent_id'] = $parent_id;
    }

    // Handle icon
    if ($icon_type !== null && ($icon_content !== null || isset($_FILES['icon_file']))) {
      $result = Image_Manager::save_image_file(ImageEntityType::Category, $category_id, 'icon_file');

      if (is_wp_error($result)) {
        return $result;
      }
    }

    $result = Category_Manager::update_category($category_id, $update_data);

    if (is_wp_error($result)) {
      return $result;
    }

    $category = Category_Manager::get_category($category_id);
    return rest_ensure_response($category);
  }

  /**
   * Delete one category from the collection.
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|WP_REST_Response
   */
  public function delete_item($request) {
    $category_id = $request->get_param('id');

    // Check if category exists
    $category = Category_Manager::get_category($category_id);
    if (is_wp_error($category)) {
      return $category;
    }

    // Check if user has permission to delete category
    if ((int)$category['created_by'] !== get_current_user_id() && !current_user_can('manage_options')) {
      return Category_Manager::get_error('no_permissions');
    }

    $result = Category_Manager::delete_category($category_id);

    if (is_wp_error($result)) {
      return $result;
    }

    return rest_ensure_response(array(
      'message' => __('Category deleted successfully.', 'wp-cospend'),
      'id' => $category_id
    ));
  }

  /**
   * Get child categories for a parent category.
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|WP_REST_Response
   */
  public function get_child_items($request) {
    $parent_id = $request->get_param('id');

    // Check if parent category exists
    $parent = Category_Manager::get_category($parent_id);
    if (is_wp_error($parent)) {
      return $parent;
    }

    $children = Category_Manager::get_child_categories($parent_id);

    if (is_wp_error($children)) {
      return $children;
    }

    return rest_ensure_response($children);
  }

  /**
   * Get the category schema, conforming to JSON Schema.
   *
   * @return array
   */
  public function get_item_schema() {
    return array(
      '$schema' => 'http://json-schema.org/draft-04/schema#',
      'title' => 'category',
      'type' => 'object',
      'properties' => array(
        'id' => array(
          'description' => __('Unique identifier for the category.', 'wp-cospend'),
          'type' => 'integer',
          'readonly' => true,
        ),
        'name' => array(
          'description' => __('The name of the category.', 'wp-cospend'),
          'type' => 'string',
          'required' => true,
        ),
        'color' => array(
          'description' => __('The color of the category.', 'wp-cospend'),
          'type' => 'string',
          'required' => true,
        ),
        'parent_id' => array(
          'description' => __('The ID of the parent category.', 'wp-cospend'),
          'type' => 'integer',
          'nullable' => true,
        ),
        'icon' => array(
          'description' => __('The icon name for the category.', 'wp-cospend'),
          'type' => 'string',
        ),
        'created_by' => array(
          'description' => __('The ID of the user who created this category.', 'wp-cospend'),
          'type' => 'integer',
          'readonly' => true,
        ),
        'created_at' => array(
          'description' => __('The date the category was created.', 'wp-cospend'),
          'type' => 'string',
          'format' => 'date-time',
          'readonly' => true,
        ),
        'updated_at' => array(
          'description' => __('The date the category was last updated.', 'wp-cospend'),
          'type' => 'string',
          'format' => 'date-time',
          'readonly' => true,
        ),
      ),
    );
  }
}
