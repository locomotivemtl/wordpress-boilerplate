<?php

/**
 *
 * Plugin Name:  Boilerplate • Framework
 * Description:  Project architecture, custom object types, utilities, and functionality.
 * Version:      0.0.0
 * Author:       Locomotive
 * Author URI:   https://locomotive.ca
 * License:      © Locomotive
 * Text Domain:  boilerplate
 * Domain Path:  /assets/languages
 * File: Framework Includes
 * Loading methodology documented {@see themes/boilerplate-2016/functions.php}.
 * @package Boilerplate
 */

/** Concatenate WordPress Core Themes */
if ( defined('WP_CORE') && WP_CORE ) {
    register_theme_directory( ABSPATH . 'wp-content/themes/' );
}

/** Soil Modules */
add_theme_support( 'soil-clean-up' );
add_theme_support( 'boilerplate-jquery-cdn' ); // Modified. We need to load jQuery in header because of Gravity Forms
add_theme_support( 'soil-disable-trackbacks' );
add_theme_support( 'soil-disable-asset-versioning' );

/** Substrate Modules */
add_theme_support( 'substrate-page', [ 'template-column' ] );
add_theme_support( 'substrate-outdated-navigator' );
add_theme_support( 'substrate-media', [ 'svg' ] );
add_theme_support( 'substrate-utilities', [ 'formatting', 'media', 'link', 'post' ] );

/**
 * Action: Register the plugin's text domain
 *
 * @uses Action: "muplugins_loaded"
 */

add_action( 'muplugins_loaded', function () {
    load_muplugin_textdomain( 'boilerplate', 'boilerplate/assets/languages' );
} );

/**
 * @var array $framework_includes List of files to include.
 */

$framework_includes = [];

foreach ( $framework_includes as $file ) {
    $filepath = plugin_dir_path( __FILE__ ) . $file;
    if ( ! file_exists( $filepath ) ) {
        trigger_error( sprintf( __( 'Error locating %s for inclusion', 'boilerplate' ), $file ), E_USER_ERROR );
    }

    require_once( $filepath );
}
unset( $file, $filepath );
