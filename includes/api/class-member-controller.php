<?php

namespace WPCospend\API;

use WP_REST_Controller;
use WP_REST_Server;
use WP_Error;
use WPCospend\Member_Manager;
use WPCospend\Image_Manager;
use WPCospend\ImageEntityType;

class Member_Controller extends WP_REST_Controller {
  /**
   * Constructor.
   */
  public function __construct() {
    $this->namespace = 'wp-cospend/v1';
    $this->rest_base = 'members';
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

    // Me route
    register_rest_route(
      $this->namespace,
      '/' . $this->rest_base . '/me',
      array(
        array(
          'methods' => WP_REST_Server::READABLE,
          'callback' => array($this, 'get_me'),
          'permission_callback' => array($this, 'get_items_permissions_check'),
        ),
      )
    );

    // Get user by email route
    register_rest_route(
      $this->namespace,
      '/' . $this->rest_base . '/user-by-email',
      array(
        array(
          'methods' => WP_REST_Server::READABLE,
          'callback' => array($this, 'get_user_by_email'),
          'permission_callback' => array($this, 'get_items_permissions_check'),
          'args' => array(
            'email' => array(
              'description' => __('Email address to search for.', 'wp-cospend'),
              'type' => 'string',
              'required' => true,
              'format' => 'email',
            ),
          ),
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

    // Member routes
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
              'description' => __('Unique identifier for the member.', 'wp-cospend'),
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
              'description' => __('Unique identifier for the member.', 'wp-cospend'),
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
   * Check if a given request has access to read members.
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|bool
   */
  public function get_items_permissions_check($request) {
    return is_user_logged_in();
  }

  /**
   * Check if a given request has access to create a member.
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|bool
   */
  public function create_item_permissions_check($request) {
    return is_user_logged_in();
  }

  /**
   * Check if a given request has access to read a member.
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|bool
   */
  public function get_item_permissions_check($request) {
    if (!is_user_logged_in()) {
      return false;
    }

    $member = Member_Manager::get_member($request->get_param('id'));
    if (is_wp_error($member)) {
      return $member;
    }

    // Admin can access any member
    if (current_user_can('manage_options')) {
      return true;
    }

    // Regular users can only access members they created
    return (int)$member['created_by'] === get_current_user_id();
  }

  /**
   * Check if a given request has access to update a member.
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|bool
   */
  public function update_item_permissions_check($request) {
    return $this->get_item_permissions_check($request);
  }

  /**
   * Check if a given request has access to delete a member.
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|bool
   */
  public function delete_item_permissions_check($request) {
    return $this->get_item_permissions_check($request);
  }

  /**
   * Get current user's member info.
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|WP_REST_Response
   */
  public function get_me($request) {
    $member = Member_Manager::get_current_user_member();
    if (is_wp_error($member)) {
      return $member;
    }

    return rest_ensure_response($member);
  }

  /**
   * Get all members (admin only).
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|WP_REST_Response
   */
  public function get_all_items($request) {
    $members = Member_Manager::get_all_members();
    if (is_wp_error($members)) {
      return $members;
    }

    return rest_ensure_response($members);
  }

  /**
   * Get a collection of members created by the current user.
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|WP_REST_Response
   */
  public function get_items($request) {
    $members = Member_Manager::get_all_members_created_by(get_current_user_id());
    if (is_wp_error($members)) {
      return $members;
    }

    return rest_ensure_response($members);
  }

  /**
   * Get one member from the collection.
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|WP_REST_Response
   */
  public function get_item($request) {
    $member = Member_Manager::get_member($request->get_param('id'));
    if (is_wp_error($member)) {
      return $member;
    }

    return rest_ensure_response($member);
  }

  /**
   * Create one member from the collection.
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|WP_REST_Response
   */
  public function create_item($request) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'cospend_members';

    $params = $request->get_params();
    $name = sanitize_text_field($params['name']);
    $wp_user_id = isset($params['wp_user_id']) ? intval($params['wp_user_id']) : null;
    $avatar_type = isset($params['avatar_type']) ? sanitize_text_field($params['avatar_type']) : null;
    $avatar_content = isset($params['avatar_content']) ? sanitize_text_field($params['avatar_content']) : null;

    // Validate required fields
    if (empty($name)) {
      return Member_Manager::get_error('missing_name');
    }

    // check if avatar_type is valid
    if ($avatar_type !== null && !in_array($avatar_type, array('file', 'icon'))) {
      return Member_Manager::get_error('invalid_avatar_type');
    }

    // check if avatar_content is valid for icon type
    if ($avatar_type === 'icon' && empty($avatar_content)) {
      return Member_Manager::get_error('invalid_avatar_content');
    }

    // check if avatar_content is valid for file type
    if ($avatar_type === 'file' && !isset($_FILES['avatar_file'])) {
      return Member_Manager::get_error('invalid_avatar_content');
    }

    // Check if member with same name already exists
    $existing = Member_Manager::get_member_by_name($name, get_current_user_id());

    if (!is_wp_error($existing)) {
      return Member_Manager::get_error('duplicate_name');
    }

    $member_id = Member_Manager::create_member($name, $wp_user_id);

    if (is_wp_error($member_id)) {
      return $member_id;
    }

    // Handle avatar
    if ($avatar_type === 'file' && isset($_FILES['avatar_file'])) {
      $avatar_id = Image_Manager::save_image_file(ImageEntityType::Member, $member_id, 'avatar_file');
      if (is_wp_error($avatar_id)) {
        return $avatar_id;
      }
    }
    if ($avatar_type === 'icon' && $avatar_content !== null) {
      $avatar_id = Image_Manager::save_image_icon(ImageEntityType::Member, $member_id, $avatar_content);
      if (is_wp_error($avatar_id)) {
        return $avatar_id;
      }
    }

    $member = Member_Manager::get_member($member_id);
    if (is_wp_error($member)) {
      return $member;
    }

    return rest_ensure_response($member);
  }

  /**
   * Update one member from the collection.
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|WP_REST_Response
   */
  public function update_item($request) {
    $id = $request->get_param('id');
    $params = $request->get_params();
    $wp_user_id = isset($params['wp_user_id']) ? intval($params['wp_user_id']) : null;
    $unlink_wp_user = isset($params['unlink_wp_user']) ? boolval($params['unlink_wp_user']) : null;
    $avatar_type = isset($params['avatar_type']) ? sanitize_text_field($params['avatar_type']) : null;
    $avatar_content = isset($params['avatar_content']) ? sanitize_text_field($params['avatar_content']) : null;

    // Prepare update data
    $update_data = array();

    // Add name if provided
    if (isset($params['name'])) {
      $name = sanitize_text_field($params['name']);
      $member_with_same_name = Member_Manager::get_member_by_name($name, get_current_user_id());
      if (!is_wp_error($member_with_same_name)) {
        return Member_Manager::get_error('duplicate_name');
      }

      $update_data['name'] = $name;
    }

    // Add wp_user_id if provided
    if (isset($params['wp_user_id']) || !is_null($unlink_wp_user)) {
      $update_data['wp_user_id'] = !is_null($unlink_wp_user) ? null : $wp_user_id;
    }

    // Update avatar if provided
    if ($avatar_type !== null && ($avatar_content !== null || isset($_FILES['avatar_file']))) {
      if ($avatar_type === 'file' && isset($_FILES['avatar_file'])) {
        $result = Image_Manager::save_image_file(ImageEntityType::Member, $id, 'avatar_file');
        if (is_wp_error($result)) {
          return $result;
        }
      }

      if ($avatar_type === 'icon' && !empty($avatar_content)) {
        $result = Image_Manager::save_image_icon(ImageEntityType::Member, $id, $avatar_content);
        if (is_wp_error($result)) {
          return $result;
        }
      }
    }

    $result = Member_Manager::update_member($id, $update_data);
    if (is_wp_error($result)) {
      return $result;
    }

    $member = Member_Manager::get_member($id);
    if (is_wp_error($member)) {
      return $member;
    }

    return rest_ensure_response($member);
  }

  /**
   * Delete one member from the collection.
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|WP_REST_Response
   */
  public function delete_item($request) {
    $id = $request->get_param('id');

    // Check if member exists
    $existing = Member_Manager::get_member($id);
    if (is_wp_error($existing)) {
      return $existing;
    }

    $result = Member_Manager::delete_member($id);
    if (is_wp_error($result)) {
      return $result;
    }

    return rest_ensure_response(array(
      'message' => __('Member deleted successfully.', 'wp-cospend'),
      'id' => $id
    ));
  }

  /**
   * Get WordPress user by email.
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|WP_REST_Response
   */
  public function get_user_by_email($request) {
    $email = sanitize_email($request->get_param('email'));

    if (!is_email($email)) {
      return Member_Manager::get_error('invalid_email');
    }

    $user = get_user_by('email', $email);
    if (!$user) {
      return Member_Manager::get_error('user_not_found');
    }

    $user_data = Member_Manager::get_wp_user($user->ID);

    return rest_ensure_response($user_data);
  }

  /**
   * Get the member schema, conforming to JSON Schema.
   *
   * @return array
   */
  public function get_item_schema() {
    return array(
      '$schema' => 'http://json-schema.org/draft-04/schema#',
      'title' => 'member',
      'type' => 'object',
      'properties' => array(
        'id' => array(
          'description' => __('Unique identifier for the member.', 'wp-cospend'),
          'type' => 'integer',
          'readonly' => true,
        ),
        'name' => array(
          'description' => __('The name of the member.', 'wp-cospend'),
          'type' => 'string',
          'required' => true,
        ),
        'wp_user_id' => array(
          'description' => __('The WordPress user ID associated with this member.', 'wp-cospend'),
          'type' => 'integer',
          'nullable' => true,
        ),
        'avatar_type' => array(
          'description' => __('The type of avatar (url, file or icon).', 'wp-cospend'),
          'type' => 'string',
          'enum' => array('url', 'file', 'icon'),
          'required' => false,
        ),
        'avatar_content' => array(
          'description' => __('The avatar content (URL or icon name).', 'wp-cospend'),
          'type' => 'string',
          'required' => false,
        ),
        'avatar' => array(
          'description' => __('The member avatar object.', 'wp-cospend'),
          'type' => 'object',
          'properties' => array(
            'id' => array(
              'description' => __('Unique identifier for the avatar.', 'wp-cospend'),
              'type' => 'integer',
            ),
            'type' => array(
              'description' => __('The type of avatar (url or icon).', 'wp-cospend'),
              'type' => 'string',
              'enum' => array('url', 'icon'),
            ),
            'content' => array(
              'description' => __('The avatar content (URL or icon name).', 'wp-cospend'),
              'type' => 'string',
            ),
          ),
          'readonly' => true,
        ),
        'wp_user' => array(
          'description' => __('The WordPress user details if linked.', 'wp-cospend'),
          'type' => 'object',
          'properties' => array(
            'id' => array(
              'description' => __('WordPress user ID.', 'wp-cospend'),
              'type' => 'integer',
            ),
            'username' => array(
              'description' => __('WordPress username.', 'wp-cospend'),
              'type' => 'string',
            ),
            'email' => array(
              'description' => __('WordPress user email.', 'wp-cospend'),
              'type' => 'string',
            ),
            'display_name' => array(
              'description' => __('WordPress user display name.', 'wp-cospend'),
              'type' => 'string',
            ),
            'avatar_url' => array(
              'description' => __('WordPress user avatar URL.', 'wp-cospend'),
              'type' => 'string',
            ),
            'default_currency' => array(
              'description' => __('The default currency for the member.', 'wp-cospend'),
              'type' => 'string',
            ),
            'roles' => array(
              'description' => __('The roles of the WordPress user.', 'wp-cospend'),
              'type' => 'array',
              'items' => array(
                'type' => 'string',
              ),
            ),
            'first_name' => array(
              'description' => __('The first name of the WordPress user.', 'wp-cospend'),
              'type' => 'string',
            ),
            'last_name' => array(
              'description' => __('The last name of the WordPress user.', 'wp-cospend'),
              'type' => 'string',
            ),
            'nickname' => array(
              'description' => __('The nickname of the WordPress user.', 'wp-cospend'),
              'type' => 'string',
            ),
          ),
          'readonly' => true,
        ),
        'created_by' => array(
          'description' => __('The ID of the user who created this member.', 'wp-cospend'),
          'type' => 'integer',
          'readonly' => true,
        ),
        'created_at' => array(
          'description' => __('The date the member was created.', 'wp-cospend'),
          'type' => 'string',
          'format' => 'date-time',
          'readonly' => true,
        ),
        'updated_at' => array(
          'description' => __('The date the member was last updated.', 'wp-cospend'),
          'type' => 'string',
          'format' => 'date-time',
          'readonly' => true,
        ),
      ),
    );
  }
}
