<?php

namespace WPCospend\API;

use WP_REST_Controller;
use WP_REST_Server;
use WP_Error;
use WPCospend\Group_Manager;
use WPCospend\Image_Manager;
use WPCospend\ImageEntityType;
use WPCospend\Member_Manager;

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

    $group = Group_Manager::get_group($request->get_param('id'));
    if (is_wp_error($group)) {
      return $group;
    }

    $group_members = Member_Manager::get_group_members($request->get_param('id'));
    if (is_wp_error($group_members)) {
      return $group_members;
    }

    $is_member = false;
    foreach ($group_members as $member) {
      if (!is_null($member['wp_user']) && (int)$member['wp_user']['id'] === get_current_user_id()) {
        $is_member = true;
      }
    }

    // Admin can access any group
    if (current_user_can('manage_options')) {
      return true;
    }

    // Regular users can only access groups they are a member of
    return $is_member;
  }

  /**
   * Check if a given request has access to update a group.
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|bool
   */
  public function update_item_permissions_check($request) {
    if (!$this->get_item_permissions_check($request)) {
      return false;
    }

    $group_members = Member_Manager::get_group_members($request->get_param('id'));
    $have_edit_permission = false;
    foreach ($group_members as $member) {
      if (!is_null($member['wp_user']) && (int)$member['wp_user']['id'] === get_current_user_id() && $member['can_edit']) {
        $have_edit_permission = true;
      }
    }
    return $have_edit_permission;
  }

  /**
   * Check if a given request has access to delete a group.
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|bool
   */
  public function delete_item_permissions_check($request) {
    if (!is_user_logged_in()) {
      return false;
    }

    $group = Group_Manager::get_group($request->get_param('id'));
    if (is_wp_error($group)) {
      return $group;
    }

    $is_admin = current_user_can('manage_options') || (int)$group['created_by'] === get_current_user_id();
    return $is_admin;
  }

  /**
   * Get all groups (admin only).
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|WP_REST_Response
   */
  public function get_all_items($request) {
    $groups = Group_Manager::get_all_groups();
    if (is_wp_error($groups)) {
      return $groups;
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
    $groups = Group_Manager::get_user_groups(get_current_user_id());
    if (is_wp_error($groups)) {
      return $groups;
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
    $group = Group_Manager::get_group($request->get_param('id'));

    if (is_wp_error($group)) {
      return $group;
    }

    return rest_ensure_response($group);
  }

  /**
   * Create one group
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
      return Group_Manager::get_error('no_name');
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
      return Group_Manager::get_error('invalid_avatar_type');
    }

    // check if avatar_content is valid for icon type
    if ($avatar_type === 'icon' && empty($avatar_content)) {
      return Group_Manager::get_error('invalid_avatar_content');
    }

    // check if avatar_content is valid for file type
    if ($avatar_type === 'file' && !isset($_FILES['avatar_file'])) {
      return Group_Manager::get_error('invalid_avatar_content');
    }

    $group_id = Group_Manager::create_group(
      $name,
      $description,
      $currency,
      get_current_user_id(),
    );

    if (is_wp_error($group_id)) {
      return $group_id;
    }

    // add current user as member to group with can_edit = true
    $is_added = Group_Manager::add_member_to_group($group_id, get_current_user_id(), true);
    if (is_wp_error($is_added)) {
      return $is_added;
    }

    // Handle avatar
    if ($avatar_type === 'file' && isset($_FILES['avatar_file'])) {
      $result = Image_Manager::save_image_file(ImageEntityType::Group, $group_id, 'avatar_file');
      if (is_wp_error($result)) {
        return $result;
      }
    }

    if ($avatar_type === 'icon' && !empty($avatar_content)) {
      $result = Image_Manager::save_image_icon(ImageEntityType::Group, $group_id, $avatar_content);
      if (is_wp_error($result)) {
        return $result;
      }
    }

    $group = Group_Manager::get_group($group_id);
    if (is_wp_error($group)) {
      return $group;
    }

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
    $group = Group_Manager::get_group($group_id);
    if (is_wp_error($group)) {
      return $group;
    }

    $avatar_type = isset($params['avatar_type']) ? sanitize_text_field($params['avatar_type']) : null;
    $avatar_content = isset($params['avatar_content']) ? sanitize_text_field($params['avatar_content']) : null;

    $update_data = array();

    if (isset($params['name'])) {
      $update_data['name'] = sanitize_text_field($params['name']);
    }
    if (isset($params['description'])) {
      $update_data['description'] = sanitize_text_field($params['description']);
    }
    if (isset($params['currency'])) {
      $update_data['currency'] = sanitize_text_field($params['currency']);
    }

    // Update avatar if provided
    if ($avatar_type !== null && ($avatar_content !== null || isset($_FILES['avatar_file']))) {
      if ($avatar_type === 'file' && isset($_FILES['avatar_file'])) {
        $result = Image_Manager::save_image_file(ImageEntityType::Group, $group_id, file_key: 'avatar_file');
        if (is_wp_error($result)) {
          return $result;
        }
      }

      if ($avatar_type === 'icon' && !empty($avatar_content)) {
        $result = Image_Manager::save_image_icon(ImageEntityType::Group, $group_id, $avatar_content);
        if (is_wp_error($result)) {
          return $result;
        }
      }
    }

    $result = Group_Manager::update_group($group_id, $update_data);
    if (is_wp_error($result)) {
      return $result;
    }

    $group = Group_Manager::get_group($group_id);
    if (is_wp_error($group)) {
      return $group;
    }

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
    $result = Group_Manager::delete_group($group_id);

    if (is_wp_error($result)) {
      return $result;
    }

    return rest_ensure_response(array(
      'message' => __('Group deleted successfully.', 'wp-cospend'),
      'id' => $group_id
    ));
  }

  /**
   * Get all members in a group.
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|WP_REST_Response
   */
  public function get_group_members($request) {
    $group_id = $request->get_param('id');
    $group = Group_Manager::get_group($group_id);
    if (is_wp_error($group)) {
      return $group;
    }

    $members = Member_Manager::get_group_members($group_id);
    if (is_wp_error($members)) {
      return $members;
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
    $group = Group_Manager::get_group($group_id);
    if (is_wp_error($group)) {
      return $group;
    }

    // Check if member exists
    $member = Member_Manager::get_member($member_id);
    if (is_wp_error($member)) {
      return $member;
    }

    // Check if user has permission to add this member
    if ((int)$member['created_by'] !== get_current_user_id() && !current_user_can('manage_options')) {
      return Group_Manager::get_error('no_permission');
    }

    $result = Group_Manager::add_member_to_group($group_id, $member_id);

    if (is_wp_error($result)) {
      return $result;
    }

    $group_members = Member_Manager::get_group_members($group_id);
    if (is_wp_error($group_members)) {
      return $group_members;
    }

    return rest_ensure_response($group_members);
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
    $group = Group_Manager::get_group($group_id);
    if (is_wp_error($group)) {
      return $group;
    }

    // Check if member exists
    $member = Member_Manager::get_member($member_id);
    if (is_wp_error($member)) {
      return $member;
    }

    $current_group_members = Member_Manager::get_group_members($group_id);
    if (is_wp_error($current_group_members)) {
      return $current_group_members;
    }

    $is_current_user_have_edit_permission = false;
    foreach ($current_group_members as $current_group_member) {
      if (!is_null($current_group_member['wp_user']) && (int)$current_group_member['wp_user']['id'] === get_current_user_id() && $current_group_member['can_edit']) {
        $is_current_user_have_edit_permission = true;
      }
    }

    // Check if user has permission to remove this member
    if (!($is_current_user_have_edit_permission || (int)$member['created_by'] !== get_current_user_id() || current_user_can('manage_options'))) {
      return Group_Manager::get_error('no_permission');
    }

    $result = Group_Manager::remove_member_from_group($group_id, $member_id);

    if (is_wp_error($result)) {
      return $result;
    }

    $group_members = Member_Manager::get_group_members($group_id);
    if (is_wp_error($group_members)) {
      return $group_members;
    }

    return rest_ensure_response($group_members);
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
}
