<?php

namespace WPCospend;

class System_Defaults {
  /**
   * System user ID for default items
   */
  const SYSTEM_USER_ID = 0;

  /**
   * Default categories with parent-child relationships
   */
  const DEFAULT_CATEGORIES = array(
    // Parent categories
    array(
      'name' => 'Food & Dining',
      'color' => '#FF6B6B',
      'icon' => 'utensils',
      'children' => array(
        array(
          'name' => 'Dining Out',
          'color' => '#FF8E8E',
          'icon' => 'utensils',
        ),
        array(
          'name' => 'Groceries',
          'color' => '#FF8E8E',
          'icon' => 'shopping-basket',
        ),
        array(
          'name' => 'Coffee & Snacks',
          'color' => '#FF8E8E',
          'icon' => 'coffee',
        ),
      ),
    ),
    array(
      'name' => 'Shopping',
      'color' => '#4ECDC4',
      'icon' => 'shopping-cart',
      'children' => array(
        array(
          'name' => 'Clothing',
          'color' => '#6ED7D0',
          'icon' => 'shirt',
        ),
        array(
          'name' => 'Electronics',
          'color' => '#6ED7D0',
          'icon' => 'laptop',
        ),
        array(
          'name' => 'Home Goods',
          'color' => '#6ED7D0',
          'icon' => 'sofa',
        ),
      ),
    ),
    array(
      'name' => 'Transportation',
      'color' => '#45B7D1',
      'icon' => 'car',
      'children' => array(
        array(
          'name' => 'Fuel',
          'color' => '#67C5DB',
          'icon' => 'fuel',
        ),
        array(
          'name' => 'Public Transit',
          'color' => '#67C5DB',
          'icon' => 'bus',
        ),
        array(
          'name' => 'Maintenance',
          'color' => '#67C5DB',
          'icon' => 'wrench',
        ),
      ),
    ),
    array(
      'name' => 'Housing',
      'color' => '#96CEB4',
      'icon' => 'home',
      'children' => array(
        array(
          'name' => 'Rent',
          'color' => '#B1D9C3',
          'icon' => 'key',
        ),
        array(
          'name' => 'Mortgage',
          'color' => '#B1D9C3',
          'icon' => 'home',
        ),
        array(
          'name' => 'Maintenance',
          'color' => '#B1D9C3',
          'icon' => 'hammer',
        ),
      ),
    ),
    array(
      'name' => 'Utilities',
      'color' => '#FFEEAD',
      'icon' => 'zap',
      'children' => array(
        array(
          'name' => 'Electricity',
          'color' => '#FFF0C0',
          'icon' => 'zap',
        ),
        array(
          'name' => 'Water',
          'color' => '#FFF0C0',
          'icon' => 'droplet',
        ),
        array(
          'name' => 'Internet',
          'color' => '#FFF0C0',
          'icon' => 'wifi',
        ),
      ),
    ),
    array(
      'name' => 'Entertainment',
      'color' => '#D4A5A5',
      'icon' => 'film',
      'children' => array(
        array(
          'name' => 'Movies',
          'color' => '#E5BDBD',
          'icon' => 'film',
        ),
        array(
          'name' => 'Games',
          'color' => '#E5BDBD',
          'icon' => 'gamepad-2',
        ),
        array(
          'name' => 'Music',
          'color' => '#E5BDBD',
          'icon' => 'music',
        ),
        array(
          'name' => 'Sports',
          'color' => '#E5BDBD',
          'icon' => 'football',
        ),
      ),
    ),
    array(
      'name' => 'Healthcare',
      'color' => '#9B59B6',
      'icon' => 'heart-pulse',
      'children' => array(
        array(
          'name' => 'Medical',
          'color' => '#B07CC7',
          'icon' => 'stethoscope',
        ),
        array(
          'name' => 'Dental',
          'color' => '#B07CC7',
          'icon' => 'tooth',
        ),
        array(
          'name' => 'Pharmacy',
          'color' => '#B07CC7',
          'icon' => 'pill',
        ),
      ),
    ),
    array(
      'name' => 'Education',
      'color' => '#3498DB',
      'icon' => 'graduation-cap',
      'children' => array(
        array(
          'name' => 'Tuition',
          'color' => '#5DABE3',
          'icon' => 'school',
        ),
        array(
          'name' => 'Books',
          'color' => '#5DABE3',
          'icon' => 'book',
        ),
        array(
          'name' => 'Courses',
          'color' => '#5DABE3',
          'icon' => 'chalkboard',
        ),
      ),
    ),
    array(
      'name' => 'Personal Care',
      'color' => '#E67E22',
      'icon' => 'user',
      'children' => array(
        array(
          'name' => 'Hair Care',
          'color' => '#EB9950',
          'icon' => 'scissors',
        ),
        array(
          'name' => 'Skincare',
          'color' => '#EB9950',
          'icon' => 'sparkles',
        ),
        array(
          'name' => 'Fitness',
          'color' => '#EB9950',
          'icon' => 'dumbbell',
        ),
      ),
    ),
    array(
      'name' => 'Gifts',
      'color' => '#E74C3C',
      'icon' => 'gift',
      'children' => array(
        array(
          'name' => 'Birthday',
          'color' => '#EC6B5D',
          'icon' => 'cake',
        ),
        array(
          'name' => 'Holiday',
          'color' => '#EC6B5D',
          'icon' => 'holiday',
        ),
        array(
          'name' => 'Special Occasion',
          'color' => '#EC6B5D',
          'icon' => 'gift',
        ),
      ),
    ),
    array(
      'name' => 'Travel',
      'color' => '#2ECC71',
      'icon' => 'plane',
      'children' => array(
        array(
          'name' => 'Flights',
          'color' => '#58D68D',
          'icon' => 'plane',
        ),
        array(
          'name' => 'Hotels',
          'color' => '#58D68D',
          'icon' => 'building-2',
        ),
        array(
          'name' => 'Activities',
          'color' => '#58D68D',
          'icon' => 'umbrella',
        ),
      ),
    ),
    array(
      'name' => 'Other',
      'color' => '#95A5A6',
      'icon' => 'more-horizontal',
      'children' => array(),
    ),
  );

