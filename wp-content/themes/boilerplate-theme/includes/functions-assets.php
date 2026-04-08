<?php

/**
 * Functions for the theme's scripts, styles, and other assets.
 *
 * The theme uses Vite to compile assets to production.
 *
 * Vite's Hot Module Replacement (HMR) can be used to rapidly preview your changes.
 * By default, Vite's serves assets on-the-fly from `http://localhost:5173`.
 *
 * The function {@see theme_is_vite_client_active()} will check if Vite's server
 * is available.
 *
 * The server's URL can be changed using the following environment variables
 * or global constants:
 *
 * ```
 * VITE_SERVER_URL=https://example.test
 * VITE_SERVER_PORT=1234
 * ```
 *
 * @phpstan-type WPScriptModule array{
 *     src:string,
 *     version:string,
 *     enqueue:bool,
 *     dependencies:list<array{
 *         id:string,
 *         import:'dynamic'|'static'
 *     }>
 * }
 */

/**
 * Determines whether a script module has been added to the queue or registered.
 *
 * This function is similar to {@see wp_script_is()} and {@see wp_style_is()}
 * but includes support for script modules.
 *
 * @param string  $handle Name of the script.
 * @param ?string $match  The path or file name of the source asset.
 *
 *     If absent, the function will query script modules, then scripts, then styles.
 *
 *     This may lead to false-positives if an asset's handle is used by
 *     a script and a style.
 * @param string $status Status of the script to check. Default 'enqueued'.
 *     Accepts 'enqueued' or 'registered'.
 *
 * @return bool Whether the script module is queued.
 */
function theme_asset_is(
    string $handle,
    ?string $match = null,
    string $status = 'enqueued'
) : bool {
    return (bool) theme_asset_query( $handle, $match, $status );
}

/**
 * Queries the script modules, scripts, styles for the given asset.
 *
 * This function is similar to {@see WP_Dependencies::query()}
 * but includes support for script modules.
 *
 * @param string  $handle Name of the script.
 * @param ?string $match  The path or file name of the source asset.
 *
 *     If absent, the function will query script modules, then scripts, then styles.
 *
 *     This may lead to false-positives if an asset's handle is used by
 *     a script and a style.
 * @param string $status Status of the script to check. Default 'enqueued'.
 *     Accepts 'enqueued' or 'registered'.
 *
 * @return _WP_Dependency|\WP_Script_Modules|bool If found, the asset or false.
 */
function theme_asset_query(
    string $handle,
    ?string $match = null,
    string $status = 'registered'
) : _WP_Dependency|array|bool {
    if ( $match ) {
        return match ( pathinfo( $match, PATHINFO_EXTENSION ) ) {
            'css'   => wp_styles()->query( $handle, $status ),
            'js'    => wp_scripts()->query( $handle, $status ),
            'ts'    => _theme_script_module_query( $handle, $status ),
            default => false,
        };
    }

    return (
        _theme_script_module_query( $handle, $status ) ||
        wp_scripts()->query( $handle, $status ) ||
        wp_styles()->query( $handle, $status )
    );
}

/**
 * Enqueues the script using Vite if available.
 *
 * @param string $path The relative path to the script's source file.
 *     The script's handle will be extracted from the source file's name.
 */
function theme_enqueue_vite_script( string $path ) : void {
    $resource_handle = _theme_resolve_asset_name_from_path( $path );

    if ( theme_is_vite_client_active() ) {
        $resource_uri = theme_get_vite_server_file_uri( $path );

        $response_code = wp_remote_retrieve_response_code(
            wp_remote_get( $resource_uri )
        );

        if ( $response_code >= 200 && $response_code <= 299 ) {
            _theme_vite_enqueue_asset( $resource_handle, $resource_uri );
        }
    } else {
        _theme_vite_enqueue_asset( $resource_handle, $path );
    }
}

/**
 * Retrieves the URL of the WYSIWYG content editor stylesheet.
 *
 * @TODO add Support for vite client
 *
 * @param  string $name The editor name.
 * @return string The full URL to the WYSIWYG content editor stylesheet.
 */
function theme_get_acf_style_uri( string $name ) : string {
    return theme_is_vite_client_active()
        ? theme_get_vite_server_file_uri( "src/styles/admin/acf-{$name}.css" )
        : theme_get_vite_manifest_file_uri( "acf-{$name}-*.css" );
}

/**
 * Retrieves the URL of the symbol in the theme's SVG spritesheet.
 *
 * @param  string $symbol The target symbol.
 * @return string The full URL to the SVG spritesheet's symbol.
 */
function theme_get_svg_sprite_symbol_uri( string $symbol ) : string {
    $sprite_url = theme_is_vite_client_active()
        ? get_theme_file_uri( 'dist/sprite.svg' )
        : theme_get_vite_manifest_file_uri( 'sprite.svg' );

    // Ensure the sprite URL is served over HTTPS in tunnel environments.
    if (!empty($_SERVER["HTTP_X_FORWARDED_HOST"]) && defined('WP_ENVIRONMENT_TYPE') && WP_ENVIRONMENT_TYPE === 'local') {
        $sprite_url = str_replace('http://', 'https://', $sprite_url);
    }

    return $sprite_url . "#{$symbol}";
}

