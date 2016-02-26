<?php

/**
 * File: Theme Constants
 *
 * @package Boilerplate\Includes
 */

namespace Boilerplate\Init;

/**
 * @const int     POST_EXCERPT_LENGTH  Excerpt length; filters in {@see <theme>/includes/excerpts.php}.
 * @const string  ENUM                 Used to provide a localized enumeration symbol
 * @const string  SEP                  Used to provide a localized separation symbol
 */

if ( ! defined('POST_EXCERPT_LENGTH') ) {
	define( 'POST_EXCERPT_LENGTH', 45 );
}

if ( ! defined('ENUM') ) {
	define( 'ENUM', _x(': ', 'Used to explain or start an enumeration', 'boilerplate') );
}

if ( ! defined('SEP') ) {
	define( 'SEP', _x(', ', 'Used to separate items in a list', 'boilerplate') );
}
