<?php

/**
 * File: Canonical API Enhanacements
 *
 * Improves document metadata by supporting more than just
 * singular pages. Enhancements in this file add support for
 * singular posts, pages, home page, front page, taxonomy,
 * category, tag & archives, post type archives, dated archives.
 *
 * It also deals with pagination, both for paged posts and pages
 * and all paginated archives.
 *
 * @link https://core.trac.wordpress.org/ticket/18660
 * @todo Move to Substrate
 * @package Boilerplate\Canonical
 */

namespace Boilerplate\Canonical;

if ( ! class_exists( 'WPSEO_Frontend' ) ) :

	remove_action( 'wp_head', 'rel_canonical' );
	remove_action( 'wp_head', 'Roots\\Soil\\CleanUp\\rel_canonical' );
	remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0 );

	add_action( 'wp_head', __NAMESPACE__ . '\\rel_canonical' );
	add_action( 'wp_head', __NAMESPACE__ . '\\adjacent_rel_links', 10, 0 );

	/**
	 * Output rel=canonical
	 *
	 * @see WordPress\rel_canonical()
	 * @see Roots\Soil\CleanUp\rel_canonical()
	 */

	function rel_canonical()
	{
		$link = false;

		if ( is_singular() ) {
			global $wp_rewrite;

			$link = get_permalink( get_queried_object() );

			if ( get_query_var( 'page' ) > 1 ) {
				if ( ! $wp_rewrite->using_permalinks() ) {
					$link = add_query_arg( 'page', get_query_var( 'page' ), $link );
				}
				else {
					$link = user_trailingslashit( trailingslashit( $link ) . get_query_var( 'page' ), 'single' );
				}
			}
		} else {
			$link = get_current_archive_link();
		}

		/**
		 * Filter the canonical URL of the current page.
		 *
		 * @param string $link The canonical URL.
		 */

		$link = apply_filters( 'rel_canonical', $link );

		if ( $link ) {
			echo '<link rel="canonical" href="' . esc_url( $link, 'canonical' ) . '" />' . "\n";
		}
	}

	/**
	 * Output rel=next and rel=prev links
	 *
	 * On archives pages these go to next and previous archive pages,
	 * when available. On singular paginated posts & pages these go to
	 * the next page within the post or page.
	 */

	function adjacent_rel_links()
	{
		global $wp_query;

		if ( ! is_singular() ) {
			$url = get_current_archive_link( false );

			if ( $url ) {
				$paged = get_query_var( 'paged' );

				if ( 0 == $paged )
					$paged = 1;

				if ( $paged > 1 )
					echo get_adjacent_rel_link( "prev", $url, $paged-1, true, 'category' );

				if ( $paged < $wp_query->max_num_pages )
					echo get_adjacent_rel_link( "next", $url, $paged+1, true, 'category' );
			}
		} else if ( $wp_query->post ) {
			$numpages = substr_count( $wp_query->post->post_content, '<!--nextpage-->' ) + 1;
			if ( $numpages > 1 ) {
				$page = get_query_var( 'page' );
				if ( !$page )
					$page = 1;

				$url = get_permalink( $wp_query->post->ID );

				// If the current page is the frontpage, pagination should use /base/
				if ( 'page' == get_option( 'show_on_front' ) && get_option( 'page_on_front' ) == $wp_query->post->ID )
					$usebase = true;
				else
					$usebase = false;

				if ( $page > 1 )
					echo get_adjacent_rel_link( 'prev', $url, $page - 1, $usebase, 'single_paged' );
				if ( $page < $numpages )
					echo get_adjacent_rel_link( 'next', $url, $page + 1, $usebase, 'single_paged' );
			}
		}
	}

	/**
	 * Get adjacent pages link for archives
	 *
	 * @param  string $rel                   Link relationship, "prev" or "next".
	 * @param  string $url                   The unpaginated URL of the current archive.
	 * @param  string $page                  The page number to add on to $url for the $link tag.
	 * @param  bool   $incl_pagination_base  Whether or not to include /page/ or not.
	 * @param  string $context               Context of the adjacent links, passed on to `user_trailingslashit()`.
	 * @return string $link link element
	 */

	function get_adjacent_rel_link( $rel, $url, $page, $incl_pagination_base, $context )
	{
		global $wp_rewrite;

		if ( ! $wp_rewrite->using_permalinks() ) {
			if ( $page > 1 ) {
				$url = add_query_arg( 'paged', $page, $url );
			}
		} else {
			if ( $page > 1 ) {
				$base = '';

				if ( $incl_pagination_base ) {
					$base = trailingslashit( $wp_rewrite->pagination_base );
				}

				$url = user_trailingslashit( trailingslashit( $url ) . $base . $page, $context );
			}
		}

		$link = '<link rel="' . esc_attr( $rel ) . '" href="' . $url . '" />' . "\n";

		/**
		 * Filter the URL of the sibling document.
		 *
		 * @param string $link    The sibling URL.
		 * @param string $context Context of the adjacent links, passed on to `user_trailingslashit()`.
		 */

		return apply_filters( "{$rel}_rel_link", $link, $context );
	}

endif;
