<?php

/**
 * File: Theme includes
 *
 * The $theme_includes array determines the code library included in your theme.
 * Add or remove files to the array as needed. Supports child theme overrides.
 *
 * Please note that missing files will produce a fatal error.
 *
 * @link https://github.com/roots/sage/pull/1042 Based on Sage
 * @package Boilerplate\Includes
 */

/**
 * @var array $theme_includes List of files to include.
 */

$theme_includes = [
    'includes/utilities/constants.php',
    'includes/utilities/general.php',
    'includes/utilities/query.php',
    'includes/utilities/formatting.php',
    'includes/utilities/assets.php',
    'includes/init.php',                  // Initial theme setup and constants
    'includes/editor.php',                // TinyMCE added features
    'includes/titles.php',                // Page titles
    'includes/excerpts.php',              // Page or post excerpts
    'includes/seo.php',                   // Yoast SEO modifications
    'includes/gravityforms.php',          // Gravity forms modifications
    'includes/nav.php',                   // Custom nav modifications
    'includes/nav-primary.php',
    'includes/assets.php',
    'includes/ajax.php'
];

foreach ( $theme_includes as $file ) {
    if ( ! $filepath = locate_template( $file ) ) {
        trigger_error( sprintf( __( 'Error locating %s for inclusion', 'boilerplate' ), $file ), E_USER_ERROR );
    }

    require_once( $filepath );
}
unset( $file, $filepath );
