<?php

namespace WPCospend\API;

use WP_REST_Controller;
use WP_REST_Server;
use WP_Error;

class Group_Controller extends WP_REST_Controller {
  /**
   * Constructor.
   */
  public function __construct() {
    $this->namespace = 'wp-cospend/v1';
    $this->rest_base = 'groups';
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

    // Group routes
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
              'description' => __('Unique identifier for the group.', 'wp-cospend'),
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
              'description' => __('Unique identifier for the group.', 'wp-cospend'),
              'type' => 'integer',
            ),
          ),
        ),
      )
    );

    // Group members routes
    register_rest_route(
      $this->namespace,
      '/' . $this->rest_base . '/(?P<id>[\d]+)/members',
      array(
        array(
          'methods' => WP_REST_Server::READABLE,
          'callback' => array($this, 'get_group_members'),
          'permission_callback' => array($this, 'get_item_permissions_check'),
          'args' => array(
            'id' => array(
              'description' => __('Unique identifier for the group.', 'wp-cospend'),
              'type' => 'integer',
            ),
          ),
        ),
      )
    );

    register_rest_route(
      $this->namespace,
      '/' . $this->rest_base . '/(?P<id>[\d]+)/members/(?P<member_id>[\d]+)',
      array(
        array(
          'methods' => WP_REST_Server::CREATABLE,
          'callback' => array($this, 'add_group_member'),
          'permission_callback' => array($this, 'update_item_permissions_check'),
          'args' => array(
            'id' => array(
              'description' => __('Unique identifier for the group.', 'wp-cospend'),
              'type' => 'integer',
            ),
            'member_id' => array(
              'description' => __('Unique identifier for the member.', 'wp-cospend'),
              'type' => 'integer',
            ),
          ),
        ),
        array(
          'methods' => WP_REST_Server::DELETABLE,
          'callback' => array($this, 'remove_group_member'),
          'permission_callback' => array($this, 'update_item_permissions_check'),
          'args' => array(
            'id' => array(
              'description' => __('Unique identifier for the group.', 'wp-cospend'),
              'type' => 'integer',
            ),
            'member_id' => array(
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
   * Check if a given request has access to read groups.
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|bool
   */
  public function get_items_permissions_check($request) {
    return is_user_logged_in();
  }

  /**
   * Check if a given request has access to create a group.
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|bool
   */
  public function create_item_permissions_check($request) {
    return is_user_logged_in();
  }

  /**
   * Check if a given request has access to read a group.
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|bool
   */
  public function get_item_permissions_check($request) {
    if (!is_user_logged_in()) {
      return false;
    }

    $group = \WPCospend\Group_Manager::get_group($request->get_param('id'));

    // Admin can access any group
    if (current_user_can('manage_options')) {
      return true;
    }

    // Regular users can only access groups they created
    return $group && (int)$group->created_by === get_current_user_id();
  }

  /**
   * Check if a given request has access to update a group.
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|bool
   */
  public function update_item_permissions_check($request) {
    return $this->get_item_permissions_check($request);
  }

  /**
   * Check if a given request has access to delete a group.
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|bool
   */
  public function delete_item_permissions_check($request) {
    return $this->get_item_permissions_check($request);
  }

  /**
   * Get all groups (admin only).
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|WP_REST_Response
   */
  public function get_all_items($request) {
    $groups = \WPCospend\Group_Manager::get_all_groups();

    if ($groups === null) {
      return new WP_Error(
        'db_error',
        __('Error fetching groups.', 'wp-cospend'),
        array('status' => 500)
      );
    }

    // add avatar to each group
    foreach ($groups as $group) {
      $group->avatar = \WPCospend\Image_Manager::get_avatar($group->id, 'group');
    }

    return rest_ensure_response($groups);
  }

  /**
   * Get a collection of groups created by the current user.
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|WP_REST_Response
   */
  public function get_items($request) {
    $groups = \WPCospend\Group_Manager::get_user_groups(get_current_user_id());

    if ($groups === null) {
      return new WP_Error(
        'db_error',
        __('Error fetching groups.', 'wp-cospend'),
        array('status' => 500)
      );
    }

    // add avatar to each group
    foreach ($groups as $group) {
      $group->avatar = \WPCospend\Image_Manager::get_avatar($group->id, 'group');
    }

    return rest_ensure_response($groups);
  }

  /**
   * Get one group from the collection.
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|WP_REST_Response
   */
  public function get_item($request) {
    $group = \WPCospend\Group_Manager::get_group($request->get_param('id'));

    if ($group === null) {
      return new WP_Error(
        'group_not_found',
        __('Group not found.', 'wp-cospend'),
        array('status' => 404)
      );
    }

    return rest_ensure_response($group);
  }

  /**
   * Create one group from the collection.
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|WP_REST_Response
   */
  public function create_item($request) {
    $params = $request->get_params();
    $name = sanitize_text_field($params['name']);
    $description = sanitize_text_field($params['description'] ?? '');
    // $currency = sanitize_text_field($params['currency']);
    $currency = 'INR';
    $avatar_type = sanitize_text_field($params['avatar_type']);
    $avatar_content = isset($params['avatar_content']) ? sanitize_text_field($params['avatar_content']) : null;

    // Validate required fields
    if (empty($name)) {
      return new WP_Error(
        'missing_name',
        __('Group name is required.', 'wp-cospend'),
        array('status' => 400)
      );
    }

    // if (empty($currency)) {
    //   return new WP_Error(
    //     'missing_currency',
    //     __('Group currency is required.', 'wp-cospend'),
    //     array('status' => 400)
    //   );
    // }

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

    $group_id = \WPCospend\Group_Manager::create_group(
      $name,
      $description,
      $currency,
      get_current_user_id(),
    );

    if ($group_id === false) {
      return new WP_Error(
        'db_error',
        __('Error creating group.', 'wp-cospend'),
        array('status' => 500)
      );
    }

    // add current user as member to group with can_edit = true
    $is_added = \WPCospend\Group_Manager::add_member_to_group($group_id, get_current_user_id(), true);
    if (!$is_added) {
      return new WP_Error(
        'db_error',
        __('Error adding member to group.', 'wp-cospend'),
        array('status' => 500)
      );
    }

    // Handle avatar
    require_once WP_COSPEND_PLUGIN_DIR . 'includes/class-image-manager.php';
    $avatar_result = \WPCospend\Image_Manager::save_avatar(
      $avatar_type,
      $avatar_content,
      $group_id,
      'group'
    );

    if (is_wp_error($avatar_result)) {
      return $avatar_result;
    }

    $group = \WPCospend\Group_Manager::get_group($group_id);
    return rest_ensure_response($group);
  }

  /**
   * Update one group from the collection.
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|WP_REST_Response
   */
  public function update_item($request) {
    $group_id = $request->get_param('id');
    $params = $request->get_params();

    // check if group exists
    $group = \WPCospend\Group_Manager::get_group($group_id);
    if ($group === null) {
      return new WP_Error(
        'group_not_found',
        __('Group not found.', 'wp-cospend'),
        array('status' => 404)
      );
    }

    // check if user has permission to update group
    if ((int)$group->created_by !== get_current_user_id() && !current_user_can('manage_options')) {
      return new WP_Error(
        'permission_denied',
        __('You do not have permission to update this group.', 'wp-cospend'),
        array('status' => 403)
      );
    }

    $avatar_type = isset($params['avatar_type']) ? sanitize_text_field($params['avatar_type']) : null;
    $avatar_content = isset($params['avatar_content']) ? sanitize_text_field($params['avatar_content']) : null;

    $update_data = array();
    $update_format = array();

    if (isset($params['name'])) {
      $update_data['name'] = sanitize_text_field($params['name']);
      $update_format[] = '%s';
    }
    if (isset($params['description'])) {
      $update_data['description'] = sanitize_text_field($params['description']);
      $update_format[] = '%s';
    }
    if (isset($params['currency'])) {
      $update_data['currency'] = sanitize_text_field($params['currency']);
      $update_format[] = '%s';
    }

    // Add updated_at timestamp
    $update_data['updated_at'] = current_time('mysql');
    $update_format[] = '%s';

    if (empty($update_data)) {
      return new WP_Error(
        'no_data',
        __('No data provided for update.', 'wp-cospend'),
        array('status' => 400)
      );
    }

    $result = \WPCospend\Group_Manager::update_group($group_id, $update_data);

    if ($result === false) {
      return new WP_Error(
        'db_error',
        __('Error updating group.', 'wp-cospend'),
        array('status' => 500)
      );
    }

    // Update avatar if provided
    if ($avatar_type !== null && ($avatar_content !== null || isset($_FILES['avatar_file']))) {
      require_once WP_COSPEND_PLUGIN_DIR . 'includes/class-image-manager.php';
      $result = \WPCospend\Image_Manager::save_avatar(
        $avatar_type,
        $avatar_content,
        $group_id,
        'group'
      );

      if (is_wp_error($result)) {
        return $result;
      }
    }

    $group = \WPCospend\Group_Manager::get_group($group_id);
    return rest_ensure_response($group);
  }

  /**
   * Delete one group from the collection.
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|WP_REST_Response
   */
  public function delete_item($request) {
    $group_id = $request->get_param('id');
    $result = \WPCospend\Group_Manager::delete_group($group_id);

    if ($result === false) {
      return new WP_Error(
        'db_error',
        __('Error deleting group.', 'wp-cospend'),
        array('status' => 500)
      );
    }

    return rest_ensure_response(array(
      'message' => __('Group deleted successfully.', 'wp-cospend'),
      'id' => $group_id
    ));
  }

  /**
   * Get the group schema, conforming to JSON Schema.
   *
   * @return array
   */
  public function get_item_schema() {
    return array(
      '$schema' => 'http://json-schema.org/draft-04/schema#',
      'title' => 'group',
      'type' => 'object',
      'properties' => array(
        'id' => array(
          'description' => __('Unique identifier for the group.', 'wp-cospend'),
          'type' => 'integer',
          'readonly' => true,
        ),
        'name' => array(
          'description' => __('The name of the group.', 'wp-cospend'),
          'type' => 'string',
          'required' => true,
        ),
        'description' => array(
          'description' => __('The description of the group.', 'wp-cospend'),
          'type' => 'string',
        ),
        'currency' => array(
          'description' => __('The currency used in this group.', 'wp-cospend'),
          'type' => 'string',
          'enum' => array('INR', 'USD', 'EUR', 'GBP'),
          'required' => false,
        ),
        'created_by' => array(
          'description' => __('The ID of the user who created this group.', 'wp-cospend'),
          'type' => 'integer',
          'readonly' => true,
        ),
        'created_at' => array(
          'description' => __('The date the group was created.', 'wp-cospend'),
          'type' => 'string',
          'format' => 'date-time',
          'readonly' => true,
        ),
        'updated_at' => array(
          'description' => __('The date the group was last updated.', 'wp-cospend'),
          'type' => 'string',
          'format' => 'date-time',
          'readonly' => true,
        ),
      ),
    );
  }

  /**
   * Get all members in a group.
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|WP_REST_Response
   */
  public function get_group_members($request) {
    $group_id = $request->get_param('id');
    $members = \WPCospend\Group_Manager::get_group_members($group_id);

    if ($members === null) {
      return new WP_Error(
        'db_error',
        __('Error fetching group members.', 'wp-cospend'),
        array('status' => 500)
      );
    }

    // Add avatars to response
    foreach ($members as $member) {
      $member->avatar = \WPCospend\Member_Manager::get_avatar($member->id);
      $member->wp_user = \WPCospend\Member_Manager::get_wp_user($member->wp_user_id);
    }

    return rest_ensure_response($members);
  }

  /**
   * Add a member to a group.
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|WP_REST_Response
   */
  public function add_group_member($request) {
    $group_id = $request->get_param('id');
    $member_id = $request->get_param('member_id');

    // Check if group exists
    $group = \WPCospend\Group_Manager::get_group($group_id);
    if ($group === null) {
      return new WP_Error(
        'group_not_found',
        __('Group not found.', 'wp-cospend'),
        array('status' => 404)
      );
    }

    // Check if member exists
    $member = \WPCospend\Member_Manager::get_member($member_id);
    if ($member === null) {
      return new WP_Error(
        'member_not_found',
        __('Member not found.', 'wp-cospend'),
        array('status' => 404)
      );
    }

    // Check if user has permission to add this member
    if ((int)$member->created_by !== get_current_user_id() && !current_user_can('manage_options')) {
      return new WP_Error(
        'permission_denied',
        __('You do not have permission to add this member to this group.', 'wp-cospend'),
        array('status' => 403)
      );
    }

    $result = \WPCospend\Group_Manager::add_member_to_group($group_id, $member_id);

    if ($result === false) {
      return new WP_Error(
        'db_error',
        __('Error adding member to group.', 'wp-cospend'),
        array('status' => 500)
      );
    }

    return rest_ensure_response(array(
      'message' => __('Member added to group successfully.', 'wp-cospend'),
      'group_id' => $group_id,
      'member_id' => $member_id
    ));
  }

  /**
   * Remove a member from a group.
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|WP_REST_Response
   */
  public function remove_group_member($request) {
    $group_id = $request->get_param('id');
    $member_id = $request->get_param('member_id');

    // Check if group exists
    $group = \WPCospend\Group_Manager::get_group($group_id);
    if ($group === null) {
      return new WP_Error(
        'group_not_found',
        __('Group not found.', 'wp-cospend'),
        array('status' => 404)
      );
    }

    // Check if member exists
    $member = \WPCospend\Member_Manager::get_member($member_id);
    if ($member === null) {
      return new WP_Error(
        'member_not_found',
        __('Member not found.', 'wp-cospend'),
        array('status' => 404)
      );
    }

    // Check if user has permission to remove this member
    if ((int)$member->created_by !== get_current_user_id() && !current_user_can('manage_options')) {
      return new WP_Error(
        'permission_denied',
        __('You do not have permission to remove this member from this group.', 'wp-cospend'),
        array('status' => 403)
      );
    }
    $result = \WPCospend\Group_Manager::remove_member_from_group($group_id, $member_id);

    if ($result === false) {
      return new WP_Error(
        'db_error',
        __('Error removing member from group.', 'wp-cospend'),
        array('status' => 500)
      );
    }

    return rest_ensure_response(array(
      'message' => __('Member removed from group successfully.', 'wp-cospend'),
      'group_id' => $group_id,
      'member_id' => $member_id
    ));
  }
}
