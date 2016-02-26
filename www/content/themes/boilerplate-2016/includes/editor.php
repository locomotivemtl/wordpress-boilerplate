<?php

/**
 * TinyMCE
 *
 * @package Boilerplate\Includes
 */

/**
 *
 */

function boilerplate_mce_init()
{
	// Check user permissions
	if ( ! current_user_can('edit_posts') && ! current_user_can('edit_pages') ) {
		return;
	}

	// Check if WYSIWYG is enabled
	if ( 'true' == get_user_option('rich_editing') ) {
		add_filter( 'mce_external_languages', 'boilerplate_mce_localize' );
	}
}

add_action('admin_head', 'boilerplate_mce_init');

/**
 * Filter the translations loaded for external TinyMCE 3.x plugins.
 *
 * The filter takes an associative array ('plugin_name' => 'path')
 * where 'path' is the include path to the file.
 *
 * The language file should follow the same format as wp_mce_translation(),
 * and should define a variable ($strings) that holds all translated strings.
 *
 * @param array $translations Translations for external TinyMCE plugins.
 */

function boilerplate_mce_localize( $locales = [] ) {
    $locales['simple-footnote'] = get_template_directory() . '/assets/languages/mce.footnotes.php';
	$locales['typekit'] = get_template_directory() . '/assets/languages/mce.typekit.php';

	return $locales;
}
