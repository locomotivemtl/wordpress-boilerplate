<?php

/**
 * File: Framework Setup & Initialization
 *
 * @package Boilerplate\Includes
 */

namespace Boilerplate\Init;

use Boilerplate\DashboardUtilities as DashUtils;

/**
 * Action: Register the plugin's text domain
 *
 * @uses Action: "muplugins_loaded"
 */

add_action( 'plugins_loaded', function () {

    if ( ! defined('ENUM') ) {
        define( 'ENUM', _x(': ', 'Used to explain or start an enumeration', 'boilerplate') );
    }

    if ( ! defined('SEP') ) {
        define( 'SEP', _x(', ', 'Used to separate items in a list', 'boilerplate') );
    }

    // Enable locale settings for time formatting
    setlocale( LC_TIME, get_locale() );

} );



/**
 * Disable XML-RPC
 *
 * As of WordPress 3.5.0, XML-RPC is enabled by default.
 */

add_filter( 'xmlrpc_enabled', '__return_false' );



/**
 * Alter Yoast SEO Behavior
 *
 * 1. Lower the SEO panel to the bottom
 * 2. Disable Page Analysis
 * 3. Disable Date in the Preview Snippet
 */

/** @see [1] */
add_filter( 'wpseo_metabox_prio', function () {
	return 'low';
} );

/** @see [2] */
# add_filter( 'wpseo_use_page_analysis', '__return_false' );

/** @see [3] */
# add_filter( 'wpseo_show_date_in_snippet', '__return_false' );



/**
 * Add separator after Comments / before Appearance.
 *
 * Fires before the administration menu loads in the admin.
 *
 * @used-by Action: 'admin_menu'
 *
 * @param string $context Empty context.
 */

add_action( 'admin_menu', function ( $menu ) {
	DashUtils\add_admin_menu_separator( 29 );
}, 1 );



/**
 * Enqueue Assets for WordPress Admin
 *
 * @used-by Action: WordPress\'admin_enqueue_scripts'
 */
add_action( 'admin_enqueue_scripts', function () {

    $url = WPMU_PLUGIN_URL . '/boilerplate/assets/styles/dist/admin.css';
    wp_enqueue_style( 'boilerplate-admin', $url );
} );
