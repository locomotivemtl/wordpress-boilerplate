<?php

/**
 * Configuration File: Development
 *
 * Overrides for `WP_ENVIRONMENT_TYPE === 'development'`
 */

use Roots\WPConfig\Config;

// Enable plugin and theme updates and installation from the admin.
Config::define( 'DISALLOW_FILE_MODS', false );

// Disable all post revisions and autosave.
Config::define( 'AUTOSAVE_INTERVAL', false );
Config::define( 'WP_POST_REVISIONS', false );

/**
 * Debugging Settings
 */
Config::define( 'SAVEQUERIES', true );
Config::define( 'WP_DEBUG', true );
Config::define( 'WP_DEBUG_DISPLAY', true );
Config::define( 'WP_DISABLE_FATAL_ERROR_HANDLER', true );
Config::define( 'SCRIPT_DEBUG', true );

ini_set( 'display_errors', '1' );
