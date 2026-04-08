<?php

/*!
 * Boilerplate Theme Bootstrapper
 *
 * Hooks, constants, and side effects.
 */

if ( defined( 'WP_CLI' ) && WP_CLI ) {
    /** Configures ACF. */
    require_once get_parent_theme_file_path( '/includes/bootstrap-cli.php' );
}

/** Configures ACF. */
require_once get_parent_theme_file_path( '/includes/bootstrap-acf.php' );

/** Configures WordPress Dashboard. */
require_once get_parent_theme_file_path( '/includes/bootstrap-admin.php' );

/** Sets-up the theme's scripts and styles. */
require_once get_parent_theme_file_path( '/includes/bootstrap-assets.php' );

/** Configures the Posts. */
require_once get_parent_theme_file_path( '/includes/bootstrap-post.php' );

/** Configures the theme and site. */
require_once get_parent_theme_file_path( '/includes/bootstrap-site.php' );

/** TODO Configures the flexible content field groups and custom behavior. */
//require_once get_parent_theme_file_path( '/includes/bootstrap-flexible-contents.php' );

/** Configure custom rules to the routing system */
require_once get_parent_theme_file_path( '/includes/bootstrap-routing.php' );

/** Configure the site's navigation */
require_once get_parent_theme_file_path( '/includes/bootstrap-navigation.php' );

/** Configure route of the styleguide page */
require_once get_parent_theme_file_path( '/includes/bootstrap-styleguide.php' );

/*!
 * Classes, interfaces, traits, and enums.
 */

/** Class for custom ACF void location. */
if ( class_exists( 'ACF_Location' ) ) {
    require_once get_parent_theme_file_path( '/includes/class-acf-location-void.php' );
}

/** Enum of custom video providers. */
require_once get_parent_theme_file_path( '/includes/enum-video-provider.php' );

/** Enum of HTML image loading attributes. */
require_once get_parent_theme_file_path( '/includes/enum-loading.php' );

/** Class for a custom video asset. */
require_once get_parent_theme_file_path( '/includes/class-video-asset.php' );

/** Class for a custom video provider registry. */
require_once get_parent_theme_file_path( '/includes/class-video-registry.php' );

/*!
 * Functions and template tags.
 */

/** Functions for ACF. */
require_once get_parent_theme_file_path( '/includes/functions-acf.php' );

/** Functions for Admin Panel. */
require_once get_parent_theme_file_path( '/includes/functions-admin.php' );

/** Functions for scripts and styles. */
require_once get_parent_theme_file_path( '/includes/functions-assets.php' );

/** Todo Functions for formatting flexible contents. */
//require_once get_parent_theme_file_path( '/includes/functions-flexible-contents.php' );

/** Functions for HTML templating. */
require_once get_parent_theme_file_path( '/includes/functions-html.php' );

/** Functions for image templating. */
require_once get_parent_theme_file_path( '/includes/functions-image.php' );


/** Functions for navigation and breadcrumb. */
require_once get_parent_theme_file_path( '/includes/functions-navigation.php' );

/** Functions for custom template tags. */
require_once get_parent_theme_file_path( '/includes/functions-template.php' );

/** General functions for the theme. */
require_once get_parent_theme_file_path( '/includes/functions-theme.php' );

/** General functions for videos. */
require_once get_parent_theme_file_path( '/includes/functions-video.php' );
