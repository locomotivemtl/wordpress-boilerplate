<?php

/**
 * File: Local Configurations for WordPress
 *
 * This is a sample local-config.php file used for local development and
 * staging environements. Just copy this file to "local-config.php" and fill in
 * the values—usually this will only contain a set of different MySQL database
 * settings for your environment.
 *
 * This file is imported into wp-config.php and can reside in the same path or
 * one level above. The local-config.php file should not be version controlled.
 *
 * You may include other settings here that you only want enabled on your
 * local development checkouts.
 *
 * Table of Contents :
 *    • URLs
 *    • Database
 *    • Developers
 *
 * @see wp-config.php, wp-config-sample.php
 * @link http://codex.wordpress.org/Roles_and_Capabilities
 */

/* ==========================================================================
   URLs
   ========================================================================== */

# define( 'WP_HOST', $_SERVER['SERVER_NAME'] . '/boilerplate' );


/* ==========================================================================
   Database
   ========================================================================== */

/**
 * Staging Environment.
 */

define('DB_NAME',     'boilerplate_name');
define('DB_USER',     'boilerplate_user');
define('DB_PASSWORD', 'xxxxxxxxxxxxxxxx');

/**
 * Production Environment.
 */

# define( 'DB_NAME',     '' );
# define( 'DB_USER',     '' );
# define( 'DB_PASSWORD', '' );


/* ==========================================================================
   Developers
   ========================================================================== */

define( 'AUTOSAVE_INTERVAL', false ); // 3600
define( 'WP_POST_REVISIONS', false );
define( 'EMPTY_TRASH_DAYS',  0 );

define( 'WP_DEBUG',     false );  # WordPress debugging mode
define( 'SAVEQUERIES',  false );  # Save MySQL database queries
define( 'SCRIPT_DEBUG', false );  # Use dev versions of core JS and CSS files
