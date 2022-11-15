<?php

/**
 * Main Configuration File
 *
 * Your base production configuration goes in this file.
 *
 * Environment-specific overrides go in their respective
 * `config/environments/{{WP_ENVIRONMENT_TYPE}}.php` file.
 *
 * A good default policy is to deviate from the production config
 * as little as possible. Try to define as much of your configuration
 * in this file as you can.
 */

use Dotenv\Dotenv;
use Roots\WPConfig\Config;

use function Env\env;

/**
 * Absolute path to the project base directory.
 *
 * @var string
 */
$base_path = dirname( __DIR__ );

/**
 * Use Dotenv to set required environment variables and load '.env' file in root
 */
$dotenv = Dotenv::createUnsafeImmutable( $base_path );
if ( file_exists( $base_path . '/.env' ) ) {
	$dotenv->load();
	$dotenv->required( [
		'WP_HOME',
		'WP_SITEURL',
	] );

	if ( ! env( 'DATABASE_URL' ) ) {
		$dotenv->required( [
			'DB_NAME',
			'DB_USER',
			'DB_PASSWORD',
		] );
	}
}

/**
 * The global environment constant for WordPress and the project.
 *
 * @var string|null
 */
$wp_env_type = env( 'WP_ENVIRONMENT_TYPE' );

/**
 * The `WP_ENVIRONMENT_TYPE` constant is officially supported by WordPress.
 */
define( 'WP_ENVIRONMENT_TYPE', ( $wp_env_type ?? 'production' ) );

/**
 * The `WP_ENV` constant is required by certain plugins from Roots for Bedrock.
 */
define( 'WP_ENV', WP_ENVIRONMENT_TYPE );

/**
 * URLs
 */
Config::define( 'WP_HOME', env( 'WP_HOME' ) );
Config::define( 'WP_SITEURL', env( 'WP_SITEURL' ) );
Config::define( 'WP_CONTENT_URL', Config::get( 'WP_HOME' ) );

/**
 * Database Settings
 */
Config::define( 'DB_NAME', env( 'DB_NAME' ) );
Config::define( 'DB_USER', env( 'DB_USER' ) );
Config::define( 'DB_PASSWORD', env( 'DB_PASSWORD' ) );
Config::define( 'DB_HOST', ( env( 'DB_HOST' ) ?? 'localhost' ) );
Config::define( 'DB_CHARSET', ( env( 'DB_CHARSET' ) ?? 'utf8mb4' ) );
Config::define( 'DB_COLLATE', ( env( 'DB_COLLATE' ) ?? '' ) );
Config::define( 'DB_PREFIX', ( env( 'DB_PREFIX' ) ?? 'wp_' ) );

/**
 * The database table prefix. Assigned to a global variable
 * in {@see /wordpress/wp-settings.php}.
 *
 * @var string
 */
$table_prefix = Config::get( 'DB_PREFIX' );

/**
 * The Data Source Name (DSN) for connecting to a database.
 *
 * @var string|null
 */
$dsn = env( 'DATABASE_URL' );

if ( $dsn ) {
	/** @psalm-var array{host:string, port: ?int, user: string, pass: ?string, path: string} */
	$dsn = parse_url( $dsn );

	Config::define( 'DB_NAME', substr( $dsn['path'], 1 ) );
	Config::define( 'DB_USER', $dsn['user'] );
	Config::define( 'DB_PASSWORD', ( $dsn['pass'] ?? null ) );
	Config::define( 'DB_HOST', ( isset( $dsn['port'] ) ? "{$dsn['host']}:{$dsn['port']}" : $dsn['host'] ) );
}

/**
 * Authentication Unique Keys and Salts
 *
 * @link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service
 * @link https://roots.io/salts.html Roots' WordPress secret-key service
 */
Config::define( 'AUTH_KEY', env( 'AUTH_KEY' ) );
Config::define( 'SECURE_AUTH_KEY', env( 'SECURE_AUTH_KEY' ) );
Config::define( 'LOGGED_IN_KEY', env( 'LOGGED_IN_KEY' ) );
Config::define( 'NONCE_KEY', env( 'NONCE_KEY' ) );
Config::define( 'AUTH_SALT', env( 'AUTH_SALT' ) );
Config::define( 'SECURE_AUTH_SALT', env( 'SECURE_AUTH_SALT' ) );
Config::define( 'LOGGED_IN_SALT', env( 'LOGGED_IN_SALT' ) );
Config::define( 'NONCE_SALT', env( 'NONCE_SALT' ) );

/**
 * Custom Settings
 */
// Disable all automatic updates since WP is managed by Composer.
Config::define( 'AUTOMATIC_UPDATER_DISABLED', true );

// Allow environment variable to control WP-Cron.
Config::define( 'DISABLE_WP_CRON', ( env( 'DISABLE_WP_CRON' ) ?? false ) );

// Disable the plugin and theme file editor in the admin.
Config::define( 'DISALLOW_FILE_EDIT', true );

// Disable plugin and theme updates and installation from the admin.
Config::define( 'DISALLOW_FILE_MODS', true );

// Allow environment variable or environment type to control indexing of your site.
Config::define( 'DISALLOW_INDEXING', ( env( 'DISALLOW_INDEXING' ) ?? ( WP_ENVIRONMENT_TYPE !== 'production' ) ) );

/**
 * Debugging Settings
 */
Config::define( 'WP_DEBUG_DISPLAY', false );
Config::define( 'WP_DEBUG_LOG', ( env( 'WP_DEBUG_LOG' ) ?? false ) );
Config::define( 'SCRIPT_DEBUG', false );

ini_set( 'display_errors', '0' );

/**
 * Prevent issues with PHP's CLI environement.
 *
 * @link https://make.wordpress.org/cli/handbook/guides/troubleshooting/#wordpress-configuration-file-wp-config-php
 */
if ( defined( 'WP_CLI' ) && WP_CLI && ! isset( $_SERVER['HTTP_HOST'] ) ) {
	$_SERVER['HTTP_HOST'] = 'host.local';
}

/**
 * Allow WordPress to detect HTTPS when used behind
 * a reverse proxy or a load balancer.
 *
 * @link https://codex.wordpress.org/Function_Reference/is_ssl#Notes
 */
if ( isset( $_SERVER['HTTP_X_FORWARDED_PROTO'] ) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https' ) {
	$_SERVER['HTTPS'] = 'on';
}

/**
 * Conditionally loads the environment-specific configuration file.
 *
 * @var string
 */
$env_conf = __DIR__ . '/environments/' . WP_ENVIRONMENT_TYPE . '.php';
if ( file_exists( $env_conf ) ) {
	require_once $env_conf;
}

/**
 * Defines all constants and throw an exception
 * if we are attempting to redefine a constant.
 */
Config::apply();
