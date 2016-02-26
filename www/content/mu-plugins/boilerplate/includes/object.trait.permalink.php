<?php

/**
 * File : Permalink Object Trait
 *
 * @package Boilerplate
 */

namespace Boilerplate\Objects;

/**
 * Trait : Permalink Object
 */

trait PermalinkObject
{
	public function __construct()
	{
		add_action('load-options-permalink.php', [ &$this, 'permalink_options' ]);
	}



// ==========================================================================
// Settings
// ==========================================================================

	/**
	 * Add setting to allow administrator to customize
	 * the object's permalink structure.
	 */

	public function permalink_options()
	{
		$obj_type = get_post_type_object( $this->obj_type );
		$setting  = $this->obj_name . '_structure';
		$options  = get_option('boilerplate', []);

		if ( isset( $_POST[ 'boilerplate-' . $setting ] ) )
		{
			$permalink_structure = $_POST[ 'boilerplate-' . $setting ];

			$prefix = '';
			if ( ! got_url_rewrite() ) {
				$prefix = '/index.php';
			}

			if ( ! empty( $permalink_structure ) ) {
				$permalink_structure = preg_replace( '#/+#', '/', '/' . str_replace( '#', '', $permalink_structure ) );

				if ( $prefix ) {
					$permalink_structure = $prefix . preg_replace( '#^/?index\.php#', '', $permalink_structure );
				}
			}

			$options[ $setting ] = $permalink_structure;

			update_option( 'boilerplate', $options );
		}

		add_settings_section( 'boilerplate-permalinks', sprintf( _x( '%s Options', 'project options', 'boilerplate' ), get_bloginfo('name') ), [ &$this, 'permalink_structure_section' ], 'permalink' );

		add_settings_field( 'boilerplate-' . $setting, $obj_type->label, [ &$this, 'permalink_structure_field' ], 'permalink', 'boilerplate-permalinks', [ 'label_for' => 'boilerplate-' . $setting ] );
	}

	/**
	 * Display additional information about the section.
	 */

	public function permalink_structure_section()
	{
		echo '<p>' . __('If you like, you may enter custom structures for your extra content type <abbr title="Universal Resource Locator">URL</abbr>s here.', 'boilerplate') . ' ' . sprintf( __('For example, using a more common "%s" structure would make your article links like <code>http://example.org/editorials/%s/sample-post/</code>. If you leave these blank the defaults—the article name—will be used.', 'boilerplate'), '<strong>' . __('Month and name') . '</strong>', date('Y/m') ) . '</p>';
	}

	/**
	 * Display an input field customize object's permalink.
	 */

	public function permalink_structure_field()
	{
		global $wp_rewrite;

		$obj_tag = '%' . $this->obj_type . '%';
		$setting = $this->obj_name . '_structure';
		$options = get_option('boilerplate', []);
		$value   = ( isset( $options[ $setting ] ) ? $options[ $setting ] : '/' . $obj_tag . '/' );

		$object = get_post_type_object( $this->obj_type );

		$object_prefix = $object->rewrite['slug'];

		$front = ( class_exists('Polylang') ? pll_get_rewrite_front() : $wp_rewrite->front );
		if ( $front and $object->rewrite['with_front'] ) {
			$slug = $front . $object_prefix;
		}

		echo '<code>' . home_url() . '/' . $object_prefix . ( got_url_rewrite() ? '' : '?post_type=' . $this->obj_type . '&p=123' ) . '</code>';

		if ( got_url_rewrite() ) {
			echo '<input name="' . 'boilerplate-' . $setting . '" id="' . 'boilerplate-' . $setting . '" type="text" value="' . esc_attr( $value ) . '" class="regular-text code" />';
		}
	}

}
