<?php

/**
 * Functions for interacting with ACF and ACF Extended.
 */

/**
 * Disables the default behaviour of ACF oEmbed field type's
 * format value that involves resolving the value.
 */
function _theme_acf_disable_oembed_format_value() : void {
    $oembed_field_type = acf_get_field_type( 'oembed' );

    remove_filter( 'acf/format_value/type=oembed', [ $oembed_field_type, 'format_value' ] );
}

/**
 * Regsiters custom tabs and settings for ACF field groups, custom post types,
 * custom taxonomies, and options pages.
 */
function _theme_acf_register_theme_settings() : void {
    foreach ( [
        'field_group',
        'post_type',
        'taxonomy',
        'ui_options_page',
    ] as $_acf_module_type ) {
        if ( 'field_group' === $_acf_module_type ) {
            add_filter( 'acf/field_group/additional_group_settings_tabs', 'theme_acf_filter_theme_settings_tabs', 10, 1 );
            add_action( 'acf/field_group/render_group_settings_tab/theme_settings', function ( array $data ) use ( $_acf_module_type ) : void {
                theme_acf_render_theme_settings_tab( $data, "acf_{$_acf_module_type}" );
            }, 10, 1 );
        } else {
            add_filter( "acf/{$_acf_module_type}/additional_settings_tabs", 'theme_acf_filter_theme_settings_tabs', 10, 1 );
            add_action( "acf/{$_acf_module_type}/render_settings_tab/theme_settings", function ( array $data ) use ( $_acf_module_type ) : void {
                theme_acf_render_theme_settings_tab( $data, "acf_{$_acf_module_type}" );
            }, 10, 1 );
        }
    }
}

/**
 * Renders custom settings for an ACF module.
 *
 * @param array<string, mixed> $data A field group, post type, taxonomy, or options page.
 */
function theme_acf_render_theme_settings_tab( array $data, string $type ) : void {
    acf_render_field_wrap( [
        'label'         => __( 'Theme', 'default' ),
        'instructions'  => __( 'Save/Load to given theme.', 'theme-theme' ),
        'name'          => 'theme_local_theme',
        'prefix'        => $type,
        'type'          => 'select',
        'value'         => ( $data['theme_local_theme'] ?? get_template() ),
        'choices'       => [
            'theme-theme'      => __( 'Common', 'theme-theme' ),
            'theme-theme' => __( 'Corporation', 'theme-theme' ),
            'theme-theme-collegiate'  => __( 'Collegiate', 'theme-theme' ),
        ],
    ] );
}

/**
 * Adds a custom tab to ACF module settings.
 *
 * @param  array<string, string> $tabs
 * @return array<string, string>
 */
function theme_acf_filter_theme_settings_tabs( array $tabs ) : array {
    $tabs['theme_settings'] = __( 'theme Settings', 'theme-theme' );

    return $tabs;
}

/**
 * Configures and renders important form data as hidden inputs.
 *
 * This function is an alternative to {@see acf_form_data()}
 * to avoid rendering an `#acf-form-data` container.
 *
 * @param array<string, mixed> $data
 */
function theme_acf_form_data( array $data = [] ) : void {
    $data = wp_parse_args( $data, [
        /** @type string The current screen (post, user, taxonomy, etc). */
        'screen'     => 'post',
        /** @type int|string The ID of current post being edited. */
        'post_id'    => 0,
        /** @type bool Enables AJAX validation. */
        'validation' => true,
    ] );

    // Create nonce using screen.
    $data['nonce'] = wp_create_nonce( $data['screen'] );

    // Append "changed" input used within "_wp_post_revision_fields" action.
    $data['changed'] = 0;

    acf_set_form_data( $data );

    foreach ( $data as $name => $value ) {
        acf_hidden_input( [
            'id'    => '_acf_' . $name,
            'name'  => '_acf_' . $name,
            'value' => $value,
        ] );
    }

    /** This filter is documented in plugins/advanced-custom-fields-pro/includes/acf-form-functions.php */
    do_action( 'acf/form_data', $data );
    do_action( 'acf/input/form_data', $data );
}

/**
 * Retrieves the list of allowed MIME types from the given field.
 *
 * @return string[]
 */
function theme_acf_get_field_mime_types( string $selector, mixed $post_id = false ) : array {
    $field = get_field_object( $selector, $post_id );
    if ( ! $field ) {
        return [];
    }

    if ( empty( $field['mime_types'] ) ) {
        return [];
    }

    if ( is_array( $field['mime_types'] ) ) {
        return $field['mime_types'];
    }

    if ( is_string( $field['mime_types'] ) ) {
        return explode( ',', $field['mime_types'] );
    }

    return [];
}

/**
 * Registers an ACF honeypot field.
 *
 * Defaults to a "validate email" type field.
 *
 * @param array<string, mixed> $args
 */
function theme_acf_register_honeypot_field( array $args = [] ) : void {
    acf_add_local_field( wp_parse_args( $args, [
        'prefix'    => 'acf',
        'name'      => '_validate_email',
        'key'       => '_validate_email',
        'label'     => __( 'Valider l\'adresse courriel', 'theme-theme' ),
        'type'      => 'text',
        'value'     => '',
        'wrapper'   => [
            'style' => 'display:none !important;',
        ],
    ] ) );
}

