<?php

namespace WPCospend\API;

use WP_REST_Controller;
use WP_REST_Server;
use WP_Error;

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

    global $wpdb;
    $table_name = $wpdb->prefix . 'cospend_members';
    $id = $request->get_param('id');
    $user_id = get_current_user_id();

    // Admin can access any member
    if (current_user_can('manage_options')) {
      return true;
    }

    // Regular users can only access members they created
    $member = $wpdb->get_row($wpdb->prepare(
      "SELECT * FROM $table_name WHERE id = %d AND created_by = %d",
      $id,
      $user_id
    ));

    return $member !== null;
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
    global $wpdb;
    $table_name = $wpdb->prefix . 'cospend_members';
    $user_id = get_current_user_id();

    $member = $wpdb->get_row($wpdb->prepare(
      "SELECT * FROM $table_name WHERE wp_user_id = %d",
      $user_id
    ));

    if ($member === null) {
      return new WP_Error(
        'member_not_found',
        __('Member not found.', 'wp-cospend'),
        array('status' => 404)
      );
    }

    // Add avatar to response
    $member->avatar = \WPCospend\Member_Manager::get_member_avatar($member->id);

    // Add WordPress user details if linked
    $member->wp_user = \WPCospend\Member_Manager::get_member_wp_user($member->wp_user_id);

    return rest_ensure_response($member);
  }

  /**
   * Get all members (admin only).
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|WP_REST_Response
   */
  public function get_all_items($request) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'cospend_members';

    $members = $wpdb->get_results(
      "SELECT * FROM $table_name ORDER BY name ASC"
    );

    if ($members === null) {
      return new WP_Error(
        'db_error',
        __('Error fetching members.', 'wp-cospend'),
        array('status' => 500)
      );
    }

    // Add avatars and WordPress user details to response
    require_once WP_COSPEND_PLUGIN_DIR . 'includes/class-image-manager.php';
    foreach ($members as $member) {
      $avatar = \WPCospend\Image_Manager::get_image('member', $member->id, 'url');
      if (!$avatar) {
        $avatar = \WPCospend\Image_Manager::get_image('member', $member->id, 'icon');
      }

      if ($avatar) {
        $member->avatar = $avatar;
      }

      // Add WordPress user details if linked
      $member->wp_user = \WPCospend\Member_Manager::get_member_wp_user($member->wp_user_id);
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
    global $wpdb;
    $table_name = $wpdb->prefix . 'cospend_members';
    $user_id = get_current_user_id();

    $members = $wpdb->get_results($wpdb->prepare(
      "SELECT * FROM $table_name WHERE created_by = %d ORDER BY name ASC",
      $user_id
    ));

    if ($members === null) {
      return new WP_Error(
        'db_error',
        __('Error fetching members.', 'wp-cospend'),
        array('status' => 500)
      );
    }

    // Add avatars and WordPress user details to response
    require_once WP_COSPEND_PLUGIN_DIR . 'includes/class-image-manager.php';
    foreach ($members as $member) {
      // Add avatar to response
      $member->avatar = \WPCospend\Member_Manager::get_member_avatar($member->id);

      // Add WordPress user details if linked
      $member->wp_user = \WPCospend\Member_Manager::get_member_wp_user($member->wp_user_id);
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
    global $wpdb;
    $table_name = $wpdb->prefix . 'cospend_members';
    $id = $request->get_param('id');

    $member = $wpdb->get_row($wpdb->prepare(
      "SELECT * FROM $table_name WHERE id = %d",
      $id
    ));

    if ($member === null) {
      return new WP_Error(
        'member_not_found',
        __('Member not found.', 'wp-cospend'),
        array('status' => 404)
      );
    }

    // Add avatar to response
    $member->avatar = \WPCospend\Member_Manager::get_member_avatar($member->id);

    // Add WordPress user details if linked
    $member->wp_user = \WPCospend\Member_Manager::get_member_wp_user($member->wp_user_id);

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
      return new WP_Error(
        'missing_name',
        __('Member name is required.', 'wp-cospend'),
        array('status' => 400)
      );
    }

    // check if avatar_type is valid
    if ($avatar_type !== null && !in_array($avatar_type, array('file', 'icon'))) {
      return new WP_Error(
        'invalid_avatar_type',
        __('Avatar type must be either "file" or "icon".', 'wp-cospend'),
        array('status' => 400)
      );
    }

    // check if avatar_content is valid for icon type
    if ($avatar_type === 'icon' && empty($avatar_content)) {
      return new WP_Error(
        'invalid_avatar_content',
        __('Avatar content is required for icon type.', 'wp-cospend'),
        array('status' => 400)
      );
    }

    // check if avatar_content is valid for file type
    if ($avatar_type === 'file' && !isset($_FILES['avatar_file'])) {
      return new WP_Error(
        'invalid_avatar_content',
        __('Avatar file is required for file type.', 'wp-cospend'),
        array('status' => 400)
      );
    }

    // Check if member with same name already exists
    $existing = $wpdb->get_var($wpdb->prepare(
      "SELECT id FROM $table_name WHERE name = %s AND created_by = %d",
      $name,
      get_current_user_id()
    ));

    if ($existing) {
      return new WP_Error(
        'duplicate_name',
        __('A member with this name already exists.', 'wp-cospend'),
        array('status' => 400)
      );
    }

    // Insert new member
    $result = $wpdb->insert(
      $table_name,
      array(
        'name' => $name,
        'wp_user_id' => $wp_user_id,
        'created_by' => get_current_user_id(),
        'created_at' => current_time('mysql'),
        'updated_at' => current_time('mysql'),
      ),
      array('%s', '%d', '%d', '%s', '%s')
    );

    if ($result === false) {
      return new WP_Error(
        'db_error',
        __('Error creating member.', 'wp-cospend'),
        array('status' => 500)
      );
    }

    $member_id = $wpdb->insert_id;

    // Handle avatar
    require_once WP_COSPEND_PLUGIN_DIR . 'includes/class-image-manager.php';
    $result = \WPCospend\Image_Manager::save_avatar(
      $avatar_type,
      $avatar_content,
      $member_id,
      'member'
    );

    if (is_wp_error($result)) {
      return $result;
    }

    $member = $wpdb->get_row($wpdb->prepare(
      "SELECT * FROM $table_name WHERE id = %d",
      $member_id
    ));

    // Add avatar to response
    $member->avatar = \WPCospend\Member_Manager::get_member_avatar($member->id);

    return rest_ensure_response($member);
  }

  /**
   * Update one member from the collection.
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|WP_REST_Response
   */
  public function update_item($request) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'cospend_members';
    $id = $request->get_param('id');

    // Check if member exists and user has permission
    $member = $wpdb->get_row($wpdb->prepare(
      "SELECT * FROM $table_name WHERE id = %d",
      $id
    ));

    if (!$member) {
      return new WP_Error(
        'member_not_found',
        __('Member not found.', 'wp-cospend'),
        array('status' => 404)
      );
    }

    // Only creator can update
    if ((int)$member->created_by !== get_current_user_id() && !current_user_can('manage_options')) {
      return new WP_Error(
        'permission_denied',
        __('You do not have permission to update this member.', 'wp-cospend'),
        array('status' => 403)
      );
    }

    $params = $request->get_params();
    $name = isset($params['name']) ? sanitize_text_field($params['name']) : null;
    $wp_user_id = isset($params['wp_user_id']) ? intval($params['wp_user_id']) : null;
    $avatar_type = isset($params['avatar_type']) ? sanitize_text_field($params['avatar_type']) : null;
    $avatar_content = isset($params['avatar_content']) ? sanitize_text_field($params['avatar_content']) : null;

    // Prepare update data
    $update_data = array();
    $update_format = array();

    // Add name if provided
    if ($name !== null) {
      // Check if another member with same name exists
      $duplicate = $wpdb->get_var($wpdb->prepare(
        "SELECT id FROM $table_name WHERE name = %s AND id != %d",
        $name,
        $id
      ));

      if ($duplicate) {
        return new WP_Error(
          'duplicate_name',
          __('A member with this name already exists.', 'wp-cospend'),
          array('status' => 400)
        );
      }

      $update_data['name'] = $name;
      $update_format[] = '%s';
    }

    // Add wp_user_id if provided
    if ($wp_user_id !== null) {
      $update_data['wp_user_id'] = $wp_user_id;
      $update_format[] = '%d';
    }

    // Add updated_at timestamp
    $update_data['updated_at'] = current_time('mysql');
    $update_format[] = '%s';

    // Update member if there are changes
    if (!empty($update_data)) {
      $result = $wpdb->update(
        $table_name,
        $update_data,
        array('id' => $id),
        $update_format,
        array('%d')
      );

      if ($result === false) {
        return new WP_Error(
          'db_error',
          __('Error updating member.', 'wp-cospend'),
          array('status' => 500)
        );
      }
    }

    // Update avatar if provided
    if ($avatar_type !== null && ($avatar_content !== null || isset($_FILES['avatar_file']))) {
      require_once WP_COSPEND_PLUGIN_DIR . 'includes/class-image-manager.php';
      $result = \WPCospend\Image_Manager::save_avatar(
        $avatar_type,
        $avatar_content,
        $id,
        'member'
      );

      if (is_wp_error($result)) {
        return $result;
      }
    }

    $member = $wpdb->get_row($wpdb->prepare(
      "SELECT * FROM $table_name WHERE id = %d",
      $id
    ));

    // Add avatar to response
    $member->avatar = \WPCospend\Member_Manager::get_member_avatar($member->id);

    return rest_ensure_response($member);
  }

  /**
   * Delete one member from the collection.
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|WP_REST_Response
   */
  public function delete_item($request) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'cospend_members';
    $id = $request->get_param('id');

    // Check if member exists
    $existing = $wpdb->get_var($wpdb->prepare(
      "SELECT id FROM $table_name WHERE id = %d",
      $id
    ));

    if (!$existing) {
      return new WP_Error(
        'member_not_found',
        __('Member not found.', 'wp-cospend'),
        array('status' => 404)
      );
    }

    // Check if member is associated with any transactions
    $transactions_table = $wpdb->prefix . 'cospend_transactions';
    $splits_table = $wpdb->prefix . 'cospend_transaction_splits';

    $has_transactions = $wpdb->get_var($wpdb->prepare(
      "SELECT COUNT(*) FROM $transactions_table WHERE payer_id = %d",
      $id
    ));

    $has_splits = $wpdb->get_var($wpdb->prepare(
      "SELECT COUNT(*) FROM $splits_table WHERE member_id = %d",
      $id
    ));

    if ($has_transactions > 0 || $has_splits > 0) {
      return new WP_Error(
        'member_in_use',
        __('Cannot delete member because they are associated with transactions.', 'wp-cospend'),
        array('status' => 400)
      );
    }

    // Delete member
    $result = $wpdb->delete(
      $table_name,
      array('id' => $id),
      array('%d')
    );

    if ($result === false) {
      return new WP_Error(
        'db_error',
        __('Error deleting member.', 'wp-cospend'),
        array('status' => 500)
      );
    }

    return rest_ensure_response(array(
      'message' => __('Member deleted successfully.', 'wp-cospend'),
      'id' => $id
    ));
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

  /**
   * Get WordPress user by email.
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|WP_REST_Response
   */
  public function get_user_by_email($request) {
    $email = sanitize_email($request->get_param('email'));

    if (!is_email($email)) {
      return new WP_Error(
        'invalid_email',
        __('Invalid email address.', 'wp-cospend'),
        array('status' => 400)
      );
    }

    $user = get_user_by('email', $email);
    if (!$user) {
      return new WP_Error(
        'user_not_found',
        __('User not found.', 'wp-cospend'),
        array('status' => 404)
      );
    }

    $user_data = \WPCospend\Member_Manager::get_member_wp_user($user->ID);

    return rest_ensure_response($user_data);
  }
}
