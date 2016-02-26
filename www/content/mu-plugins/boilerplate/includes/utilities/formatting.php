<?php

/**
 * File: Formatting Utilities
 *
 * @package Boilerplate\Utilities
 */

/**
 * Quick Line Breaks & Tab Characters
 *
 * @const string N Line Feed
 * @const string R Carriage Return
 * @const string T Horizontal Tab
 */

if ( ! defined('N') ) define( 'N', ( defined('LF') ? LF : "\n" ) );
if ( ! defined('R') ) define( 'R', ( defined('CR') ? CR : "\r" ) );
if ( ! defined('T') ) define( 'T', ( defined('HT') ? HT : "\t" ) );



if ( ! function_exists( 'leadingslashit' ) ) :

    /**
     * Prepends a leading slash.
     *
     * Will remove leading forward and backslashes if it exists already before adding
     * a leading forward slash. This prevents double slashing a string or path.
     *
     * The primary use of this is for paths and thus should be used for paths. It is
     * not restricted to paths and offers no specific path support.
     *
     * Opposite of {@see WordPress\trailingslashit()}.
     *
     * @param string $string What to add the leading slash to.
     * @return string String with leading slash added.
     */

    function leadingslashit( $string )
    {
        return '/' . unleadingslashit( $string );
    }

endif;



if ( ! function_exists( 'unleadingslashit' ) ) :

    /**
     * Removes leading forward slashes and backslashes if they exist.
     *
     * The primary use of this is for paths and thus should be used for paths. It is
     * not restricted to paths and offers no specific path support.
     *
     * Opposite of {@see WordPress\untrailingslashit()}.
     *
     * @param string $string What to remove the leading slashes from.
     * @return string String without the leading slashes.
     */

    function unleadingslashit( $string )
    {
        return ltrim( $string, '/\\' );
    }

endif;




/**
 * Retrieve the label for the current query variable from the main query.
 *
 * @param string $var
 *
 * @return string
 */

function boilerplate_get_query_label( $var )
{
    $value = strtoupper( get_query_var( $var ) );
    $label = strtoupper( $var );
    $const = "BOILERPLATE_{$label}_{$value}_LABEL";

    if ( defined( $const ) ) {
        return constant( $const );
    }

    return '';
}

/**
 * Wrap an abbreviation, and its description, in a <abbr> HTML element.
 *
 * @param string $abbreviation
 * @param string $description
 *
 * @return string
 */

function abbr( $abbreviation, $description )
{
    return sprintf(
        '<abbr title="%2$s">%1$s</abbr>',
        $abbreviation,
        esc_attr( $description )
    );
}
