<?php

/**
 * Polylang Utility Functions
 *
 * @package WordPress\Skeleton
 */



if ( ! function_exists('pll_is_not_translated_post_type') )
{

	/**
	 * Is the post type NOT translated by Polylang?
	 *
	 * @see Boilerplate\pll_is_translated_post_type() Opposite of
	 *
	 * @param string|string[] $post_type Post type name or array of post type names.
	 *
	 * @return bool
	 */

	function pll_is_not_translated_post_type( $post_type )
	{
		global $polylang;

		if ( isset( $polylang ) ) {
			$pll_post_types = $polylang->model->get_translated_post_types( false );

			return ( is_array( $post_type ) && array_diff( $post_type, $pll_post_types ) || in_array( $post_type, $pll_post_types ) );
		}

		return false;
	}

}

/**
 * Remove Polylang's language query var and tax query.
 *
 * @param WP_Query &$query The WP_Query instance to target.
 */

function pll_remove_language_query_var( &$query )
{
	$qv = &$query->query_vars;

	unset( $qv['lang'] );

	if ( ! empty( $qv['tax_query'] ) ) {
		foreach ( $qv['tax_query'] as $i => $tax_query ) {
			if ( isset( $tax_query['taxonomy'] ) && 'language' === $tax_query['taxonomy'] ) {
				unset( $qv['tax_query'][ $i ] );
			}
		}
	}
}



/**
 * Retrieve anything up to the start of the first tag in your $permalink_structure.
 *
 * This will provide a Polylang-influenced $front.
 *
 * @return  string  $front  The translation-affixed WP_Rewrite $front.
 *
 * @package WordPress\Skeleton\Polylang
 * @see     WordPress\WP_Rewrite::$front
 * @see     http://codex.wordpress.org/Class_Reference/WP_Rewrite
 */

function pll_get_rewrite_front()
{
	global $polylang, $wp_rewrite;

	$front = '';

	$languages = $polylang->model->get_languages_list([ 'fields' => 'slug' ]);
	if ( $polylang->options['hide_default'] ) {
		$languages = array_diff( $languages, [ $polylang->options['default_lang'] ]);
	}

	if ( ! empty( $languages ) ) {
		$front = ltrim( $wp_rewrite->front, '/' );
		$front = ( ! empty( $front ) && 0 === strpos( key( $rules ), $front ) ? $front : '' ); // does this set of rules uses front?
		$front = $wp_rewrite->root . $front;
		$front = ( $polylang->options['rewrite'] ? '' : 'language/' ) . '(' . implode( '|', $languages ) . ')/';
	}

	return $front;
}



/**
 * Retrieve the translations of $text in the given language(s) or all available languages.
 *
 * @param   string  $text      Text to translate.
 * @param   string  $domain    Optional. Text domain. Unique identifier for retrieving translated strings.
 * @param   mixed   $language  Optional. Unsupported. Given language. Default is to retrive all available translations.
 * @return  array              Translations without pipe.
 *
 * @package WordPress\Skeleton\Polylang
 * @see     WordPress\__
 */

function pll_translations__( $text, $domain = 'default', $language = -1 )
{
	return pll_get_string_translations( $text, null, null, null, $domain, $language );
}



/**
 * Retrieve the plural or single form translations based on the supplied amount
 * in the given language(s) or all available languages.
 *
 * @param   string  $single    The text that will be used if $number is 1.
 * @param   string  $plural    The text that will be used if $number is not 1.
 * @param   int     $number    The number to compare against to use either $single or $plural.
 * @param   string  $domain    Optional. Text domain. Unique identifier for retrieving translated strings.
 * @param   mixed   $language  Optional. Unsupported. Given language. Default is to retrive all available translations.
 * @return  array              Translations without pipe.
 *
 * @package WordPress\Skeleton\Polylang
 * @see     WordPress\_n
 */

function pll_translations_n( $single, $plural, $number, $domain = 'default', $language = -1 )
{
	return pll_get_string_translations( $single, $plural, $number, null, $domain, $language );
}



/**
 * This is a hybrid of pll_translations_n() and pll_translations_x().
 * It supports contexts and plurals.
 *
 * @param   string  $single    The text that will be used if $number is 1.
 * @param   string  $plural    The text that will be used if $number is not 1.
 * @param   int     $number    The number to compare against to use either $single or $plural.
 * @param   string  $context   Context information for the translators.
 * @param   string  $domain    Optional. Text domain. Unique identifier for retrieving translated strings.
 * @param   mixed   $language  Optional. Unsupported. Given language. Default is to retrive all available translations.
 * @return  array              Translations without pipe.
 *
 * @package WordPress\Skeleton\Polylang
 * @see     WordPress\_n
 */

