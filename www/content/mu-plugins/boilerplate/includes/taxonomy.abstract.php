<?php

/**
 * File : Abstract Taxonomy Type Class
 *
 * @package Boilerplate
 */

namespace Boilerplate\Taxonomies;

use Boilerplate\AbstractModel;

/**
 * Abstract : Taxonomy Type
 */

abstract class AbstractTaxonomy extends AbstractModel
{
	public $obj_type;
	public $tax_type;

	/**
	 * Singleton
	 *
	 * @param  string  $class
	 */

	public static function get_instance( $class = '' )
	{
		if ( empty( $class ) ) {
			$class = get_called_class();
		}

		if ( ! class_exists( $class ) ) {
			wp_die( sprintf( __('Class "%s" not found.', 'boilerplate'), $class ) );
		}

		return new $class();
	}

	/**
	 * Register Object Type
	 *
	 * @param array $args Array of arguments for registering a post type.
	 *
	 * @uses  WordPress\register_post_type()
	 * @see http://codex.wordpress.org/Function_Reference/register_post_type
	 */

	public function register( $args = [] )
	{
		register_taxonomy( $this->taxonomy, $this->obj_type, $args );
	}

	/**
	 * Check the current object against a WP_Query.
	 *
	 * @param object $obj
     *
	 * @return bool
	 */

	public function is_related( $obj )
	{
		if ( ! is_object( $obj ) ) {
			return null;
		}

		return ( is_a( $obj, 'WP_Query' ) && $this->obj_type === $obj->get('taxonomy') );
	}

}
