<?php

/**
 * File: Page For Archive Object Trait
 *
 * @package  Boilerplate\Dashboard
 */

namespace Boilerplate\Dashboard;

/**
 * Class: Page For Archive Handler
 *
 * Adds support for custom post types to use a static page
 * as their archive stand-in.
 */

class StaticPageHandler
{

	public static function init()
	{
		$c = get_called_class();

		add_action( 'load-options-reading.php', [ $c, 'reading_options' ] );

		/** Make sure Polylang has had a chance to load. */
		add_action( 'plugins_loaded', function () use ( $c ) {
			if ( class_exists( 'Polylang' ) ) {
				/** Translate 'page_for_posts' and 'page_on_front' */
				add_filter( 'option_page_for_posts', [ $c, 'pll_translate_page' ] );
				add_filter( 'option_page_on_front',  [ $c, 'pll_translate_page' ] );
			}
		} );
	}

// ==========================================================================
// Translation
// ==========================================================================

	/**
	 * Translates _page for posts_ and _page on front_
	 *
	 * @used-by Filters\"option_{$option}"
	 * @param   int  $value  The page ID for 'page_for_posts' and 'page_on_front'
	 * @return  int  $value  The translated page ID or the original ID.
	 */

	public static function pll_translate_page( $value )
	{
		if ( class_exists( 'Polylang' ) ) {
			global $polylang;

			/** This hook may be often called so let's store the result */
			static $posts = [];

			$blog_id = get_current_blog_id();

			$lang = pll_current_language();

			if ( $lang ) {
				if ( ! isset( $posts[ $blog_id ][ $value ] ) ) {
					$_value = $polylang->model->get_post( $value, $lang );

					$posts[ $blog_id ][ $value ] = ( $_value ? $_value : null );
				}

				if ( ! is_null( $posts[ $blog_id ][ $value ] ) ) {
					return $posts[ $blog_id ][ $value ];
				}
			}
		}

		return $value;
	}

// ==========================================================================
// Settings
// ==========================================================================

	/**
	 * @param object $obj_type
	 * @return string
	 */

	public static function get_page_for_items_setting( $obj_type )
	{
		return ( isset( $obj_type->labels->page_for_items_setting ) ? $obj_type->labels->page_for_items_setting : ( isset( $obj_type->name ) ? "page_for_{$obj_type->name}" : false ) );
	}

	/**
	 * @param object $obj_type
	 * @return string
	 */

	public static function get_page_for_items_label( $obj_type )
	{
		return ( isset( $obj_type->labels->page_for_items ) ? $obj_type->labels->page_for_items : ( isset( $obj_type->label ) ? sprintf( __( '%1$s: %2$s', 'boilerplate' ), $obj_type->label ) : '' ) );
	}

	/**
	 * Add setting to allow administrator to choose
	 * a static page for their object type archive.
	 */

	public static function reading_options()
	{
		$c = get_called_class();

		$obj_types = get_post_types(
			[
				'public'      => true,
				'has_archive' => true,
				'_builtin'    => false
			],
			'objects'
		);

		if ( count( $obj_types ) ) {
			$options = get_option( 'boilerplate', [] );
			$do_save = false;

			add_settings_section( 'custom_static_pages', sprintf( _x( '%s Options', 'project options', 'boilerplate' ), get_bloginfo('name') ), '', 'reading' );

			foreach ( $obj_types as $obj_type ) {
				$key    = static::get_page_for_items_setting( $obj_type );
				$value = filter_input( INPUT_POST, $key, FILTER_SANITIZE_NUMBER_INT );

				if ( $value ) {
					$options[ $key ] = pll_get_post( $value, pll_default_language() );
					$do_save = true;
				}

				add_settings_field(
					$key,
					__( 'Static pages', 'boilerplate' ),
					[ $c, 'static_page_field' ],
					'reading',
					'custom_static_pages',
					[
						'obj_type' => $obj_type
					]
				);
			}

			if ( $do_save ) {
				update_option( 'boilerplate', $options );
			}
		}
	}

	/**
	 * Display a dropdown list of Pages to select from
	 * to be the object's archive.
	 */

	public static function static_page_field( $args )
	{
		if ( ! isset( $args['obj_type'] ) ) {
			return;
		}

		if ( is_string( $args['obj_type'] ) ) {
			$obj_type = get_post_type_object( $args['obj_type'] );
		}
		else {
			$obj_type = $args['obj_type'];
		}

		$key   = static::get_page_for_items_setting( $obj_type );
		$label = static::get_page_for_items_label( $obj_type );

		$options = get_option( 'boilerplate', [] );
		$value   = ( isset( $options[ $key ] ) ? pll_get_post( $options[ $key ] ) : '' );

		printf(
			$label,
			wp_dropdown_pages( [
				'echo'              => 0,
				'name'              => $key,
				'show_option_none'  => __( '&mdash; Select &mdash;' ),
				'option_none_value' => '0',
				'selected'          => $value
			] )
		);
	}

}

StaticPageHandler::init();
