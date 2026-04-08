<?php

/**
 * Hooks and side effects to customize WordPress Dashboard.
 */

/**
 * Enqueues scripts and styles to customize Dashboard, ACF, and ACF Extended.
 */
add_action( 'admin_enqueue_scripts', function () : void {
    if (is_plugin_active('acf')) {
        wp_enqueue_script(
            'theme-theme-admin',
            get_theme_file_uri( '/admin/admin.js' ),
            [ 'acf-input', 'select2' ],
            null,
            true
        );

        wp_enqueue_script(
            'theme-theme-admin-acf-conditional-logic-elements',
            get_theme_file_uri( '/admin/acf-conditional-logic-elements.js' ),
            [ 'acf-input', 'select2' ],
            null,
            true
        );

        wp_enqueue_script(
            'theme-theme-admin-acf-custom-placeholders',
            get_theme_file_uri( '/admin/acf-custom-placeholders.js' ),
            [ 'acf-input', 'select2' ],
            null,
            true
        );

        wp_enqueue_style(
            'theme-theme-admin',
            get_theme_file_uri( '/admin/admin.css' ),
            [ 'acf-field-group' ],
            null
        );
    }
} );

/**
 * Remove the menu management buttons from the admin
 */
add_action( 'admin_menu', '_theme_remove_admin_menu_items' );
add_action('admin_bar_menu', '_theme_admin_bar_remove_menu_options', 5000);
