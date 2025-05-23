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
  const DEFAULT_EXPENSE_CATEGORIES = array(
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
          'icon' => 'volleyball',
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
          'icon' => 'notebook-pen',
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
          'icon' => 'tent-tree',
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
   * Default income categories
   */
  const DEFAULT_INCOME_CATEGORIES = array(
    array(
      'name' => 'Work',
      'color' => '#2ECC71',
      'icon' => 'briefcase',
      'children' =>
      array(
        array(
          'name' => 'Salary',
          'color' => '#2ECC71',
          'icon' => 'dollar-sign',
        ),
        array(
          'name' => 'Bonus',
          'color' => '#2ECC71',
          'icon' => 'gem',
        ),
        array(
          'name' => 'Gifts',
          'color' => '#2ECC71',
          'icon' => 'gift',
        ),
        array(
          'name' => 'Other',
          'color' => '#95A5A6',
          'icon' => 'more-horizontal',
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
   * Default transfer categories
   */
  const DEFAULT_TRANSFER_CATEGORIES = array(
    array(
      'name' => 'Bank',
      'color' => '#2ECC71',
      'icon' => 'banknote-arrow-up',
      'children' => array(),
    ),
    array(
      'name' => 'ATM',
      'color' => '#2ECC71',
      'icon' => 'banknote-arrow-down',
      'children' => array(),
    ),
    array(
      'name' => 'CDM',
      'color' => '#2ECC71',
      'icon' => 'coins',
      'children' => array(),
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
      ...array_map(function ($category) {
        return array(
          ...$category,
          'type' => 'expense',
        );
      }, self::DEFAULT_EXPENSE_CATEGORIES),
      ...array_map(function ($category) {
        return array(
          ...$category,
          'type' => 'income',
        );
      }, self::DEFAULT_INCOME_CATEGORIES),
      ...array_map(function ($category) {
        return array(
          ...$category,
          'type' => 'transfer',
        );
      }, self::DEFAULT_TRANSFER_CATEGORIES),
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
            'type' => $category['type'],
            'created_by' => self::SYSTEM_USER_ID,
          ),
          array('%s', '%s', '%s', '%d')
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
      $children = is_array($category['children']) ? $category['children'] : array();
      foreach ($children as $child) {
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
            'type' => $category['type'],
          ),
          array('%d', '%s', '%s', '%d', '%s')
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
          ),
          array('%s', '%d', '%s', '%s', '%d')
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

    $default_tags = self::DEFAULT_TAGS;

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
        ),
        array('%s', '%s', '%d')
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
        ),
        array('%s', '%d', '%s', '%s', '%d')
      );
    }
  }
}
