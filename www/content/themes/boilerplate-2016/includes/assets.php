<?php

/**
 * File: Scripts & Stylesheets
 *
 * Global:
 *
 * 1. Register your assets as early as possible, such as on the "init" action.
 *
 * Front-end:
 *
 * 1. Enqueue your assets on the "wp_enqueue_scripts" action.
 *
 * WordPress Admin:
 *
 * 1. Register your assets on the "admin_init" action.
 * 2. Enqueue your assets on the "admin_enqueue_scripts" action.
 *
 * Gravity Forms:
 *
 * 1. Enqueue your assets on the "gform_enqueue_scripts" action.
 *
 * @package Boilerplate\Includes
 */

namespace Boilerplate\Assets;

add_action( 'init',                  __NAMESPACE__ . '\\register_assets' );
add_action( 'admin_init',            __NAMESPACE__ . '\\register_admin_assets' );

add_action( 'wp_enqueue_scripts',    __NAMESPACE__ . '\\enqueue_assets', 11 );
add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\\enqueue_admin_assets', 11 );
add_action( 'gform_enqueue_scripts', __NAMESPACE__ . '\\enqueue_gform_assets', 11 );

/**
 * Register Assets for front-end
 *
 * @used-by  Actions: WordPress\'init'
 */

function register_assets()
{
    wp_register_style( 'google-fonts', 'https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,700' );
    wp_register_style( 'boilerplate-main', boilerplate_get_asset_url( 'styles/dist/main.css' ), ['google-fonts'], ASSET_VERSION, 'all' );

    wp_register_script( 'boilerplate-vendors',  boilerplate_get_asset_url( 'scripts/dist/vendors.js' ),  [ 'jquery' ], ASSET_VERSION, true );
    wp_register_script( 'boilerplate-app',      boilerplate_get_asset_url( 'scripts/dist/app.js' ),      [ 'boilerplate-vendors' ], ASSET_VERSION, true );
}

/**
 * Register Assets for WordPress Admin
 *
 * @used-by Action: WordPress\'admin_init'
 */

function register_admin_assets()
{
}

/**
 * Enqueue Assets for front-end
 *
 * 1. For debugging.
 *
 * @used-by Actions: WordPress\'wp_enqueue_scripts'
 */

function enqueue_assets()
{
    wp_enqueue_style( 'google-fonts' );
    wp_enqueue_style( 'boilerplate-main' );

    /** [1] */
    wp_add_inline_style( 'boilerplate-main', 'body > pre, main > pre { margin-top: 100px; padding: 2%; }' );

    wp_enqueue_script( 'jquery' );
    wp_enqueue_script( 'boilerplate-vendors' );
    wp_enqueue_script( 'boilerplate-app' );

    do_action( 'boilerplate_enqueue_assets' );
}

/**
 * Enqueue Assets for WordPress Admin
 *
 * @used-by Action: WordPress\'admin_enqueue_scripts'
 */

function enqueue_admin_assets()
{
}

/**
 * Enqueue Assets for Gravity Forms
 *
 * @used-by  Action: GravityForms\'gform_enqueue_scripts'
 * @param    array  $form
 * @param    bool   $is_ajax
 */

function enqueue_gform_assets( $form, $is_ajax = true )
{
}