/**
 * Fixes ACF reCAPTCHA field type translations.
 *
 * @listens filter:gettext_acfe
 * @listens filter:gettext_default
 *
 * @param string $translation Translated text.
 * @param string $text        Text to translate.
 *
 * @return string
 */
function _theme_acfe_filter_gettext_translation( string $translation, string $text ) : string {
    if ( $translation !== $text ) {
        return $translation;
    }

    return match ($text) {
        /**
         * Missing domain and inadequate translation.
         *
         * @see wp-content/plugins/acf-extended-pro/includes/fields/field-recaptcha.php
         *
         * @todo Move theme's translations to external file.
         */
        'Invalid reCaptcha, please try again' => _x( 'CAPTCHA invalide, veuillez réessayer', 'acf validation', 'theme-theme' ),
        default => $translation,
    };
}

/**
 * Adds the WP_CLI synchronisation commands for ACF.
 *
 * @return void
 */
function _theme_acf_register_wp_cli_sync_commands() {
    if ( class_exists( 'WP_CLI' ) ) {
        WP_CLI::add_command( 'acf-sync all-sites', '_theme_acf_sync_all_sites_command' );
        WP_CLI::add_command( 'acf-sync all', '_theme_acf_sync_all_command' );
        WP_CLI::add_command( 'acf-sync field-groups', '_theme_acf_sync_field_groups_command' );
        WP_CLI::add_command( 'acf-sync post-types', '_theme_acf_sync_post_types_command' );
        WP_CLI::add_command( 'acf-sync taxonomies', '_theme_acf_sync_taxonomies_command' );
        WP_CLI::add_command( 'acf-sync option-pages', '_theme_acf_sync_option_pages_command' );
    }
}

/**
 *  Syncs all ACF field groups, post types, taxonomies and option pages across all sites of the network.
 *
 * @param array $args
 * @param array $assoc_args
 *
 * @return void
 */
function _theme_acf_sync_all_sites_command( array $args = [], array $assoc_args = [] ) : void {
    if ( !class_exists( 'WP_CLI' ) ) {
        return;
    }

    if ( !_theme_acf_sync_is_multisite() ) {
        WP_CLI::log("❌ This command can only be run when on a multisite environment.");
        return;
    }
    $sites = get_sites();

    foreach ( $sites as $site ) {
        $assoc_args['site'] = $site->site_id;
        _theme_acf_sync_all_command($args, $assoc_args);

    }
}

/**
 *  Syncs all ACF field groups, post types, taxonomies and option pages.
 *
 * @param array $args
 * @param array $assoc_args
 *
 * @return void
 */
function _theme_acf_sync_all_command( array $args = [], array $assoc_args = [] ) : void {
    // Switch to specified site.
    if ( _theme_acf_sync_is_multisite() && isset( $assoc_args['site'] ) ) {
        _theme_acf_sync_switch_to_blog(intval( $assoc_args['site'] ) );
        unset( $assoc_args['site'] );
    }

    _theme_acf_sync_field_groups_command( $args, $assoc_args );
    _theme_acf_sync_post_types_command( $args, $assoc_args );
    _theme_acf_sync_taxonomies_command( $args, $assoc_args );
    _theme_acf_sync_option_pages_command( $args, $assoc_args );
}

function _theme_acf_sync_is_multisite() : bool
{
    return theme_get_environment_var( 'MULTISITE', false );
}