function theme_get_vite_client_url() : string {
    return theme_get_vite_server_url() . '/@vite/client';
}

/**
 * @return array<string, array{file:string, name?:string, src:string, isEntry:bool}>
 */
function theme_get_vite_manifest_data( bool $reload = false ) : array {
    static $manifest_data;

    if ( false === $reload && is_array( $manifest_data ) ) {
        return $manifest_data;
    }

    $manifest_path = theme_get_vite_manifest_path();
    if ( ! is_readable( $manifest_path ) ) {
        return $manifest_data = [];
    }

    return $manifest_data = wp_json_file_decode( $manifest_path, [ 'associative' => true ] );
}

/**
 * Retrieves the URL of a file from the Vite manifest.
 *
 * @param  string $symbol The file to search for.
 * @return string The URL of the file.
 */
function theme_get_vite_manifest_file_uri( string $file ) : string {
    $manifest = theme_get_vite_manifest_data();

    $file = ltrim( $file, '/' );

    if ( str_contains( $file, '*' ) ) {
        $pattern = str_replace( [ '.', '*' ], [ '\\.', '.+' ], $file );
        $pattern = "~^{$pattern}$~";

        foreach ( $manifest as $resource ) {
            foreach ( [ 'file', 'src', 'name' ] as $key ) {
                if ( isset( $resource[ $key ] ) && preg_match( $pattern, $resource[ $key ] ) ) {
                    return get_theme_file_uri( 'dist/' . $resource['file'] );
                }
            }
        }
    }

    if ( isset( $manifest[ $file ] ) ) {
        return get_theme_file_uri( 'dist/' . $manifest[ $file ]['file'] );
    }

    return get_theme_file_uri( 'dist/' . $file );
}

function theme_get_vite_manifest_path() : string {
    return get_theme_file_path( 'dist/manifest.json' );
}

/**
 * Retrieves the URL of a file from the Vite server.
 *
 * @param  string $symbol The file to search for.
 * @return string The URL of the file.
 */
function theme_get_vite_server_file_uri( string $file ) : string {
    $file = ltrim( $file, '/' );

    return theme_get_vite_server_url() . '/' . $file;
}

function theme_get_vite_server_url() : string {
    static $server_url;

    if ( $server_url ) {
        return $server_url;
    }

    $server_url = getenv( 'VITE_SERVER_URL' )
        ?: (
            defined( 'VITE_SERVER_URL' )
            ? constant( 'VITE_SERVER_URL' )
            : 'http://localhost'
        );

    $server_port = getenv( 'VITE_SERVER_PORT' )
        ?: (
            defined( 'VITE_SERVER_PORT' )
            ? constant( 'VITE_SERVER_PORT' )
            : 5173
        );
    if ( is_numeric( $server_port ) ) {
        $server_url = rtrim( $server_url, '/' ) . ":{$server_port}";
    }

    return $server_url;
}

/**
 * Determines if Vite server is available.
 */
function theme_is_vite_client_active( bool $retry = false ) : bool {
    static $has_vite_server;

    if ( false === $retry && is_bool( $has_vite_server ) ) {
        return $has_vite_server;
    }

    if ( wp_get_environment_type() !== 'local' ) {
        return $has_vite_server = false;
    }

    $response_code = wp_remote_retrieve_response_code(
        wp_remote_get( theme_get_vite_client_url(), [
            'sslverify' => false,
        ] )
    );

    return $has_vite_server = ( $response_code >= 200 && $response_code <= 299 );
}

/**
 * Displays the URL of the symbol in the theme's SVG spritesheet.
 *
 * @param string $symbol The target symbol.
 */
function theme_svg_sprite_symbol_uri( string $symbol ) : void {
    echo theme_get_svg_sprite_symbol_uri( $symbol );
}

/**
 * Filters the HTML link and script tag of an enqueued style.
 *
 * This function cleans up the output of stylesheet `<link>` and `<script>` tags.
 *
 * @access private
 *
 * @listens filter:script_loader_tag
 * @listens filter:style_loader_tag
 */
function _theme_filter_asset_loader_tag( string $html ) : string {
    return str_replace( "'", '"', $html );
}

/**
 * Determines whether a script module has been added to the queue or registered.
 *
 * This function is similar to {@see wp_script_is()} but for {@see WP_Script_Modules}
 * using PHP's reflection API given WordPress' class does not provide any public
 * API to query the registry.
 *
 * @access private
 *
 * @param string $handle Name of the script.
 * @param string $status Status of the script to check. Default 'enqueued'.
 *     Accepts 'enqueued' or 'registered'.
 *
 * @return bool Whether the script module is queued.
 */
