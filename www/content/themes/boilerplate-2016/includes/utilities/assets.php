<?php

/**
 * File: Theme Script & Style Utilities
 *
 * @package Boilerplate\Utilities
 */

/**
 * Display the URL for an asset in the 'assets' directory
 * of the current theme/child theme.
 *
 * Returns the {@see get_stylesheet_directory_uri()} value with the
 * appropriate protocol, and path to the 'assets' directory.
 *
 * This function is based on {@see home_url()}.
 *
 * @param  string $path   Optional. Path relative to the current theme/child theme.
 * @param  string $scheme Optional. Scheme to give the home URL context. Accepts
 *                        'http', 'https', or 'relative'. Default null.
 * @return string Asset URL link with optional path to asset appended.
 */

function boilerplate_asset_url( $path, $scheme = null ) {
    echo boilerplate_get_asset_url( $path, $scheme );
}

/**
 * Retrieve the asset URL for a given site.
 *
 * Returns the {@see get_stylesheet_directory_uri()} value with the
 * appropriate protocol, and path to the 'assets' directory.
 *
 * This function is based on {@see get_home_url()}.
 *
 * @param  string      $path        Optional. Path relative to the home URL.
 * @param  string|null $orig_scheme Optional. Scheme to give the home URL context. Accepts
 *                                  'http', 'https', 'relative', or null. Default null.
 * @return string Home URL link with optional path appended.
 */

function boilerplate_get_asset_url( $path, $scheme = null ) {
    $orig_scheme = $scheme;

    $url = get_stylesheet_directory_uri();

    if ( ! in_array( $scheme, array( 'http', 'https', 'relative' ) ) ) {
        if ( is_ssl() && ! is_admin() && 'wp-login.php' !== $GLOBALS['pagenow'] )
            $scheme = 'https';
        else
            $scheme = parse_url( $url, PHP_URL_SCHEME );
    }

    $url = set_url_scheme( $url, $scheme );

    if ( $path && is_string( $path ) ) {
        $url .= '/' . 'assets/' . ltrim( $path, '/' );
    }

    /**
     * Filter the asset URL.
     *
     * @param string      $url         The complete home URL including scheme and path.
     * @param string      $path        Path relative to the home URL. Blank string if no path is specified.
     * @param string|null $orig_scheme Scheme to give the home URL context. Accepts 'http', 'https', 'relative' or null.
     */

    return apply_filters( 'boilerplate/asset_url', $url, $path, $orig_scheme );
}

/**
 * Display site icons and theme colors
 */

function boilerplate_device_assets()
{
?>
        <link rel="apple-touch-icon" sizes="57x57" href="<?php boilerplate_asset_url('images/icons/apple-touch-icon-57x57.png') ?>">
        <link rel="apple-touch-icon" sizes="60x60" href="<?php boilerplate_asset_url('images/icons/apple-touch-icon-60x60.png') ?>">
        <link rel="apple-touch-icon" sizes="72x72" href="<?php boilerplate_asset_url('images/icons/apple-touch-icon-72x72.png') ?>">
        <link rel="apple-touch-icon" sizes="76x76" href="<?php boilerplate_asset_url('images/icons/apple-touch-icon-76x76.png') ?>">
        <link rel="apple-touch-icon" sizes="114x114" href="<?php boilerplate_asset_url('images/icons/apple-touch-icon-114x114.png') ?>">
        <link rel="apple-touch-icon" sizes="120x120" href="<?php boilerplate_asset_url('images/icons/apple-touch-icon-120x120.png') ?>">
        <link rel="apple-touch-icon" sizes="144x144" href="<?php boilerplate_asset_url('images/icons/apple-touch-icon-144x144.png') ?>">
        <link rel="apple-touch-icon" sizes="152x152" href="<?php boilerplate_asset_url('images/icons/apple-touch-icon-152x152.png') ?>">
        <link rel="apple-touch-icon" sizes="180x180" href="<?php boilerplate_asset_url('images/icons/apple-touch-icon-180x180.png') ?>">
        <link rel="icon" type="image/png" href="<?php boilerplate_asset_url('images/icons/favicon-32x32.png') ?>" sizes="32x32">
        <link rel="icon" type="image/png" href="<?php boilerplate_asset_url('images/icons/android-chrome-192x192.png') ?>" sizes="192x192">
        <link rel="icon" type="image/png" href="<?php boilerplate_asset_url('images/icons/favicon-96x96.png') ?>" sizes="96x96">
        <link rel="icon" type="image/png" href="<?php boilerplate_asset_url('images/icons/favicon-16x16.png') ?>" sizes="16x16">
        <link rel="mask-icon" href="<?php boilerplate_asset_url('images/icons/safari-pinned-tab.svg') ?>" color="#5bbad5">
        <meta name="msapplication-TileColor" content="#da532c">
        <meta name="msapplication-TileImage" content="<?php boilerplate_asset_url('images/icons/mstile-144x144.png') ?>">
        <meta name="msapplication-config" content="<?php echo home_url('/browserconfig.xml'); ?>">
        <meta name="theme-color" content="#ffffff">

<?php
}
