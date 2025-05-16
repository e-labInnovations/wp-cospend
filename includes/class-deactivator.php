<?php

namespace WPCospend;

class Deactivator {
  /**
   * Deactivate the plugin.
   */
  public static function deactivate() {
    // Remove custom roles
    remove_role('cospend_manager');

    // Note: We don't delete the tables on deactivation
    // This is to preserve user data in case of accidental deactivation
  }
}
