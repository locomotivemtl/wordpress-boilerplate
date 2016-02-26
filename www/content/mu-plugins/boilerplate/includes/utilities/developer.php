<?php

/**
 * File: Developer Utilities
 *
 * @package Boilerplate\Utilities
 */

/**
 * Outputs information about a variable, wrapped in a HTML `<pre>` tag.
 *
 * `pre()` gets structured information about the given expressions.
 *
 * @uses var_dump()
 * @param mixed $expression,... $expression The variable you want to dump.
 */

function pre()
{
	$args = func_get_args();

	foreach ( $args as $arg_name => $arg_value ) {
		if ( is_array( $arg_value ) ) {
			array_walk_recursive( $arg_value, function( &$v ) {
				if ( is_string( $v ) ) {
					$v = htmlspecialchars( $v );
				}
			} );
		}

		echo '<pre id="var_dump-' . $arg_name . '">';
		var_dump( $arg_value );
		echo '</pre>';
	}
}
