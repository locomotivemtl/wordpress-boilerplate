<?php

/**
 * Hooks for the theme's scripts and styles.
 *
 * The theme uses Vite to compile assets to production.
 *
 * See {@see functions-assets.php} for further details and related utilities.
 */

/**
 * Registers scripts and styles.
 *
 * If a manifest file is present, all listed resources will be registered.
 */
add_action( 'init', function () : void {
    if ( $has_vite_server = theme_is_vite_client_active() ) {
        wp_enqueue_script_module( 'theme-theme-vite-client', theme_get_vite_client_url(), [], null );
    }

    foreach ( theme_get_vite_manifest_data() as $resource ) {
        $resource = wp_parse_args( $resource, [
            'name' => null,
            'file' => null,
            'src'  => null,
        ] );

        if ( ! $resource['file'] && ! $resource['src'] ) {
            continue;
        }

        $resource_uri = ( $has_vite_server && $resource['src'] )
            ? theme_get_vite_server_url() . '/' . $resource['src']
            : get_theme_file_uri( 'dist/' . $resource['file'] );

        // Ensure the assets URLs is served over HTTPS in tunnel environments.
        if (!empty($_SERVER["HTTP_X_FORWARDED_HOST"]) && defined('WP_ENVIRONMENT_TYPE') && WP_ENVIRONMENT_TYPE === 'local') {
            $resource_uri = str_replace('http://', 'https://', $resource_uri);
        }

        $resource_handle = _theme_resolve_asset_name_from_path( $resource['name'] ?? $resource['src'] );

        _theme_vite_register_asset(
            $resource_handle,
            $resource_uri,
            $resource['src']
        );
    }
} );

/**
 * CookieYes (cookie-law-info) Hack Part 1: Intercepts CookieYes JS script to ignores re-evaluation by Swup.
 *
 * @see wp-content/plugins/cookie-law-info/lite/frontend/class-frontend.php
 *
 * @global bool|null $_theme_cookieyes_intercept
 */
add_action( 'wp_head', function () : void {
    global $_theme_cookieyes_intercept;

    if ( $_theme_cookieyes_intercept = is_plugin_active( 'cookie-law-info/cookie-law-info.php' ) ) {
        ob_start();
    }
}, 0 );

/**
 * CookieYes Hack Part 2: Alters the JS script to add Swup data-attribute to ignore re-evaluation.
 *
 * @global bool|null $_theme_cookieyes_intercept
 */
add_action( 'wp_head', function () : void {
    global $_theme_cookieyes_intercept;

    if (
        ! $_theme_cookieyes_intercept ||
        ! ( $wp_head = ob_get_clean() )
    ) {
        return;
    }

    if ( str_contains( $wp_head, 'cookieyes' ) || str_contains( $wp_head, 'cookie-law-info-gcm-js' ) ) {
        echo preg_replace(
            '#<script (id="(?:cookieyes|cookie-law-info-gcm-js)"[^>]+?)( data-swup-ignore-script)?></script>#',
            '<script $1 data-swup-ignore-script onload="document.getElementById(\'cookieyes-banner\')?.setAttribute(\'data-swup-ignore-script\',\'\')"></script>',
            $wp_head
        );
    } else {
        echo $wp_head;
    }
}, 2 );

/**
 * Ignores re-evaluation of all scripts by Swup.
 */
add_filter( 'script_loader_tag', function ( string $tag ) : string {
    if ( str_contains( $tag, 'data-swup-ignore-script' ) ) {
        return $tag;
    }

    return str_replace( '></script>', ' data-swup-ignore-script></script>', $tag );
}, 10, 1 );

/**
 * Alters the output of script tags.
 */
add_filter( 'script_loader_tag', '_theme_filter_asset_loader_tag', 15 );

/**
 * Alters the output of style tags.
 */
add_filter( 'style_loader_tag', '_theme_filter_asset_loader_tag', 15 );

/**
 * Ignores re-evaluation of specific script modules by Swup.
 */
add_filter( 'wp_script_attributes', function ( array $attributes ) : array {
    if ( in_array( ( $attributes['id'] ?? null ), [
        'theme-theme-main-js-module',
    ] ) ) {
        $attributes['data-swup-ignore-script'] = true;
    }

    return $attributes;
} );

/**
 * Enqueues scripts and styles.
 *
 * If a Vite server is available, enqueue its client.
 */
add_action( 'wp_enqueue_scripts', function () : void {
    wp_enqueue_script_module( 'theme-theme-main' );
    wp_enqueue_style( 'theme-theme-main' );
} );

/**
 * As soon as possible, inject Node environment variables
 * if the Vite server is active.
 */
add_action( 'wp_footer', function () : void {
    if ( theme_is_vite_client_active() ) {
        // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript
        printf( "<script data-swup-ignore-script>window.process = %s</script>\n", wp_json_encode( [
            'env' => [ 'NODE_ENV' => 'development' ],
        ] ) );
    }
}, 1 );
