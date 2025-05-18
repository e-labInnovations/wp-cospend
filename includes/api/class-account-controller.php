<?php

namespace WPCospend\API;

use WP_REST_Controller;

class Account_Controller extends WP_REST_Controller {
  /**
   * Constructor.
   */
  public function __construct() {
    $this->namespace = 'wp-cospend/v1';
    $this->rest_base = 'accounts';
  }
}
