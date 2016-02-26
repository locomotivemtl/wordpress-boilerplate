<?php

/**
 * File: Clean up WordPress Administration Area
 *
 * Remove unnecessary features on a per-project basis.
 *
 * @package Boilerplate\CleanUpDashboard
 */

namespace Boilerplate\CleanUpDashboard;

/**
 * Disable various on-screen notifications.
 *
 * Removes:
 * 1. Disable WordPress version checks for non-administrators.
 *    Leave updating to the professionals.
 * 2. Disable login errors for everyone; not knowing whether
 *    the username or password is wrong improves security.
 *
 * @used-by Action: 'admin_init'
 */

function disable_admin_notifications()
{
	/** @see [1] */
	if ( ! current_user_can( 'administrator' ) ) {
		remove_action( 'wp_version_check', 'wp_version_check' );
		remove_action( 'admin_init', '_maybe_update_core' );
		add_filter( 'pre_transient_update_core', '__return_null' );
	}

	/** @see [1] */
	add_filter( 'login_errors', '__return_null' );
}

add_action( 'admin_init', __NAMESPACE__ . '\\disable_admin_notifications' );

/**
 * Remove unnecessary Dashboard Widgets
 *
 * @link http://codex.wordpress.org/Dashboard_Widgets_API
 * @link http://www.deluxeblogtips.com/2011/01/remove-dashboard-widgets-in-wordpress.html
 *
 * @used-by Action: 'wp_dashboard_setup'
 */

function remove_dashboard_widgets()
{
	remove_meta_box( 'dashboard_recent_drafts',   'dashboard', 'side' );
	remove_meta_box( 'dashboard_quick_press',     'dashboard', 'side' );
	remove_meta_box( 'dashboard_primary',         'dashboard', 'side' );
	remove_meta_box( 'dashboard_secondary',       'dashboard', 'side' );
	remove_meta_box( 'dashboard_recent_comments', 'dashboard', 'normal' );
	remove_meta_box( 'dashboard_incoming_links',  'dashboard', 'normal' );
	remove_meta_box( 'dashboard_plugins',         'dashboard', 'normal' );

	/** If WPML is installed, remove it's widget */
	if ( class_exists( 'SitePress' ) ) {
		remove_meta_box( 'icl_dashboard_widget', 'dashboard', 'side' );
	}
}

add_action( 'wp_dashboard_setup', __NAMESPACE__ . '\\remove_dashboard_widgets' );

/**
 * Remove unnecessary widgets
 *
 * @used-by Action: 'admin_head'
 */

function remove_widgets()
{
	remove_meta_box( 'dashboard_recent_drafts',   'dashboard', 'side' );
	remove_meta_box( 'dashboard_quick_press',     'dashboard', 'side' );
	remove_meta_box( 'dashboard_primary',         'dashboard', 'side' );
	remove_meta_box( 'dashboard_secondary',       'dashboard', 'side' );
	remove_meta_box( 'dashboard_recent_comments', 'dashboard', 'normal' );
	remove_meta_box( 'dashboard_incoming_links',  'dashboard', 'normal' );
	remove_meta_box( 'dashboard_plugins',         'dashboard', 'normal' );

	/** If WPML is installed, remove it's widget */
	if ( class_exists( 'SitePress' ) )
	{
		global $post;

		if ( isset( $post->post_type ) ) {
			remove_meta_box( 'icl_div_config', $post->post_type, 'normal' );
		}
	}
}

add_action( 'admin_head', __NAMESPACE__ . '\\remove_widgets', 99 );

/**
 * Remove unnecessary Administration Menu Items
 *
 * The "Link Manager" was deprecated in WordPress 3.5.
 *
 * Removes:
 * 1. Comments
 * 2. Settings → Discussion
 *
 * @used-by Action: 'admin_menu'
 */

function remove_admin_menu_items()
{
	remove_menu_page( 'edit-comments.php' );
	remove_submenu_page( 'options-general.php', 'options-discussion.php' );
}

add_action( 'admin_menu', __NAMESPACE__ . '\\remove_admin_menu_items' );

/**
 * Remove unnecessary Administration Toolbar Nodes
 *
 * The "Link Manager" was deprecated in WordPress 3.5.
 *
 * Removes:
 * 1. Comments
 * 2. New ("+") → User
 *
 * @used-by Action: 'admin_bar_menu'
 */

function remove_admin_bar_nodes( $wp_admin_bar )
{
	$wp_admin_bar->remove_node('updates');
	$wp_admin_bar->remove_node('new-content');
	$wp_admin_bar->remove_node('comments');
	$wp_admin_bar->remove_node('new-user');
	$wp_admin_bar->remove_node('wp-logo');
}

add_action( 'admin_bar_menu', __NAMESPACE__ . '\\remove_admin_bar_nodes', 999 );
