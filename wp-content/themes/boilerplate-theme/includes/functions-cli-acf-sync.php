<?php

/**
 * Functions and commands for the WP-CLI to sync ACF and ACF Extended features.
 */

/**
 *  Syncs all ACF field groups, post types, taxonomies and option pages.
 *
 * @param array $args
 * @param array $assoc_args
 *
 * @return void
 */
function theme_cli_command_acf_sync_all( array $args = [], array $assoc_args = [] ) : void {
    // Switch to specified site.
    if ( is_multisite() && isset( $assoc_args['site'] ) ) {
        _theme_cli_acf_sync_switch_to_blog( intval( $assoc_args['site'] ) );
        unset( $assoc_args['site'] );
    }

    theme_cli_command_acf_sync_field_groups( $args, $assoc_args );
    theme_cli_command_acf_sync_post_types( $args, $assoc_args );
    theme_cli_command_acf_sync_taxonomies( $args, $assoc_args );
    theme_cli_command_acf_sync_option_pages( $args, $assoc_args );
}

/**
 *  Syncs all ACF field groups, post types, taxonomies and option pages across all sites of the network.
 *
 * @param array $args
 * @param array $assoc_args
 *
 * @return void
 */
function theme_cli_command_acf_sync_all_sites( array $args = [], array $assoc_args = [] ) : void {
    if ( ! class_exists( 'WP_CLI' ) ) {
        return;
    }

    if ( !is_multisite() ) {
        WP_CLI::log( '❌ This command can only be run when on a multisite environment.' );
        return;
    }

    $sites = get_sites();
    foreach ( $sites as $site ) {
        $assoc_args['site'] = $site->site_id;
        theme_cli_command_acf_sync_all( $args, $assoc_args );

    }
}

/**
 *  Syncs all ACF field groups.
 *
 * @param array $args
 * @param array $assoc_args
 *
 * @return void
 */
function theme_cli_command_acf_sync_field_groups( array $args = [], array $assoc_args = [] ) : void {
    if ( ! class_exists( 'WP_CLI' ) ) {
        return;
    }

    // Switch to specified site.
    if ( is_multisite() && isset( $assoc_args['site'] ) ) {
        _theme_cli_acf_sync_switch_to_blog( intval( $assoc_args['site'] ) );
    }

    // Include dependencies.
    acf_include( 'includes/admin/admin-internal-post-type-list.php' );
    acf_include( 'includes/admin/post-types/admin-field-groups.php' );

    /** @var \ACF_Admin_Field_Groups $field_groups_class */
    $field_groups_class = acf_get_instance( 'ACF_Admin_Field_Groups' );
    WP_CLI::log( "\nSynchronizing field groups..." );
    _theme_cli_acf_sync_element( $field_groups_class );
}

/**
 *  Syncs all ACF option pages.
 *
 * @param array $args
 * @param array $assoc_args
 *
 * @return void
 */
function theme_cli_command_acf_sync_option_pages( array $args = [], array $assoc_args = [] ) : void {
    if ( ! class_exists( 'WP_CLI' ) ) {
        return;
    }

    // Switch to specified site.
    if ( is_multisite() && isset( $assoc_args['site'] ) ) {
        _theme_cli_acf_sync_switch_to_blog( intval( $assoc_args['site'] ) );
    }

    // Include dependencies.
    acf_include( 'includes/admin/admin-internal-post-type-list.php' );
    acf_include( 'pro/admin/post-types/admin-ui-options-pages.php' );

    /** @var \ACF_Admin_UI_Options_Pages $option_pages_class */
    $option_pages_class = acf_get_instance( 'ACF_Admin_UI_Options_Pages' );
    WP_CLI::log( "\nSynchronizing option pages..." );
    _theme_cli_acf_sync_element( $option_pages_class );
}

/**
 *  Syncs all ACF post types.
 *
 * @param array $args
 * @param array $assoc_args
 *
 * @return void
 */
