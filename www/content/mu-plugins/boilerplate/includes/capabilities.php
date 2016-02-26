<?php

/**
 * File : WordPress User Roles & Capabilities
 *
 * @package Boilerplate
 */

namespace Boilerplate;

/**
 * Class : Capabilities
 */

class Roles
{

	public static function init()
	{
		$class = get_called_class();

		$class = new $class();

		// add_action('init', [ &$class, 'populate' ]);
		add_filter('wpseo_bulk_edit_roles', [ &$class, 'wpseo_edit_roles' ]);
	}

	/**
	 * Add, edit, and remove user roles amd capabilities
	 *
	 * Roles:
	 * - Add `developer`, a super administrator
	 * - Edit `administrator`, an emasculated administrator
	 * - Add `reader`, replaces `subscriber`
	 */

	public function populate()
	{
		// Dummy gettext calls to get strings in the catalog.
		/* translators: user role */
		_x('Developer', 'User role', 'boilerplate');
		/* translators: user role */
		_x('Reader', 'User role', 'boilerplate');

		$this->add_reader_role();
		$this->add_developer_role();
		$this->edit_administrator_role();
	}

	/**
	 * Replace `subscriber` for `reader`
	 */

	private function add_reader_role()
	{
		$role = add_role('reader', 'Reader');

		if ( $role instanceof WP_Role ) {
			$role->add_cap('read');
			$role->add_cap('level_0');

			$users = get_users([ 'role' => 'subscriber' ]);

			foreach ( $users as $user ) {
				$user->remove_role('subscriber');
				$user->add_role('reader');
			}

			update_option('default_role', 'reader');
			remove_role('subscriber');
		}
	}

	/**
	 * Add `developer` as "all-powerful" administator
	 */

	private function add_developer_role()
	{
		// Add Alternate Super Administrator
		$role = get_role('administrator');
		add_role('developer', 'Developer', $role->capabilities);
	}

	/**
	 * Neuter the `administrator` for client-usage.
	 */

	private function edit_administrator_role()
	{
/*
		$role = get_role('administrator');

		if ( $role instanceof WP_Role ) {

			// Since 1.6.0
			$role->add_cap('switch_themes');
			$role->add_cap('edit_themes');
			$role->add_cap('activate_plugins');
			$role->add_cap('edit_plugins');
			$role->add_cap('edit_users');
			$role->add_cap('edit_files');
			$role->add_cap('manage_options');
			$role->add_cap('moderate_comments');
			$role->add_cap('manage_categories');
			$role->add_cap('manage_links');
			$role->add_cap('upload_files');
			$role->add_cap('import');
			$role->add_cap('unfiltered_html');
			$role->add_cap('edit_posts');
			$role->add_cap('edit_others_posts');
			$role->add_cap('edit_published_posts');
			$role->add_cap('publish_posts');
			$role->add_cap('edit_pages');
			$role->add_cap('read');
			$role->add_cap('level_10');
			$role->add_cap('level_9');
			$role->add_cap('level_8');
			$role->add_cap('level_7');
			$role->add_cap('level_6');
			$role->add_cap('level_5');
			$role->add_cap('level_4');
			$role->add_cap('level_3');
			$role->add_cap('level_2');
			$role->add_cap('level_1');
			$role->add_cap('level_0');

			// Since 2.1.0
			$role->add_cap('edit_others_pages');
			$role->add_cap('edit_published_pages');
			$role->add_cap('publish_pages');
			$role->add_cap('delete_pages');
			$role->add_cap('delete_others_pages');
			$role->add_cap('delete_published_pages');
			$role->add_cap('delete_posts');
			$role->add_cap('delete_others_posts');
			$role->add_cap('delete_published_posts');
			$role->add_cap('delete_private_posts');
			$role->add_cap('edit_private_posts');
			$role->add_cap('read_private_posts');
			$role->add_cap('delete_private_pages');
			$role->add_cap('edit_private_pages');
			$role->add_cap('read_private_pages');
			$role->add_cap('delete_users');
			$role->add_cap('create_users');

			// Since 2.3.0
			$role->add_cap( 'unfiltered_upload' );

			// Since 2.5.0
			$role->add_cap( 'edit_dashboard' );

			// Since 2.6.0
			$role->add_cap( 'update_plugins' );
			$role->add_cap( 'delete_plugins' );

			// Since 2.7.0
			$role->add_cap( 'install_plugins' );
			$role->add_cap( 'update_themes' );

			// Since 2.8.0
			$role->add_cap( 'install_themes' );

			// Since 3.0.0
			$role->add_cap( 'update_core' );
			$role->add_cap( 'list_users' );
			$role->add_cap( 'remove_users' );

			# $role->add_cap( 'add_users' );

			$role->add_cap( 'promote_users' );
			$role->add_cap( 'edit_theme_options' );
			$role->add_cap( 'delete_themes' );
			$role->add_cap( 'export' );
		}
*/
	}

	/**
	 * Add custom roles to plugin capabilities
	 *
	 * @see WPSEO\wpseo_add_capabilities()
	 */

	public function wpseo_edit_roles( $roles = [] )
	{
		$developer = get_role('developer');

		if ( $developer instanceof WP_Role ) {
			$roles[] = 'developer';
		}

		return $roles;
	}

}

Roles::init();
