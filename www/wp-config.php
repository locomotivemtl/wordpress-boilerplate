<?php

/**
 * Configuration bootstrapper for WordPress
 *
 * DO NOT EDIT THIS FILE.
 *
 * Edit the config files found in the `config/` directory instead.
 * This file is required in the root directory so WordPress can find it.
 * WordPress is hardcoded to look in its own directory or one directory up
 * for `wp-config.php`.
 */

/**
 * Absolute path to the WordPress directory.
 *
 * Conditionally defined in case it was not defined earlier.
 */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/wordpress/' );
}

/**
 * Absolute path to the WordPress content directory.
 *
 * Repurposed as the project Web root.
 */
define( 'WP_CONTENT_DIR', __DIR__ );

/**
 * Loads the WordPress Plugin API early to allow
 * Composer dependencies to use hooks.
 */
require_once __DIR__ . '/wordpress/wp-includes/plugin.php';

/** Registers the Composer autoloader. */
require_once __DIR__ . '/../vendor/autoload.php';

/** Sets up the configuration for WordPress, the project, and the environment. */
require_once __DIR__ . '/../config/application.php';

/**
 * Conditonally ignores WordPress settings if testing the project.
 */
if ( ! getenv( 'WP_PHPUNIT__TESTS_CONFIG' ) ) {
	/** Sets up WordPress vars and included files. */
	require_once __DIR__ . '/wordpress/wp-settings.php';
}
