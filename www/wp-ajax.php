<?php
/**
 * WordPress Ajax Process Execution
 *
 * This file is a near-identical copy of {@see wp-admin/admin-ajax.php} from WordPress v6.1.0.
 *
 * Differences:
 * 1. Constant `WP_ADMIN` is set to FALSE, by default.
 * 2. WordPress action 'admin_init' is replaced with custom action 'ajax_init'.
 * 4. Custom actions hooked on 'admin_init' are not executed.
 * 3. Core WordPress actions are not hooked.
 *
 * Usage:
 * 1. Add file to the WordPress content directory (WP_CONTENT_DIR), or anywhere else accessible by URI.
 * 2. If the file is added elsewhere, change the path to {@see wp-load.php}, on line 39, to ensure the file can be included.
 * 3. Change your JavaScript to point AJAX requests to 'wp-ajax.php' instead of 'admin-ajax.php'.
 *
 * Note:
 * - Maintained by Chauncey McAskill: {@link https://gist.github.com/mcaskill/95acb103a5e5a78a7184b38fbacfa66e mcaskill/wp-ajax.php}.
 * - Inspired by Sully Syed's {@link https://gist.github.com/yllus/8181d8670fd296854c1e41078d969cc1 yllus/admin-ajax.php}.
 *
 * @package WordPress
 * @subpackage HTTP
 *
 * @link https://codex.wordpress.org/AJAX_in_Plugins
 */

/**
 * Executing Ajax process.
 *
 * @since 2.1.0
 */
define( 'DOING_AJAX', true );
if ( ! defined( 'WP_ADMIN' ) ) {
	define( 'WP_ADMIN', false );
}

/** Load WordPress Bootstrap */
require_once __DIR__ . '/wordpress/wp-load.php';

/** Allow for cross-domain requests (from the front end). */
send_origin_headers();

header( 'Content-Type: text/html; charset=' . get_option( 'blog_charset' ) );
header( 'X-Robots-Tag: noindex' );

// Require a valid action parameter.
if ( empty( $_REQUEST['action'] ) || ! is_scalar( $_REQUEST['action'] ) ) {
	wp_die( '0', 400 );
}

/** Load WordPress Administration APIs */
require_once ABSPATH . 'wp-admin/includes/admin.php';

/** Load Ajax Handlers for WordPress Core */
require_once ABSPATH . 'wp-admin/includes/ajax-actions.php';

send_nosniff_header();
nocache_headers();

/**
 * Fires as a AJAX request is being initialized.
 *
 * Note, this should replace the 'admin_init' hook.
 *
 * This is roughly analogous to the more general {@see 'init'} hook, which fires earlier.
 *
 * @event action:ajax_init
 */
do_action( 'ajax_init' );

add_action( 'wp_ajax_nopriv_heartbeat', 'wp_ajax_nopriv_heartbeat', 1 );

$action = $_REQUEST['action'];

if ( is_user_logged_in() ) {
	// If no action is registered, return a Bad Request response.
	if ( ! has_action( "wp_ajax_{$action}" ) ) {
		wp_die( '0', 400 );
	}

	/**
	 * Fires authenticated Ajax actions for logged-in users.
	 *
	 * The dynamic portion of the hook name, `$action`, refers
	 * to the name of the Ajax action callback being fired.
	 *
	 * @since 2.1.0
	 */
	do_action( "wp_ajax_{$action}" );
} else {
	// If no action is registered, return a Bad Request response.
	if ( ! has_action( "wp_ajax_nopriv_{$action}" ) ) {
		wp_die( '0', 400 );
	}

	/**
	 * Fires non-authenticated Ajax actions for logged-out users.
	 *
	 * The dynamic portion of the hook name, `$action`, refers
	 * to the name of the Ajax action callback being fired.
	 *
	 * @since 2.8.0
	 */
	do_action( "wp_ajax_nopriv_{$action}" );
}

// Default status.
wp_die( '0' );