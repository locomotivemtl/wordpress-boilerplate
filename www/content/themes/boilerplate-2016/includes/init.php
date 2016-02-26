<?php

/**
 * File: Theme Setup & Initialization
 *
 * @todo Rename 'boilerplate-news-header' image size  to 'boilerplate-singular-header'.
 *
 * @package Boilerplate\Includes
 */

namespace Boilerplate\Init;

use Boilerplate\Assets;

/**
 * Load template wrapper
 *
 * @see base.php
 */

add_filter( 'template_include', [ '\\Roots\\Sage\\Wrapper\\SageWrapping', 'wrap' ], 99 );
add_filter( 'sage/wrap_base', 'boilerplate_wrap_chromless_base' );


/**
 * Add page slug to body_class() classes if it doesn't exist.
 */

add_filter( 'body_class', 'boilerplate_body_class' );


/**
 * If the content is empty, display a message.
 */

# add_filter( 'the_content', 'boilerplate_the_content', 1 );



/**
 * Provide the URL to access WordPress' AJAX interface.
 */

# add_action( 'wp_head', 'boilerplate_wp_ajax' );


/**
 * Add site icons and theme colors
 *
 * @action 'login_head'
 * @action 'admin_head'
 * @action 'wp_head'
 */

add_action( 'login_head', 'boilerplate_device_assets' );
add_action( 'admin_head', 'boilerplate_device_assets' );
add_action( 'wp_head',    'boilerplate_device_assets' );


/**
 * Theme setup
 */

function setup()
{
    // Make theme available for translation
    load_theme_textdomain( 'boilerplate', get_template_directory() . '/assets/languages' );

    // Enable plugins to manage the document title
    // http://codex.wordpress.org/Function_Reference/add_theme_support#Title_Tag
    add_theme_support('title-tag');

    // Register wp_nav_menu() menus
    // http://codex.wordpress.org/Function_Reference/register_nav_menus
    register_nav_menus([
        'nav-primary' => _x('Primary Navigation', 'primary site nav menu', 'boilerplate' ),
        'nav-footer'  => _x('Footer Navigation', 'footer site nav menu', 'boilerplate' )
    ]);

    // Add post thumbnails
    // http://codex.wordpress.org/Post_Thumbnails
    // http://codex.wordpress.org/Function_Reference/set_post_thumbnail_size
    // http://codex.wordpress.org/Function_Reference/add_image_size
    add_theme_support('post-thumbnails');

    add_image_size('boilerplate-vignette',  300,  250,  true);

    // Add post formats
    // http://codex.wordpress.org/Post_Formats
    # add_theme_support('post-formats', ['aside', 'gallery', 'link', 'image', 'quote', 'video', 'audio']);

    // Add HTML5 markup for captions
    // http://codex.wordpress.org/Function_Reference/add_theme_support#HTML5
    add_theme_support( 'html5', ['caption', 'comment-form', 'comment-list'] );

    // Tell the TinyMCE editor to use a custom stylesheet
    # add_editor_style( 'assets/styles/dist/editor.css' );

}

add_action( 'after_setup_theme', __NAMESPACE__ . '\\setup' );