function _theme_script_module_is( string $handle, string $status = 'enqueued' ) : bool {
    if ( function_exists( '_wp_scripts_maybe_doing_it_wrong' ) ) {
        _wp_scripts_maybe_doing_it_wrong( __FUNCTION__, $handle );
    }

    return (bool) _theme_script_module_query( $handle, $status );
}

/**
 * Queries the script module.
 *
 * This function is similar to {@see WP_Dependencies::query()} but for
 * script modules using PHP's reflection API given WordPress' class does
 * not provide any public API to query the registry.
 *
 * @access private
 *
 * @param string $handle Name of the script.
 * @param string $status Status of the script to check. Default 'enqueued'.
 *     Accepts 'enqueued' or 'registered'.
 *
 * @return \WP_Script_Modules|false If found, the asset or false.
 */
function _theme_script_module_query( string $handle, string $status = 'registered' ) : array|false {
    $wp_script_modules = wp_script_modules();

    $registered_property = new ReflectionProperty( $wp_script_modules, 'registered' );
    $registered_property->setAccessible(true);
    $registered_modules = $registered_property->getValue( $wp_script_modules );

    $registered_module = $registered_modules[ $handle ] ?? null;

    if ( ! $registered_module ) {
        return false;
    }

    switch ( $status ) {
        case 'registered': {
            return $registered_module;
        }

        case 'enqueued': {
            return ( $registered_module['enqueue'] ?? false )
                ? $registered_module
                : false;
        }
    }

    return false;
}

/**
 * @access private
 */
function _theme_resolve_asset_name_from_path( string $path ) : string {
    $resource_name = pathinfo( $path, PATHINFO_FILENAME );

    if ( preg_match( '~^_?(sprite)-[a-z\d]{8}$~i', $resource_name, $matches ) ) {
        $resource_name = $matches[1];
    }

    return "theme-theme-{$resource_name}";
}

/**
 * Helper function to output a {@see _doing_it_wrong()} message
 * for Vite scripts and styles.
 *
 * @access private
 *
 * @param string $function_name Function name.
 * @param string $handle        Optional. Name of the script or stylesheet that was
 *                              registered or enqueued too early. Default empty.
 */
function _theme_vite_assets_maybe_doing_it_wrong(
    string $function_name,
    ?string $handle = null
) : void {
    $message = __( 'Vite scripts and styles should not be registered or enqueued without a URL.', 'theme-common' );

    if ( $handle ) {
        $message .= ' ' . sprintf(
            /* translators: %s: Name of the script or stylesheet. */
            __( 'This notice was triggered by the %s handle.' ),
            '<code>' . $handle . '</code>'
        );
    }

    _doing_it_wrong(
        $function_name,
        $message,
        '2025-01-17'
    );
}

/**
 * @access private
 *
 * @param ?string  $src   The absolute URL of the asset.
 * @param ?string  $match The path or file name of the source asset.
 *     Determines whether the a script is ESM or JS based on its source.
 * @param string[] $deps
 * @param mixed    $args
 */
function _theme_vite_enqueue_asset(
    string $handle,
    ?string $src = null,
    ?string $match = null,
    array $deps = [],
    bool|string|null $ver = null,
    mixed $args = null
) : void {
    $match ??= $src;
    $src   ??= '';

    if ( ! $match ) {
        _theme_vite_assets_maybe_doing_it_wrong( __FUNCTION__, $handle );
        return;
    }

    switch ( pathinfo( $match, PATHINFO_EXTENSION ) ) {
        case 'css':
            wp_enqueue_style( $handle, $src, $deps, $ver, ( $args ?? 'all' ) );
            return;

        case 'js':
            wp_enqueue_script( $handle, $src, $deps, $ver, $args );
            return;

        case 'ts':
            wp_enqueue_script_module( $handle, $src, $deps, $ver );
            return;
    }
}

/**
 * @access private
 *
 * @param ?string  $src   The absolute URL of the asset.
 * @param ?string  $match The path or file name of the source asset.
 *     Determines whether the a script is ESM or JS based on its source.
 * @param string[] $deps
 * @param mixed    $args
 */
function _theme_vite_register_asset(
    string $handle,
    ?string $src = null,
    ?string $match = null,
    array $deps = [],
    bool|string|null $ver = null,
    mixed $args = null
) : void {
    $match ??= $src;
    $src   ??= '';

    if ( ! $match ) {
        _theme_vite_assets_maybe_doing_it_wrong( __FUNCTION__, $handle );
        return;
    }

    switch ( pathinfo( $match, PATHINFO_EXTENSION ) ) {
        case 'css':
            wp_register_style( $handle, $src, $deps, $ver, ( $args ?? 'all' ) );
            return;

        case 'js':
            wp_register_script( $handle, $src, $deps, $ver, $args );
            return;

        case 'ts':
            wp_register_script_module( $handle, $src, $deps, $ver );
            return;
    }
}
