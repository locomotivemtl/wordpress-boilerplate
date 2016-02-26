<?php

/**
 * File : Polylang Object Trait
 *
 * @package Boilerplate
 */

namespace Boilerplate\Objects;

/**
 * Trait : Polylang Object
 */

trait PolylangObject
{
	public function __construct()
	{
		// Translate custom static pages
		add_filter( 'pll_translation_url', [ &$this, 'pll_translate_url' ], 10, 2 );
	}

	/**
	 * Translates URL
	 *
	 * @used-by Filters\"pll_translation_url"
	 * @param   string  $url   The current possible URL
	 * @param   string  $lang  The related language slug
	 * @return  string  $value
	 */

	public function pll_translate_url( $url, $lang )
	{
		if ( empty( $url ) && is_post_type_archive() ) {
			global $polylang, $wp_query;

			$id = $polylang->model->get_post( $wp_query->queried_object_id, $lang );
			if ( $id ) {
				$url = get_page_link( $id );
			}
		}
		elseif ( is_search() ) {
			$slug = pll_translations_x( 'search', 'URI slug', 'boilerplate', $lang );

			if ( $slug ) {
				$url = home_url( user_trailingslashit( "/$lang/$slug/", 'search' ) );
			}
		}

		return $url;
	}

	/**
	 * Register multilingual for this object type
	 *
	 * @param  array $post_types
	 * @param  bool $hide
	 * @return array $post_types
	 * @see    Filter: `pll_get_post_types`
	 */

	public function pll_set_object_type( $post_types = [] )
	{
		if ( ! empty( $this->obj_type ) ) {
			$post_types[ $this->obj_type ] = $this->obj_type;
		}

		return $post_types;
	}

	/**
	 * Translate Object Type Rewrite Rules
	 *
	 * @param  array $post_types
	 * @return array $post_types
	 */

	public function pll_translate_slugs( $post_types = [] )
	{
		global $polylang;

		$object = get_post_type_object( $this->obj_type );

		if ( $this->obj_rewrite ) {
			$translations = [];

			if ( $this->obj_page ) {
				$translations = pll_get_translations( 'page', $this->obj_page );
			}

			if ( empty( $translations ) ) {
				$translations = pll_translations_x( $this->get_rewrite_slug(), 'URI slug', 'boilerplate' );
			}

			if ( is_array( $translations ) && ! empty( $translations ) ) {
				$post_types[ $this->obj_type ] = [];

				foreach ( $translations as $language => $text ) {
					$post_types[ $this->obj_type ][ $language ] = /* (object) */[
						  'has_archive' => ( $object->_builtin ? false : $object->has_archive )
						, 'rewrite'     => [
							'slug'      => ( isset( $text->post_name ) ? $text->post_name : ( isset( $text->slug ) ? $text->slug : $text ) )
						]
					];

                    if ( isset( $object->rewrite['feed'] ) && ! is_null( $object->rewrite['feed'] ) ) {
                        $post_types[ $this->obj_type ][ $language ]['rewrite']['feed'] = $object->rewrite['feed'];
                    }

                    if ( isset( $object->rewrite['pages'] ) && ! is_null( $object->rewrite['pages'] ) ) {
                        $post_types[ $this->obj_type ][ $language ]['rewrite']['pages'] = $object->rewrite['pages'];
                    }
				}
			}
		}

		return $post_types;
	}

	/**
	 * Translate Object Type Rewrite Rules
	 *
	 * @param  array $post_types
	 * @return array $post_types
	 */

	public function pll_translate_taxonomy_slugs( $taxonomies = [] )
	{
		global $polylang;

		$object = get_taxonomy( $this->obj_tax );

		if ( $this->obj_rewrite && isset( $object->rewrite['slug'] ) ) {
			$translations = [];

			if ( empty( $translations ) ) {
				$translations = pll_translations_x( $object->rewrite['slug'], 'URI slug', 'boilerplate' );
			}

			if ( is_array( $translations ) && ! empty( $translations ) ) {
				$taxonomies[ $this->obj_tax ] = [];

				foreach ( $translations as $language => $text ) {
					$taxonomies[ $this->obj_tax ][ $language ] = $text;
				}
			}
		}

		return $taxonomies;
	}

}
