<?php

/**
 * @wordpress-plugin
 *
 * Plugin Name:  Ecocide
 * Plugin URI:   https://github.com/mcaskill/wp-ecocide
 * Description:  A collection of modules to clean up or disable features in WordPress.
 * Version:      1.0.0-d0966c5
 * Author:       Chauncey McAskill
 * Author URI:   https://mcaskill.ca
 * License:      MIT License
 */

if ( ! is_blog_installed() ) {
    return;
}

if ( ! class_exists( 'Ecocide\\Modules' ) ) {
    $vendor_autoload_path = __DIR__ . '/vendor/autoload.php';
    if ( file_exists( $vendor_autoload_path ) ) {
        // phpcs:ignore WPThemeReview.CoreFunctionality.FileInclude.FileIncludeFound
        require_once $vendor_autoload_path;
    } else {
        wp_die( __( 'The Composer autoloader could not be found.' ) );
    }
}

$ecocide = new Ecocide\Modules();

$ecocide->get('disable-attachment-template')->boot();
$ecocide->get('disable-author-template')->boot();
$ecocide->get('disable-comments')->boot();
$ecocide->get('disable-customizer')->boot();
$ecocide->get('disable-emoji')->boot();
$ecocide->get('disable-post')->boot();
$ecocide->get('disable-post-category')->boot();
$ecocide->get('disable-post-format')->boot();
$ecocide->get('disable-post-tag')->boot();
$ecocide->get('disable-search')->boot();
$ecocide->get('disable-xml-rpc')->boot();