  /**
   * Default tags
   */
  const DEFAULT_TAGS = array(
    array(
      'name' => 'Recurring',
      'color' => '#3498DB',
      'icon' => 'repeat',
    ),
    array(
      'name' => 'Urgent',
      'color' => '#E74C3C',
      'icon' => 'alert-circle',
    ),
    array(
      'name' => 'Shared',
      'color' => '#2ECC71',
      'icon' => 'users',
    ),
    array(
      'name' => 'Personal',
      'color' => '#F1C40F',
      'icon' => 'user',
    ),
    array(
      'name' => 'Business',
      'color' => '#9B59B6',
      'icon' => 'briefcase',
    ),
  );

  /**
   * Create system default categories and tags.
   */
  public static function create_defaults() {
    self::create_default_categories();
    self::create_default_tags();
  }

  /**
   * Create default categories with parent-child relationships.
   */
  private static function create_default_categories() {
    global $wpdb;
    $categories_table = $wpdb->prefix . 'cospend_categories';
    $images_table = $wpdb->prefix . 'cospend_images';

    $default_categories = array(
      array(
        'name' => __('Food & Dining', 'wp-cospend'),
        'color' => '#FF6B6B',
        'icon' => 'utensils',
        'children' => array(
          array(
            'name' => __('Dining Out', 'wp-cospend'),
            'color' => '#FF8E8E',
            'icon' => 'utensils',
          ),
          array(
            'name' => __('Groceries', 'wp-cospend'),
            'color' => '#FF8E8E',
            'icon' => 'shopping-basket',
          ),
          array(
            'name' => __('Coffee & Snacks', 'wp-cospend'),
            'color' => '#FF8E8E',
            'icon' => 'coffee',
          ),
        ),
      ),
      array(
        'name' => __('Transportation', 'wp-cospend'),
        'color' => '#45B7D1',
        'icon' => 'car',
        'children' => array(
          array(
            'name' => __('Fuel', 'wp-cospend'),
            'color' => '#67C5DB',
            'icon' => 'fuel',
          ),
          array(
            'name' => __('Public Transit', 'wp-cospend'),
            'color' => '#67C5DB',
            'icon' => 'bus',
          ),
          array(
            'name' => __('Maintenance', 'wp-cospend'),
            'color' => '#67C5DB',
            'icon' => 'wrench',
          ),
        ),
      ),
      array(
        'name' => __('Housing', 'wp-cospend'),
        'color' => '#96CEB4',
        'icon' => 'home',
        'children' => array(
          array(
            'name' => __('Rent', 'wp-cospend'),
            'color' => '#B1D9C3',
            'icon' => 'key',
          ),
          array(
            'name' => __('Mortgage', 'wp-cospend'),
            'color' => '#B1D9C3',
            'icon' => 'home',
          ),
          array(
            'name' => __('Maintenance', 'wp-cospend'),
            'color' => '#B1D9C3',
            'icon' => 'hammer',
          ),
        ),
      ),
      array(
        'name' => __('Utilities', 'wp-cospend'),
        'color' => '#FFEEAD',
        'icon' => 'zap',
        'children' => array(
          array(
            'name' => __('Electricity', 'wp-cospend'),
            'color' => '#FFF0C0',
            'icon' => 'zap',
          ),
          array(
            'name' => __('Water', 'wp-cospend'),
            'color' => '#FFF0C0',
            'icon' => 'droplet',
          ),
          array(
            'name' => __('Internet', 'wp-cospend'),
            'color' => '#FFF0C0',
            'icon' => 'wifi',
          ),
        ),
      ),
      array(
        'name' => __('Entertainment', 'wp-cospend'),
        'color' => '#D4A5A5',
        'icon' => 'film',
        'children' => array(
          array(
            'name' => __('Movies', 'wp-cospend'),
            'color' => '#E5BDBD',
            'icon' => 'film',
          ),
          array(
            'name' => __('Games', 'wp-cospend'),
            'color' => '#E5BDBD',
            'icon' => 'gamepad-2',
          ),
          array(
            'name' => __('Music', 'wp-cospend'),
            'color' => '#E5BDBD',
            'icon' => 'music',
          ),
        ),
      ),
    );

    foreach ($default_categories as $category) {
      // Check if parent category already exists
      $existing_parent = $wpdb->get_var($wpdb->prepare(
        "SELECT id FROM $categories_table WHERE name = %s AND created_by = %d AND parent_id IS NULL",
        $category['name'],
        self::SYSTEM_USER_ID
      ));

      if ($existing_parent) {
        $parent_id = $existing_parent;
      } else {
        // Insert parent category
        $wpdb->insert(
          $categories_table,
          array(
            'name' => $category['name'],
            'color' => $category['color'],
            'created_by' => self::SYSTEM_USER_ID,
            'created_at' => current_time('mysql'),
            'updated_at' => current_time('mysql'),
          ),
          array('%s', '%s', '%d', '%s', '%s')
        );

        $parent_id = $wpdb->insert_id;

        // Insert parent icon
        $wpdb->insert(
          $images_table,
          array(
            'entity_type' => 'category',
            'entity_id' => $parent_id,
            'type' => 'icon',
            'content' => $category['icon'],
            'created_by' => self::SYSTEM_USER_ID,
            'created_at' => current_time('mysql'),
            'updated_at' => current_time('mysql'),
          ),
          array('%s', '%d', '%s', '%s', '%d', '%s', '%s')
        );
      }

      // Create child categories
      foreach ($category['children'] as $child) {
        // Check if child category already exists
        $existing_child = $wpdb->get_var($wpdb->prepare(
          "SELECT id FROM $categories_table WHERE name = %s AND created_by = %d AND parent_id = %d",
          $child['name'],
          self::SYSTEM_USER_ID,
          $parent_id
        ));

        if ($existing_child) {
          continue;
        }

        // Insert child category
        $wpdb->insert(
          $categories_table,
          array(
            'parent_id' => $parent_id,
            'name' => $child['name'],
            'color' => $child['color'],
            'created_by' => self::SYSTEM_USER_ID,
            'created_at' => current_time('mysql'),
            'updated_at' => current_time('mysql'),
          ),
          array('%d', '%s', '%s', '%d', '%s', '%s')
        );

        $child_id = $wpdb->insert_id;

        // Insert child icon
        $wpdb->insert(
          $images_table,
          array(
            'entity_type' => 'category',
            'entity_id' => $child_id,
            'type' => 'icon',
            'content' => $child['icon'],
            'created_by' => self::SYSTEM_USER_ID,
            'created_at' => current_time('mysql'),
            'updated_at' => current_time('mysql'),
          ),
          array('%s', '%d', '%s', '%s', '%d', '%s', '%s')
        );
      }
    }
  }

