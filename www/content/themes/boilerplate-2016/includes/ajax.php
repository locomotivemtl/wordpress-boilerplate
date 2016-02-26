<?php

/**
 * AJAX calls
 *
 * @package Boilerplate\Includes
 */


function boilerplate_load_something() {

    //boilerplate_get_template_view('something');

    die();
}
add_action( 'wp_ajax_nopriv_boilerplate_load_something', 'boilerplate_load_something' );
add_action( 'wp_ajax_boilerplate_load_something', 'boilerplate_load_something' );