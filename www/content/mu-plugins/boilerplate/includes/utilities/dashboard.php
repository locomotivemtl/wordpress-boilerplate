<?php

/**
 * File: Dashboard Utilities
 *
 * @package Boilerplate\Utilities
 */

namespace Boilerplate\DashboardUtilities;

/**
 * Add a top level separator
 *
 * If you're running into PHP errors such as "Invalid argument supplied
 * for `foreach()`" or the "You do not have sufficient permissions to
 * access this page" error, then you've hooked too early. The action hook
 * you should use is {@see `admin_menu`}.
 *
 * @global $menu The WordPress administration menu
 *
 * @param int $position The position in the menu order this one should appear
 */

function add_admin_menu_separator( $position )
{
	global $menu;
	$index = 0;

	foreach( $menu as $offset => $section ) {
		if ( substr( $section[2], 0, 9 ) === 'separator' ) {
			$index++;
		}

		if ( $offset >= $position ) {
			$menu[ $position ] = [ '', 'read', "separator{$index}", '', 'wp-menu-separator' ];
			break;
		}
	}

	ksort( $menu );
}

/**
 * When the post edit page is being displayed.
 *
 * @author Ohad Raz <admin@bainternet.info>
 * @link http://wordpress.stackexchange.com/a/50045/18350
 *
 * @param  string  $new_edit What page to check for accepts new - new post page, edit - edit post page, null for either
 * @return boolean
 */

function is_edit_page( $new_edit = null )
{
	global $pagenow;

	if ( ! is_admin() ) {
		return false;
	}
	else if ( $new_edit == 'edit' ) {
		return in_array( $pagenow, [ 'post.php', ] );
	}
	else if ( $new_edit == 'new' ) {//check for new post page
		return in_array( $pagenow, [ 'post-new.php' ] );
	}
	else {//check for either new or edit
		return in_array( $pagenow, [ 'post.php', 'post-new.php' ] );
	}
}
