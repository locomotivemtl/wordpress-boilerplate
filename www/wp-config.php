<?php

/**
 * Master Configurations for WordPress.
 *
 * This file has the following configurations: MySQL settings, Table Prefix,
 * Secret Keys, WordPress Language, and ABSPATH. You can find more information
 * by visiting {@link http://codex.wordpress.org/Editing_wp-config.php Editing
 * wp-config.php} Codex page. You can get the MySQL settings from your web host.
 *
 * This file is used by the wp-config.php creation script during the
 * installation. You don't have to use the web site, you can just copy this file
 * to "wp-config.php" and fill in the values.
 *
 * Preferably, the wp-config.php file should be version controlled and used
 * solely for production environements and a "local-config.php" file would be
 * used exclusively for local development and staging environements.
 *
 * Table of Contents :
 *    • Configuration Bootstrap
 *    • WordPress Location
 *    • Database Settings
 *    • Plugin Settings
 *    • WordPress Settings
 *    • Multisite Settings
 *    • WordPress Bootstrap
 *
 * @see local-config.php, local-config-sample.php
 *
 * @package WordPress Boilerplate
 */

/* ==========================================================================
   Configuration Bootstrap
   ========================================================================== */

/**
 * @const string DS   Shorthand for {@alias DIRECTORY_SEPARATOR}
 * @const string HT   Horizontal Tab
 * @const string LF   Line Feed: e.g., Unix, Mac OS X
 * @const string CR   Carriage Return: e.g., Mac OS, Commodore
 * @const string CRLF CR+LF: e.g., Microsoft Windows, DOS
 */

if ( ! defined('DS')   ) define( 'DS',   DIRECTORY_SEPARATOR );
if ( ! defined('HT')   ) define( 'HT',   "\t" );
if ( ! defined('LF')   ) define( 'LF',   "\n" );
if ( ! defined('CR')   ) define( 'CR',   "\r" );
if ( ! defined('CRLF') ) define( 'CRLF', CR . LF );

/** @const string BASEPATH Since WordPress is in a subdirectory, this will be the "true" absolute path */
if ( ! defined('BASEPATH') ) define( 'BASEPATH', __DIR__ . DS );

/**
 * Load environment configurations
 */

foreach ( [ 'local', 'shared' ] as $file ) {

    # Look for a configuration directory
    if ( is_dir( BASEPATH . 'config' ) ) {
        $file = 'config' . DS . $file . '.php';
    }
    else {
        $file = $file . '-config.php';
    }

    # Look for a local configuration file for WordPress
    if ( file_exists( BASEPATH . $file ) ) {

        # The config file resides in BASEPATH
        include_once( BASEPATH . $file );

    } elseif ( file_exists( dirname( BASEPATH ) . DS . $file ) && ! file_exists( dirname( BASEPATH ) . DS . 'wp-config.php' ) ) {

        # The config file resides one level above the root but is not part of another install
        include_once( dirname( BASEPATH ) . DS . $file );

    } else {

        # Most likely in production mode

    }
}

/**
 * Set a convenience environment variable in case anyone needs
 * reference, e.g., determining if they wish to serve minified
 * or source scripts and styles.
 */

if ( ! defined('WP_ENV') ) {
    define( 'WP_ENV', ( getenv('APPLICATION_ENV') ?: 'production' ) );
}


/**
 * Fixes SSL check for websites behind load balancers or reverse proxies
 * that support HTTP_X_FORWARDED_PROTO.
 *
 * Sets HTTPS index to false if no HTTPS detected to prevent PHP Notices.
 */

# $_SERVER['HTTPS'] = ( getenv('HTTP_X_FORWARDED_PROTO') && 'https' == getenv('HTTP_X_FORWARDED_PROTO') ? 'on' : false );


/* ==========================================================================
   WordPress Location
   ========================================================================== */

/**
 * This tells WordPress where it can find its core components
 */

if ( ! defined('WP_HOST')        ) define( 'WP_HOST',        getenv('SERVER_NAME') );
if ( ! defined('WP_CORE')        ) define( 'WP_CORE',        ( is_dir( BASEPATH . 'wordpress' ) ? 'wordpress' : '' ) );

if ( ! defined('HAS_HTTPS')      ) define( 'HAS_HTTPS',      ( getenv('HTTPS') && ( 'on' == strtolower( getenv('HTTPS') ) || '1' == getenv('HTTPS') ) ) || ( getenv('SERVER_PORT') && ( '443' == getenv('SERVER_PORT') ) ) );
if ( ! defined('URI_PROTOCOL')   ) define( 'URI_PROTOCOL',   'http' . ( getenv('HTTPS') ? 's' : '' ) . '://' );