function pll_translations_nx( $single, $plural, $number, $context, $domain = 'default', $language = -1 )
{
	return pll_get_string_translations( $single, $plural, $number, $context, $domain, $language );
}



/**
 * Retrieve the translations of $text with gettext context in the given
 * language or all available languages.
 *
 * @param   string  $text      Text to translate.
 * @param   string  $context   Context information for the translators.
 * @param   string  $domain    Optional. Text domain. Unique identifier for retrieving translated strings.
 * @param   mixed   $language  Optional. Unsupported. Given language. Default is to retrive all available translations.
 * @return  array              Translations without pipe.
 *
 * @package WordPress\Skeleton\Polylang
 * @see     WordPress\_x
 */

function pll_translations_x( $text, $context, $domain = 'default', $language = -1 )
{
	return pll_get_string_translations( $text, null, null, $context, $domain, $language );
}



/**
 * Retrieve gettext translations in the given language(s) or all available languages.
 *
 * If there is no translation, or the text domain isn't loaded, the original text is returned.
 *
 * @param   string  $singular      Text to translate. The "single" form that will be used if $number is 1.
 * @param   string  $plural        The text that will be used if $number is not 1.
 * @param   int     $number        The number to compare against to use either $text or $plural.
 * @param   string  $context       Context information for the translators.
 * @param   string  $domain        Optional. Text domain. Unique identifier for retrieving translated strings.
 * @param   mixed   $language      Optional. Unsupported. Given language. Default is to retrive all available translations.
 * @return  array   $translations  Translations.
 *
 * @package WordPress\Skeleton\Polylang
 * @see     WordPress\_x
 * @todo    Add support for $language parameter.
 */

function pll_get_string_translations( $singular, $plural = null, $number = null, $context = null, $domain = 'default', $language = -1 )
{
	global $pll_domains, $pll_l10n;

	if ( empty( $pll_l10n[ $domain ] ) ) {
		$loaded = pll_load_textdomain( $domain );

		if ( ! $loaded ) {
			return false;
		}
	}

	$for_plural = ( ! empty( $plural ) && is_numeric( $number ) );

	$translations = [
		'en' => $singular
	];

	if ( isset( $pll_l10n[ $domain ] ) && ! empty( $pll_l10n[ $domain ] ) ) {
		foreach ( $pll_l10n[ $domain ] as $lang => $mo ) {
			if ( $language !== -1 && $language !== $lang ) {
				continue;
			}

			if ( $for_plural ) {
				$translations[ $lang ] = $mo->translate_plural( $singular, $plural, $number, $context );
			}
			else {
				$translations[ $lang ] = $mo->translate( $singular, $context );
			}
		}
	}

	if ( empty( $context ) ) {
		if ( $for_plural ) {
			$translations = apply_filters( 'pll_ngettext', $translations, $singular, $plural, $number, $domain );
		}
		else {
			$translations = apply_filters( 'pll_gettext', $translations, $singular, $domain );
		}
	}
	else {
		if ( $for_plural ) {
			$translations = apply_filters( 'pll_ngettext_with_context', $translations, $singular, $plural, $number, $context, $domain );
		}
		else {
			$translations = apply_filters( 'pll_gettext_with_context', $translations, $singular, $context, $domain );
		}
	}

	if ( is_string( $language ) && isset( $translations[ $language ] ) ) {
		return $translations[ $language ];
	}

	return $translations;
}



if ( ! function_exists('pll_get_post_translations') )
{

/**
 * Retrive list of translated objects given a post or term ID or object.
 *
 * @param   string       $type          Post type or taxonomy.
 * @param   int|WP_Post  $id            Optional. Post or Term ID or object. Default current post.
 * @param   bool         $add_self      Optional. If no translations, return self. Default is "false".
 * @return  array        $translations  List of translated posts or terms.
 *
 * @package WordPress\Skeleton\Polylang
 */

	function pll_get_translations( $type, $id, $add_self = false )
	{
		global $polylang;

		if ( ! is_object( $polylang ) ) {
			return false;
		}

		$translations = $polylang->model->get_translations( $type, $id );

		if ( empty( $translations ) && $add_self ) {
			if ( $type == 'post' || $polylang->is_translated_post_type( $type ) ) {
				$translations = [ ( pll_get_post_language( $id ) ) => $id ];
			}
			elseif ( $type == 'term' || $polylang->is_translated_taxonomy( $type ) ) {
				$translations = [ ( pll_get_term_language( $id ) ) => $id ];
			}
		}

		if ( is_array( $translations ) && ! empty( $translations ) ) {
			foreach ( $translations as $language => $object_id ) {
				$translations[ $language ] = ( is_tax( $type ) ? get_term( $object_id, $type ) : get_post( $object_id ) );
			}
		}

		return $translations;
	}
}