function theme_cli_command_acf_sync_post_types( array $args = [], array $assoc_args = [] ) : void {
    if ( ! class_exists( 'WP_CLI' ) ) {
        return;
    }

    // Switch to specified site.
    if ( is_multisite() && isset( $assoc_args['site'] ) ) {
        _theme_cli_acf_sync_switch_to_blog( intval( $assoc_args['site'] ) );
    }

    // Include dependencies.
    acf_include( 'includes/admin/admin-internal-post-type-list.php' );
    acf_include( 'includes/admin/post-types/admin-post-types.php' );

    /** @var \ACF_Admin_Post_Types $post_types_class */
    $post_types_class = acf_get_instance( 'ACF_Admin_Post_Types' );
    WP_CLI::log( "\nSynchronizing post types..." );
    _theme_cli_acf_sync_element( $post_types_class );
}

/**
 *  Syncs all ACF taxonomies.
 *
 * @param array $args
 * @param array $assoc_args
 *
 * @return void
 */
function theme_cli_command_acf_sync_taxonomies( array $args = [], array $assoc_args = [] ) : void {
    if ( ! class_exists( 'WP_CLI' ) ) {
        return;
    }

    // Switch to specified site.
    if ( is_multisite() && isset( $assoc_args['site'] ) ) {
        _theme_cli_acf_sync_switch_to_blog( intval( $assoc_args['site'] ) );
    }

    // Include dependencies.
    acf_include( 'includes/admin/admin-internal-post-type-list.php' );
    acf_include( 'includes/admin/post-types/admin-taxonomies.php' );

    /** @var \ACF_Admin_Taxonomies $taxonomies_class */
    $taxonomies_class = acf_get_instance( 'ACF_Admin_Taxonomies' );
    WP_CLI::log( "\nSynchronizing taxonomies..." );
    _theme_cli_acf_sync_element( $taxonomies_class );
}

function _theme_cli_acf_sync_element( ACF_Admin_Internal_Post_Type_List $syncedGroup ): void {
    if ( ! class_exists( 'WP_CLI'  ) ) {
        return;
    }

    $syncedGroup->setup_sync();

    // Disable "Local JSON" controller to prevent the .json file from being modified during import.
    acf_update_setting( 'json', false );

    // Sync field groups and generate array of new IDs.
    $files = acf_get_local_json_files( $syncedGroup->post_type );

    // Check if there is anything to sync
    if ( empty( $syncedGroup->sync ) ) {
        WP_CLI::log( "Nothing to synchronize." );
        return;
    }

    foreach ( $syncedGroup->sync as $key => $element ) {
        if ( !isset( $files[$key] ) ) {
            WP_CLI::warning( "❌ Could not synchronize element {$key}" );
            continue;
        }

        $local_element = json_decode( file_get_contents($files[$key]), true );
        $local_element['ID'] = $element['ID'];
        $imported_element = acf_import_internal_post_type( $local_element, $syncedGroup->post_type );
        WP_CLI::log( "✅ Synchronized : {$imported_element["title"]}" );
    }
}

/**
 * Adds the WP_CLI synchronisation commands for ACF.
 */
function _theme_cli_acf_register_sync_commands() : void {
    if ( ! class_exists( 'WP_CLI' ) ) {
        return;
    }

    WP_CLI::add_command( 'acf-sync all',          'theme_cli_command_acf_sync_all' );
    WP_CLI::add_command( 'acf-sync all-sites',    'theme_cli_command_acf_sync_all_sites' );
    WP_CLI::add_command( 'acf-sync field-groups', 'theme_cli_command_acf_sync_field_groups' );
    WP_CLI::add_command( 'acf-sync option-pages', 'theme_cli_command_acf_sync_option_pages' );
    WP_CLI::add_command( 'acf-sync post-types',   'theme_cli_command_acf_sync_post_types' );
    WP_CLI::add_command( 'acf-sync taxonomies',   'theme_cli_command_acf_sync_taxonomies' );
}

function _theme_cli_acf_sync_switch_to_blog( int $blog_id ): void {
    if ( ! class_exists( 'WP_CLI' ) ) {
        return;
    }

    $domain = get_blogaddress_by_id( $blog_id );

    if ( ! empty( $domain ) ) {
        WP_CLI::log( "🔁Switching to {$domain}\n" );
        switch_to_blog( $blog_id );
    } else {
        $default_blog_id = get_current_blog_id();
        $domain = get_blogaddress_by_id( $default_blog_id );
        WP_CLI::log( "❌ Could not find site with ID {$blog_id}." );
        WP_CLI::log( "Defaulting to {$domain}.\n" );
    }
}