if ( ! defined('WP_HOME')        ) define( 'WP_HOME',        URI_PROTOCOL . WP_HOST );
if ( ! defined('WP_SITEURL')     ) define( 'WP_SITEURL',     WP_HOME . '/' . WP_CORE );

if ( ! defined('WP_CONTENT_DIR') ) define( 'WP_CONTENT_DIR', BASEPATH . ( is_dir( BASEPATH . 'content' ) ? 'content' : 'wp-content' ) );
if ( ! defined('WP_CONTENT_URL') ) define( 'WP_CONTENT_URL', WP_HOME . '/' . basename( WP_CONTENT_DIR ) );


/* ==========================================================================
   Database Settings
   ========================================================================== */

/**
 * Give each a unique prefix for multiple installations in one database.
 * Only numbers, letters, and underscores please!
 */

if ( ! isset( $table_prefix ) ) $table_prefix = ( getenv('DB_PREFIX') ?: 'wp_' );

/**
 * You almost certainly do not want to change these.
 *
 * @const string DB_HOST    Database hostname
 * @const string DB_CHARSET Database character set to use in creating database tables. Should be utf8mb4.
 * @const string DB_COLLATE Database collate type.
 */

if ( ! defined('DB_HOST')    ) define( 'DB_HOST',    'localhost' );
if ( ! defined('DB_CHARSET') ) define( 'DB_CHARSET', 'utf8' );
if ( ! defined('DB_COLLATE') ) define( 'DB_COLLATE', '' );


/* ==========================================================================
   Composer Packages
   ========================================================================== */

require_once( WP_CONTENT_DIR . '/vendor/autoload.php');


/* ==========================================================================
   Plugin Settings
   ========================================================================== */

# Enable W3 Total Cache
if ( ! defined('WP_CACHE') ) define( 'WP_CACHE', false );


/* ==========================================================================
   WordPress Settings
   ========================================================================== */

/**
 * Automatic WordPress Core, Plugin, and Theme Updates
 *
 * Automatic Updates are unattended, and by default, will only update WordPress
 * core to maintenance & security releases (for example, from 3.7 to 3.7.1,
 * but not from 3.7.1 to 3.8).
 *
 * @since 3.7
 */

if ( ! defined('AUTOMATIC_UPDATER_DISABLED') ) define( 'AUTOMATIC_UPDATER_DISABLED', false );


/**
 * Automatic Core Updates
 *
 * Determines if the WordPress Core version should update to minor, major, or nightly.
 *
 * Defaults :
 *    • $upgrade_dev Upgrade Nightly — Update daily to the latest nightly, if possible.
 *      (i.e., 3.7-alpha-25000 -> 3.7-alpha-25678 -> 3.7-beta1)
 *      The default is false.
 *    • $upgrade_minor Upgrade Minor — Only update WordPress to in-branch releases.
 *      (i.e., 3.7.0 -> 3.7.1 -> 3.7.2 -> 3.7.4)
 *      The default is true.
 *    • Upgrade Major — Update WordPress to new branch releases.
 *      (i.e., 3.7.0 -> 3.8.0 -> 3.9.1)
 *      The default is false.
 *
 * Settings :
 *    • 'minor' — Only minor updates for core. Undefined default.
 *    • true — ALL updates for core (minor, major, nightly).
 *    • false — Turn off automatic updates, unless a filter allows it.
 *
 * For fine-grained control you can use the following filters to override constant :
 *    • allow_dev_background_core_updates
 *    • allow_minor_background_core_updates
 *    • allow_major_background_core_updates
 *
 * @since 3.7
 */

if ( ! defined('WP_AUTO_UPDATE_CORE') ) define( 'WP_AUTO_UPDATE_CORE', 'minor' );

/**
 * Built-in Theme & Plugin Editors
 *
 * By default WordPress allows users, with administrative permissions,
 * to view and edit theme and plugin files through the file editor
 * accessible though the admin area.
 *
 * It can be very risky to use;
 *    • WordPress users can mess things up. Especially if you (or your client)
 *      don't really know what you're doing.
 *    • It is a gateway for hackers. WordPress is a secure platform,
 *      but users are often the weak link. Most people don’t think about it,
 *      but your WordPress admin account is only as secure as you make it.
 *
 * Disabling the built-in editor disallows the following capabilities :
 *    • edit_files
 *    • edit_plugins
 *    • edit_themes
 *
 * When enabled, malicious users would need to break into your host
 * or your FTP account in order to modify files.
 */

if ( ! defined('DISALLOW_FILE_EDIT') ) define( 'DISALLOW_FILE_EDIT', true );