function _theme_acf_sync_switch_to_blog( int $blog_id ): void
{
    if ( !class_exists( 'WP_CLI' ) ) {
        return;
    }

    $domain = get_blogaddress_by_id( $blog_id );

    if ( !empty($domain) ) {
        WP_CLI::log( "🔁Switching to {$domain}\n" );
        switch_to_blog( $blog_id );
    } else {
        $default_blog_id = get_current_blog_id();
        $domain = get_blogaddress_by_id( $default_blog_id );
        WP_CLI::log( "❌ Could not find site with ID {$blog_id}." );
        WP_CLI::log( "Defaulting to {$domain}.\n" );
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
function _theme_acf_sync_field_groups_command( array $args = [], array $assoc_args = [] ) : void {
    if ( !class_exists( 'WP_CLI' ) ) {
        return;
    }

    // Switch to specified site.
    if ( _theme_acf_sync_is_multisite() && isset( $assoc_args['site'] ) ) {
        _theme_acf_sync_switch_to_blog( intval( $assoc_args['site'] ) );
    }

    // Include dependencies.
    acf_include( 'includes/admin/admin-internal-post-type-list.php' );
    acf_include( 'includes/admin/post-types/admin-field-groups.php' );

    /** @var \ACF_Admin_Field_Groups $field_groups_class */
    $field_groups_class = acf_get_instance( 'ACF_Admin_Field_Groups' );
    WP_CLI::log( 'Synchronizing field groups...' );
    _theme_acf_sync_element( $field_groups_class );
}

/**
 *  Syncs all ACF post types.
 *
 * @param array $args
 * @param array $assoc_args
 *
 * @return void
 */
function _theme_acf_sync_post_types_command( array $args = [], array $assoc_args = [] ) : void {
    if ( !class_exists( 'WP_CLI' ) ) {
        return;
    }

    // Switch to specified site.
    if ( _theme_acf_sync_is_multisite() && isset( $assoc_args['site'] ) ) {
        _theme_acf_sync_switch_to_blog( intval( $assoc_args['site'] ) );
    }

    // Include dependencies.
    acf_include( 'includes/admin/admin-internal-post-type-list.php' );
    acf_include( 'includes/admin/post-types/admin-post-types.php' );

    /** @var \ACF_Admin_Post_Types $post_types_class */
    $post_types_class = acf_get_instance( 'ACF_Admin_Post_Types' );
    WP_CLI::log( 'Synchronizing post types...' );
    _theme_acf_sync_element( $post_types_class );
}

/**
 *  Syncs all ACF taxonomies.
 *
 * @param array $args
 * @param array $assoc_args
 *
 * @return void
 */
function _theme_acf_sync_taxonomies_command( array $args = [], array $assoc_args = [] ) : void {
    if ( !class_exists( 'WP_CLI' ) ) {
        return;
    }

    // Switch to specified site.
    if ( _theme_acf_sync_is_multisite() && isset( $assoc_args['site'] ) ) {
        _theme_acf_sync_switch_to_blog( intval( $assoc_args['site'] ) );
    }

    // Include dependencies.
    acf_include( 'includes/admin/admin-internal-post-type-list.php' );
    acf_include( 'includes/admin/post-types/admin-taxonomies.php' );

    /** @var \ACF_Admin_Taxonomies $taxonomies_class */
    $taxonomies_class = acf_get_instance( 'ACF_Admin_Taxonomies' );
    WP_CLI::log( 'Synchronizing taxonomies...' );
    _theme_acf_sync_element( $taxonomies_class );
}

/**
 *  Syncs all ACF option pages.
 *
 * @param array $args
 * @param array $assoc_args
 *
 * @return void
 */
function _theme_acf_sync_option_pages_command( array $args = [], array $assoc_args = [] ) : void {
    if ( !class_exists( 'WP_CLI' ) ) {
        return;
    }

    // Switch to specified site.
    if ( _theme_acf_sync_is_multisite() && isset( $assoc_args['site'] ) ) {
        _theme_acf_sync_switch_to_blog( intval( $assoc_args['site'] ) );
    }

    // Include dependencies.
    acf_include( 'includes/admin/admin-internal-post-type-list.php' );
    acf_include( 'pro/admin/post-types/admin-ui-options-pages.php' );

    /** @var \ACF_Admin_UI_Options_Pages $option_pages_class */
    $option_pages_class = acf_get_instance( 'ACF_Admin_UI_Options_Pages' );
    WP_CLI::log( 'Synchronizing option pages...' );
    _theme_acf_sync_element( $option_pages_class );
}

function _theme_acf_sync_element( ACF_Admin_Internal_Post_Type_List $syncedGroup ): void
{
    if ( !class_exists( 'WP_CLI'  ) ) {
        return;
    }

    $syncedGroup->setup_sync();

    // Disable "Local JSON" controller to prevent the .json file from being modified during import.
    acf_update_setting( 'json', false );

    // Sync field groups and generate array of new IDs.
    $files = acf_get_local_json_files( $syncedGroup->post_type );

    // Check if there is anything to sync
    if ( empty( $syncedGroup->sync ) ) {
        WP_CLI::log( "✅ Nothing to synchronize.\n" );
        return;
    }

    foreach ( $syncedGroup->sync as $key => $element ) {
        if ( !isset( $files[$key] ) ) {
            WP_CLI::warning( "❌ Could not synchronize element {$key}\n" );
            continue;
        }

        $local_element = json_decode( file_get_contents($files[$key]), true );
        $local_element['ID'] = $element['ID'];
        $imported_element = acf_import_internal_post_type( $local_element, $syncedGroup->post_type );
        WP_CLI::success( "✅ Synchronized : {$imported_element["title"]}\n" );
    }
}
/**
 * Determines if the given params match a layout.
 *
 * @see \acf_is_field()
 *
 * @param  mixed $field A field array.
 * @return bool
 */
function _theme_acf_is_field_layout($field): bool {
    return is_array($field) && isset($field['key'], $field['name']);
}

/**
 * Determines if the given identifier is a layout key.
 *
 * @see \acf_is_field_key()
 *
 * @param  string $id The identifier.
 * @return bool
 */
function _theme_acf_is_field_layout_key($id): bool
{
    // Check if $id is a string starting with "layout_".
    if (is_string($id) && substr($id, 0, 7) === 'layout_') {
        return true;
    }

    /**
     * Filters whether the $id is a field group key.
     *
     * @event filter:acf/is_field_layout_key
     *
     * @param bool   $bool The result.
     * @param string $id   The identifier.
     */
    return apply_filters('acf/is_field_layout_key', false, $id);
}
