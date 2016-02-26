<?php

/**
 * Gravity Forms
 *
 * @package Boilerplate\Includes
 */

add_filter('gform_init_scripts_footer', '__return_true');

function wrap_gform_cdata_open($content = '')
{
	$content = 'document.addEventListener( "DOMContentLoaded", function() { ';
	return $content;
}
add_filter('gform_cdata_open', 'wrap_gform_cdata_open');

function wrap_gform_cdata_close($content = '')
{
	$content = ' }, false );';
	return $content;
}
add_filter('gform_cdata_close', 'wrap_gform_cdata_close');