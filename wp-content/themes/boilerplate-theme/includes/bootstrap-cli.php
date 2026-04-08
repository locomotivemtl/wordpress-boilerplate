<?php

/**
 * Hooks and side effects for the WP-CLI.
 */

/**
 * Bootstraps WP-CLI features for ACF and ACF Extended.
 */
add_action( 'acf/init', function () : void {
    require_once get_parent_theme_file_path( '/includes/functions-cli-acf-sync.php' );

    _theme_cli_acf_register_sync_commands();
} );