  /**
   * Create default tags with icons.
   */
  private static function create_default_tags() {
    global $wpdb;
    $tags_table = $wpdb->prefix . 'cospend_tags';
    $images_table = $wpdb->prefix . 'cospend_images';

    $default_tags = array(
      array(
        'name' => __('Regular', 'wp-cospend'),
        'color' => '#808080',
        'icon' => 'circle',
      ),
      array(
        'name' => __('Urgent', 'wp-cospend'),
        'color' => '#FF0000',
        'icon' => 'alert-circle',
      ),
      array(
        'name' => __('Important', 'wp-cospend'),
        'color' => '#FFA500',
        'icon' => 'star',
      ),
      array(
        'name' => __('Recurring', 'wp-cospend'),
        'color' => '#3498DB',
        'icon' => 'repeat',
      ),
      array(
        'name' => __('Personal', 'wp-cospend'),
        'color' => '#2ECC71',
        'icon' => 'user',
      ),
    );

    foreach ($default_tags as $tag) {
      // Check if tag already exists
      $existing = $wpdb->get_var($wpdb->prepare(
        "SELECT id FROM $tags_table WHERE name = %s AND created_by = %d",
        $tag['name'],
        self::SYSTEM_USER_ID
      ));

      if ($existing) {
        continue;
      }

      // Insert tag
      $wpdb->insert(
        $tags_table,
        array(
          'name' => $tag['name'],
          'color' => $tag['color'],
          'created_by' => self::SYSTEM_USER_ID,
          'created_at' => current_time('mysql'),
          'updated_at' => current_time('mysql'),
        ),
        array('%s', '%s', '%d', '%s', '%s')
      );

      $tag_id = $wpdb->insert_id;

      // Insert icon
      $wpdb->insert(
        $images_table,
        array(
          'entity_type' => 'tag',
          'entity_id' => $tag_id,
          'type' => 'icon',
          'content' => $tag['icon'],
          'created_by' => self::SYSTEM_USER_ID,
          'created_at' => current_time('mysql'),
          'updated_at' => current_time('mysql'),
        ),
        array('%s', '%d', '%s', '%s', '%d', '%s', '%s')
      );
    }
  }

  /**
   * Check if a category is a system default.
   *
   * @param int $category_id Category ID to check
   * @return bool True if it's a system category
   */
  public static function is_system_category($category_id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'cospend_categories';

    $created_by = $wpdb->get_var($wpdb->prepare(
      "SELECT created_by FROM $table_name WHERE id = %d",
      $category_id
    ));

    return $created_by === self::SYSTEM_USER_ID;
  }

  /**
   * Check if a tag is a system default.
   *
   * @param int $tag_id Tag ID to check
   * @return bool True if it's a system tag
   */
  public static function is_system_tag($tag_id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'cospend_tags';

    $created_by = $wpdb->get_var($wpdb->prepare(
      "SELECT created_by FROM $table_name WHERE id = %d",
      $tag_id
    ));

    return $created_by === self::SYSTEM_USER_ID;
  }
}