/**
 * Plugin & Theme Update & Installation
 *
 * This will block users being able to use the plugin and theme
 * installation/update functionality from the WordPress admin area.
 *
 * Setting this constant also disables the Plugin and Theme editor
 * (i.e. you don't need to set DISALLOW_FILE_MODS and DISALLOW_FILE_EDIT,
 * as on it's own DISALLOW_FILE_MODS will have the same effect).
 */

if ( ! defined('DISALLOW_FILE_MODS') ) define( 'DISALLOW_FILE_MODS', false );

/**
 * AutoSave Interval
 *
 * When editing a post, WordPress uses AJAX to auto-save revisions to the post
 * as you edit. Setting this to "false" or zero will not disable the script.
 *
 * Settings :
 *    • integer/number — Delay, in seconds, between auto-saves. (e.g., 30 or 120)
 *      The default is 60 seconds.
 *
 * The auto-save script can be disabled, or deferred, in a number of ways :
 *    1. such as installing the "motionindesign/mu-plugins/webmotion" subpackage
 *       and setting AUTOSAVE_INTERVAL to false for proper deactivation;
 *    2. or setting AUTOSAVE_INTERVAL to a very high number; e.g., 3600 to
 *       postpone saves to a time greater than would be spent editing a post.
 */

if ( ! defined('AUTOSAVE_INTERVAL') ) define( 'AUTOSAVE_INTERVAL', 60 );

/**
 * Post Revisions
 *
 * WordPress will save copies of each edit made to a post or page,  allowing the
 * possibility of reverting to a previous version. The saving of revisions can
 * be disabled, or a maximum number of revisions can be specified.
 *
 * Settings :
 *    • true — Unlimited revisions per post.
 *    • false — Disabled revisions system.
 *    • integer/number — Specify a maximum number of revisions (e.g., 3 or 5).
 *
 * It is recommended to disable post revisions for developers and to limit
 * revisions for copywriters to prevent unncessary database bloat.
 */

if ( ! defined('WP_POST_REVISIONS') ) define( 'WP_POST_REVISIONS', 5 );

/**
 * Empty Trash
 *
 * This constant controls the number of days before WordPress permanently
 * deletes posts, pages, attachments, and comments, from the trash bin.
 *
 * Settings :
 *    • integer/number — Number of days before emptying trash (e.g., 5 or 10).
 *      The default is 30 days.
 *    • 0 — Disable trash. Note that WordPress will not ask for confirmation
 *      when someone clicks on "Delete Permanently".
 *
 * @since 2.9
 */

if ( ! defined('EMPTY_TRASH_DAYS') ) define( 'EMPTY_TRASH_DAYS',  30 );



/* ==========================================================================
   Multisite Settings
   ========================================================================== */

/**
 * @link http://codex.wordpress.org/Create_A_Network
 * @since 3.0
 */

if ( ! defined('WP_ALLOW_MULTISITE') ) define( 'WP_ALLOW_MULTISITE', false );

if ( WP_ALLOW_MULTISITE )
{
    # Domain Mapping : http://wordpress.org/plugins/wordpress-mu-domain-mapping/
    if ( ! defined('SUNRISE')              ) define( 'SUNRISE',              false );

    if ( ! defined('MULTISITE')            ) define( 'MULTISITE',            true );
    if ( ! defined('SUBDOMAIN_INSTALL')    ) define( 'SUBDOMAIN_INSTALL',    true );

    if ( ! defined('DOMAIN_CURRENT_SITE')  ) define( 'DOMAIN_CURRENT_SITE',  WP_HOST );
    if ( ! defined('PATH_CURRENT_SITE')    ) define( 'PATH_CURRENT_SITE',    '/' );

    if ( ! defined('SITE_ID_CURRENT_SITE') ) define( 'SITE_ID_CURRENT_SITE', 1 );
    if ( ! defined('BLOG_ID_CURRENT_SITE') ) define( 'BLOG_ID_CURRENT_SITE', 1 );

    if ( ! defined('ADMIN_COOKIE_PATH')    ) define( 'ADMIN_COOKIE_PATH',    '/' );

    if ( ! defined('SUNRISE') || ( 'on' !== strtolower( SUNRISE ) && true !== SUNRISE && 1 !== SUNRISE ) )
    {
        if ( ! defined('COOKIE_DOMAIN')     ) define( 'COOKIE_DOMAIN',       '' );
        if ( ! defined('COOKIEPATH')        ) define( 'COOKIEPATH',          '' );
        if ( ! defined('SITECOOKIEPATH')    ) define( 'SITECOOKIEPATH',      '' );
    }
}


/* ==========================================================================
   WordPress Bootstrap
   ========================================================================== */

# Absolute path to the WordPress directory
if ( ! defined('ABSPATH') ) define( 'ABSPATH', __DIR__ . WP_CORE . DS );

# Sets up WordPress vars and included files
require_once( ABSPATH . 'wp-settings.php' );