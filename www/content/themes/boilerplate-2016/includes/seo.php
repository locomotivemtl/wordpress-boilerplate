<?php

/**
 * SEO Utilities
 *
 * @package Boilerplate\Includes
 */



/**
 * Handle <title> cleanup
 *
 * @param  string $title
 * @return $title
 */

function boilerplate_wpseo_title( $title )
{
	$site_name = get_bloginfo('name');
	$separator = wpseo_retrieve_seperator();

	$suffix = ' ' . $separator . ' ' . $site_name;

	if ( is_post_type_archive() ) {
		$queried_object = get_queried_object();

		if ( ! empty( $queried_object->labels ) ) {
			$queried_title = $queried_object->labels->name;
		}
		else if ( ! empty( $queried_object->post_title ) ) {
			$queried_title = $queried_object->post_title;
		}

		if ( !empty( $queried_title ) ) {
			return apply_filters( 'the_title', $queried_title ) . $suffix;
		}
	}

	if ( is_search() && ! get_search_query() ) {
		return __('Search', 'boilerplate') . $suffix;
	}

	return $title;
}

add_filter('wpseo_title', 'boilerplate_wpseo_title', 1);



/**
 * Handle og:title cleanup
 *
 * @param  string $title
 * @return $title
 */

function boilerplate_meta_title( $title )
{
	// if ( ! is_front_page() ) {
		$site_name = get_bloginfo('name');
		$separator = wpseo_retrieve_seperator();

		$remove = '#\s*(?:' . preg_quote( $separator ) . ')\s*(?:' . preg_quote( $site_name ) . ')#i';

		$title = preg_replace( $remove, '', $title );
	// }

	return $title;
}

add_filter('wpseo_twitter_title',    'boilerplate_meta_title', 1);
add_filter('wpseo_opengraph_title',  'boilerplate_meta_title', 1);
add_filter('wpseo_googleplus_title', 'boilerplate_meta_title', 1);



/**
 * Retrieve the separator for use as replacement string.
 *
 * @see WPSEO_Replace_Vars::retrive_sep()
 *
 * @return string
 */

function wpseo_retrieve_seperator()
{
	$replacement = WPSEO_Options::get_default( 'wpseo_titles', 'separator' );

	// Get the titles option and the separator options
	$titles_options    = get_option('wpseo_titles');
	$seperator_options = WPSEO_Option_Titles::get_instance()->get_separator_options();

	// This should always be set, but just to be sure
	if ( isset( $seperator_options[ $titles_options['separator'] ] ) ) {
		// Set the new replacement
		$replacement = $seperator_options[ $titles_options['separator'] ];
	}

	/**
	 * Filter: 'wpseo_replacements_filter_sep' - Allow customization of the separator character(s)
	 *
	 * @api string $replacement The current separator
	 */

	return apply_filters( 'wpseo_replacements_filter_sep', $replacement );
}
