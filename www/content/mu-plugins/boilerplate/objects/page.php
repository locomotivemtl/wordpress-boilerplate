<?php

/**
 * File : "Page" Object Type
 * Post Type : page
 *
 * Default Post Type
 *
 * Page in WordPress is like post, but it lives outside the normal time-based
 * listings of posts. Pages can use different page templates to display them.
 * Pages can also be organized in a hierarchical structure, with pages being
 * parents to other pages, but they normally cannot be assigned categories
 * and tags. If permalinks are enabled, the permalink of a page is always
 * composed solely of the main site URL and the user-friendly and URL-valid
 * names (also referred to as slug) of the page and its parents if they exist.
 * See the Pages article for more information about the differences.
 */

namespace Boilerplate\Objects;

/**
 * Class : "Page" Object Type
 *
 * @link https://gist.github.com/leoken/4395160
 */

class Page extends AbstractObject
{
	public $obj_type = 'page';
	public $obj_name = 'pages';

	protected $templates;

	protected $registration_hooks;

	public function __construct()
	{
		$this->templates = [
			'redirection-child'  => __('Redirect to first child', 'boilerplate'),
			'redirection-parent' => __('Redirect to closest parent', 'boilerplate')
		];

		/**
		 * This collection of hooks are used to trigger the registration
		 * of fake custom page templates.
		 */
		$this->registration_hooks = [
			'action' => 'substrate/page_template_column/column_added',
			'filter' => 'page_attributes_dropdown_pages_args',
			'filter' => 'quick_edit_dropdown_pages_args',
			'filter' => 'wp_insert_post_data'
		];

		add_action( 'init', [ &$this, 'modify_object_type' ] );

		// Add a hook to various filters and actions to inject templates into the cache.
		foreach ( $this->registration_hooks as $hook => $func ) {
			call_user_func_array( "add_{$hook}", [ $func, [ &$this, "{$hook}_noop" ], 1 ] );
		}

		add_filter( 'template_include',  [ &$this, 'template_include'  ], 1 );
		add_action( 'template_redirect', [ &$this, 'template_redirect' ], 1 );
	}

// ==========================================================================
// Object Type
// ==========================================================================

	/**
	 * Modify "Page" Object Type
	 *
	 * Disable support for Authoring and Comments for this default object type.
	 */

	public function modify_object_type()
	{
		remove_post_type_support( $this->obj_type, 'author' );
		remove_post_type_support( $this->obj_type, 'comments' );
		remove_post_type_support( $this->obj_type, 'custom-fields' );
	}



// ==========================================================================
// Special Page Templates
// ==========================================================================

// Registration of Templates
// ==========================================================================

	/**
	 * Proxy "noop" for the WordPress Action API.
	 *
	 * @param mixed This function may be called on an action.
	 *
	 * @return void|mixed
	 */

	public function action_noop()
	{
		$action = current_action();

		$args = func_get_args();

		$this->noop( 'action', $action );

		if ( count( $args ) ) {
			return reset( $args );
		}
	}

	/**
	 * Proxy "noop" for the WordPress Filter API.
	 *
	 * @param mixed This function may be called on a filter.
	 *
	 * @return void|mixed
	 */

	public function filter_noop()
	{
		$filter = current_filter();

		$args = func_get_args();

		$this->noop( 'filter', $filter );

		if ( count( $args ) ) {
			return reset( $args );
		}
	}

	/**
	 * A noop method to trigger the addition of custom templates. This method will
	 * execute once when triggered via the WordPress Hooks API.
	 *
	 * @param string $hook  Either an "action" or a "filter".
	 * @param string $event The hook name.
	 *
	 * @return void|mixed
	 */

	public function noop( $hook = false, $event = false )
	{
		if ( $hook && $event ) {
			foreach ( $this->registration_hooks as $__hook => $__event ) {
				call_user_func_array( "remove_{$__hook}", [ $__event, [ &$this, "{$__hook}_noop" ], 1 ] );
			}
		}

		$this->register_templates();
	}

	/**
	 * Adds fake custom templates to the pages cache in order to trick WordPress
	 * into thinking the template file exists where it doens't really exist.
	 */

	public function register_templates()
	{
		// Create the key used for the themes cache
		$cache_key = 'page_templates-' . md5( get_theme_root() . '/' . get_stylesheet() );

		// Retrieve the cache list.
		// If it doesn't exist, or it's empty prepare an array
		$templates = wp_get_theme()->get_page_templates();
		if ( empty( $templates ) ) {
			$templates = [];
		}

		// New cache, therefore remove the old one
		wp_cache_delete( $cache_key , 'themes');

		// Now add our template to the list of templates by merging our templates
		// with the existing templates array from the cache.
		$templates = array_merge( $templates, $this->templates );

		// Add the modified cache to allow WordPress to pick it up for listing
		// available templates
		wp_cache_add( $cache_key, $templates, 'themes', 1800 );
	}



// Parsing of Templates
// ==========================================================================

	/**
	 * Filter the path of the current template before including it,
	 * executing any template swaps.
	 *
	 * @used-by Filter: 'template_include'
	 *
	 * @param string $template The path of the template to include.
	 *
	 * @return string $template
	 */

	public function template_include( $template )
	{
		if ( ! is_admin() && is_home() ) {
			global $post;

			$file = basename( $template );

			if ( 'home.php' !== $file && locate_template( 'archive.php' ) ) {
				$template = preg_replace( '#' . $file . '$#', 'archive.php', $template );
			}
		}

		return $template;
	}

	/**
	 * Fires before determining which template to load,
	 * executing any custom template redirections.
	 *
	 * @used-by Action: 'template_redirect'
	 *
	 * @todo Remove this condition from 'redirection-child': `$post->post_parent === 0`.
	 */

	public function template_redirect()
	{
		if ( ! is_admin() && is_page() ) {
			global $post;

			$post->template = get_post_meta( $post->ID, '_wp_page_template', true );

			if ( isset( $this->templates[ $post->template ] ) ) {
				if ( 'redirection-child' === $post->template ) {
					$destination = get_children( [
						'numberposts' => 1,
						'post_parent' => $post->ID,
						'post_type'   => 'page',
						'post_status' => 'publish',
						'orderby'     => 'menu_order',
						'order'       => 'ASC'
					] );
				}

				if ( 'redirection-parent' === $post->template && $post->post_parent > 0 ) {
					$destination = get_post( $post->post_parent );
				}

                if ( 1 == count( $destination ) && wp_redirect( get_permalink( current( $destination )->ID ), 301 ) ) {
                    exit;
                }
			}
		}
	}

}

Page::get_instance();