/**
 * Load a collection of `.mo` files into the text domain $domain.
 *
 * If the text domain already exists, the translations will be merged. If both
 * sets have the same string, the translation from the original value will be taken.
 *
 * On success, the `.mo` files are placed in the $pll_l10n global by $domain
 * and will be a MO object.
 *
 * @param   string  $domain  Text domain. Unique identifier for retrieving translated strings.
 * @return  bool             True on success, false on failure.
 *
 * @package WordPress\Skeleton\Polylang
 * @see     WordPress\load_textdomain
 */

function pll_load_textdomain( $domain )
{
	global $polylang, $pll_domains, $pll_l10n;

	if ( ! is_object( $polylang ) ) {
		return false;
	}

	if ( empty( $pll_domains[ $domain ] ) ) {
		return false;
	}

	$pll_domain = $pll_domains[ $domain ];

	$languages_list = $polylang->model->get_languages_list();

	foreach ( $languages_list as $language ) {
		$locale = $language->locale;

		$mo = new MO();

		foreach ( $pll_domain as $dirname => $basename ) {
			$mofile = $dirname . '/' . sprintf( $basename, $locale );

			if ( ! is_readable( $mofile ) ) {
				continue;
			}

			if ( ! $mo->import_from_file( $mofile ) ) {
				continue;
			}

			if ( isset( $pll_l10n[ $domain ][ $language->slug ] ) ) {
				$mo->merge_with( $pll_l10n[ $domain ][ $language->slug ] );
			}

			$pll_l10n[ $domain ][ $language->slug ] = $mo;
		}
	}

	return true;
}



/**
 * Track text domains and MO translation paths.
 *
 * Traditionally, WordPress merges existing text domains and does not keep
 * inventory of where each translation file comes from. This can be inconvenient
 * if ever we need to load translations from multiple languages at once.
 *
 * @param  string  $domain  Text domain. Unique identifier for retrieving translated strings.
 * @param  string  $mofile  Path to the `.mo` file.
 *
 * @package WordPress\Skeleton\Polylang
 */

function pll_track_textdomains( $domain, $mofile )
{
	global $pll_domains;
/*
	if ( ! is_readable( $mofile ) ) {
		return false;
	}
*/
	if ( empty( $pll_domains ) ) {
		$pll_domains = [];
	}

	$path = pathinfo( $mofile );
	$file = str_replace( get_locale(), '%s', $path['basename'] );

	$pll_domains[ $domain ][ $path['dirname'] ] = $file;
}

add_action( 'load_textdomain', 'pll_track_textdomains', 0, 2 );



/**
 * Modify Polylang's "%language%" rewrite tag
 *
 * Modify the rewrite tag to use an imploded list of available languages.
 * This should be more reliable than a generic non-hierarchical regular expression.
 *
 * @param string        $taxonomy     Taxonomy slug.
 * @param array|string  $object_type  Object type or array of object types.
 * @param array         $args         Array of taxonomy registration arguments.
 *
 * @package WordPress\Skeleton\Polylang
 * @todo    Test if this expression is better for $regex: `(?:(fr|en)/)?`
 * @note    WP_Rewrite strips `(` and `)` when replacing tags.
 *          This prevents imploding languages here a viable option.
 */
/*
function pll_rewrite_language_tag( $taxonomy, $object_type, $args = [] )
{
	global $polylang;

	if ( is_object( $polylang ) && 'language' === $taxonomy ) {
		$tag   = "%$taxonomy%";
		# $regex = '(' . implode( '|', $polylang->model->get_languages_list([ 'fields' => 'slug' ]) ) . ')';
		$regex = '(\w{2})';
		$query = ( empty( $args['query_var'] ) ? 'taxonomy=' . $taxonomy . '&term=' : $args['query_var'] . '=' );

		// pre( $tag, $regex, $query );

		add_rewrite_tag( $tag, $regex, $query );
	}
}

add_action( 'registered_taxonomy', 'pll_rewrite_language_tag', 1, 3 );
*/
