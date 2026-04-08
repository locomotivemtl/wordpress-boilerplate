<?php

/**
 * Removes specific menu item option from the WordPress admin dashboard.
 * @return void
 */
function _theme_remove_admin_menu_items(): void
{
    // Remove the "Theme File Editor" option from the "Appearance" menu item.
    remove_submenu_page( 'themes.php', 'theme-editor.php');
}

/**
 * Remove some unused menu option from the admin bar.
 * @param \WP_Admin_Bar $wp_admin_bar
 *
 * @return void
 */
function _theme_admin_bar_remove_menu_options( WP_Admin_Bar $wp_admin_bar ): void
{
    $wp_admin_bar->remove_menu('customize');
}
