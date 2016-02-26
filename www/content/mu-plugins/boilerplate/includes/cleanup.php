<?php

/**
 * File: Clean up WordPress Administration Area
 *
 * Remove unnecessary features on a per-project basis.
 *
 * @package Boilerplate\CleanUp
 */

namespace Boilerplate\CleanUp;

/**
 * Disable syndication feeds for comments
 *
 * @used-by Action: 'do_feed', 'do_feed_*'
 *
 * @param bool $is_comment_feed Whether the feed is a comment feed.
 */

function disable_admin_notifications( $is_comment_feed )
{
	if ( $is_comment_feed ) {
		wp_die( __( 'No syndication feed available.', 'boilerplate' ) );
	}
}

add_action( 'do_feed',      __NAMESPACE__ . '\\disable_feed', 0 );
add_action( 'do_feed_rdf',  __NAMESPACE__ . '\\disable_feed', 0 );
add_action( 'do_feed_rss',  __NAMESPACE__ . '\\disable_feed', 0 );
add_action( 'do_feed_rss2', __NAMESPACE__ . '\\disable_feed', 0 );
add_action( 'do_feed_atom', __NAMESPACE__ . '\\disable_feed', 0 );

/**
 * Clean up output of archive link tags.
 *
 * @see WordPress\get_archives_link()
 * @used-by Filter: 'get_archives_link'
 *
 * @param string HTML link content for archive.
 * @return string Formatted HTML link content for archive.
 */

function clean_archives_link( $link_html )
{
	return preg_replace( '#=\s*\'(.*?)\'#', '="$1"', $link_html );
}

add_filter( 'get_archives_link', __NAMESPACE__ . '\\clean_archives_link' );

/**
 * Append additional parameters to the oEmbed URL
 *
 * @used-by Filter: 'oembed_fetch_url'
 *
 * @param string $provider URL of the oEmbed provider.
 * @param string $url      URL of the content to be embedded.
 * @param array  $args     Optional arguments, usually passed from a shortcode.
 */

function oembed_query_args( $provider, $url, $args = [] )
{
	if ( isset( $args['autoplay'] ) && (bool) $args['autoplay'] ) {
		$provider = add_query_arg( 'autoplay', 1, $provider );
	}

	return $provider;
}

add_filter( 'oembed_fetch_url', __NAMESPACE__ . '\\oembed_query_args' );


/**
 * Add `class="thumbnail"` to attachment items
 *
 * @param string      $link_html The page link HTML output.
 * @param int         $id        Post ID.
 * @param string      $size      Image size. Default 'thumbnail'.
 * @param bool        $permalink Whether to add permalink to image. Default false.
 * @param bool        $icon      Whether to include an icon. Default false.
 * @param string|bool $text      If string, will be link text. Default false.
 */

function attachment_link_class( $link_html, $id, $size, $permalink, $icon, $text )
{
	/**
	 * Filter the HTML class attribute for an attachment page link.
	 *
	 * @param string      $class     The HTML class attribute value.
	 * @param int         $id        Post ID.
	 * @param string      $size      Image size. Default 'thumbnail'.
	 * @param bool        $permalink Whether to add permalink to image. Default false.
	 * @param bool        $icon      Whether to include an icon. Default false.
	 * @param string|bool $text      If string, will be link text. Default false.
	 */

	$classes = apply_filters( 'boilerplate/attachment_link_class', '', $id, $size, $permalink, $icon, $text );

	if ( ! empty( $classes ) && false === strpos( $link_html, ' class="' ) ) {

		$link_html = str_replace( 'href', 'class="' . esc_attr( $classes ) . '" href', $link_html );
	}

	return $link_html;
}

add_filter( 'wp_get_attachment_link', __NAMESPACE__ . '\\attachment_link_class', 99, 6 );

/**
 * Filter the default caption shortcode output.
 *
 * If the filtered output isn't empty, it will be used instead of generating
 * the default caption template.
 *
 * @see img_caption_shortcode()
 *
 * @param string $output  The caption output. Default empty.
 * @param array  $attr    Attributes of the caption shortcode.
 * @param string $content The image element, possibly wrapped in a hyperlink.
 */

function img_caption( $output, $attr, $content )
{
	if ( is_feed() ) {
		return $output;
	}

	$defaults = [
		'id'      => '',
		'align'   => 'alignnone',
		'width'   => '',
		'caption' => ''
	];

	$attr = shortcode_atts( $defaults, $attr );

	// If the width is less than 1 or there is no caption, return the content wrapped between the [caption] tags
	if ( 1 > $attr['width'] || empty( $attr['caption'] ) ) {
		return $content;
	}

	// Set up the attributes for the caption <div>
	$attributes  = ( ! empty( $attr['id'] ) ? ' id="' . esc_attr( $attr['id'] ) . '"' : '' );
	$attributes .= ' class="thumbnail wp-caption ' . esc_attr( $attr['align'] ) . '"';
	$attributes .= ' style="width: ' . esc_attr( $attr['width'] ) . 'px"';

	$output  = '<figure' . $attributes .'>';
	$output .= do_shortcode( $content );
	$output .= '<figcaption class="caption wp-caption-text">' . $attr['caption'] . '</figcaption>';
	$output .= '</figure>';

	return $output;
}

add_filter( 'img_caption_shortcode', __NAMESPACE__ . '\\img_caption', 99, 6 );
