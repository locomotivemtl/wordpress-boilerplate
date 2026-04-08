<?php

/**
 * Hooks and side effects to customize the theme and site.
 */

/**
 * Configures the theme's defaults and registers support
 * for various WordPress features.
 */
add_action( 'after_setup_theme', function () : void {
    if ( class_exists( 'Ecocide\Modules' ) ) {
        $ecocide = new Ecocide\Modules();

        $ecocide->get( 'disable-post-format' )->boot();
        $ecocide->get( 'disable-post-tag' )->boot();

        /**
         * Customizes the REST URL to test 'page' post type for REST API availability.
         */
        add_filter(
            'ecocide/modules/disable_post/test_rest_availability_url',
            fn ( string $url ) : string => str_replace( '/wp/v2/types/post', '/wp/v2/types/page', $url )
        );
    }

    /**
     * Outputs HTML5 markup for core features.
     *
     * @link https://codex.wordpress.org/Theme_Markup
     */
    add_theme_support( 'html5', [
        'caption',
        'comment-form',
        'comment-list',
        'gallery',
        'script',
        'search-form',
        'style',
    ] );

    /**
     * Automatically add the `<title>` tag.
     *
     * @link https://developer.wordpress.org/reference/functions/add_theme_support/#title-tag
     */
    add_theme_support( 'title-tag' );

    /**
     * Automatically add the menu in Appearence section.
     *
     * @link https://developer.wordpress.org/reference/functions/add_theme_support/#menus
     */
    add_theme_support( 'menus' );

    /**
     * Automatically add feed links to `<head>`.
     */
    add_theme_support( 'automatic-feed-links' );

    /**
     * Adds featured image support.
     *
     * @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
     */
    add_theme_support( 'post-thumbnails' );
} );

/**
 * Cleans-up WordPress theme output and registers support
 * for various custom features.
 */
add_action( 'init', function () : void {
    /** Disables output of relational links for the posts adjacent to the current post for single post pages. */
    remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head', 10 );

    /** Disables output of the links to the feeds. */
    remove_action( 'wp_head', 'feed_links', 2 );
    remove_action( 'wp_head', 'feed_links_extra', 3 );

    /** Disables output of the REST API discovery link in the Web site `<head>`. */
    remove_action( 'wp_head', 'rest_output_link_wp_head', 10 );

    /** Disables output of the link to the Really Simple Discovery service endpoint, EditURI link. */
    remove_action( 'wp_head', 'rsd_link' );

    /** Disables output of the link to the Windows Live Writer manifest file. */
    remove_action( 'wp_head', 'wlwmanifest_link' );

    /** Disables output of the XHTML generator for WordPress. */
    remove_action( 'wp_head', 'wp_generator' );

    /** Disables output of the oEmbed discovery links in the Web site `<head>`. */
    remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );

    /** Disables output of the JavaScript to communicate with the embedded iframes. */
    remove_action( 'wp_head', 'wp_oembed_add_host_js' );

    /** Disables output of shortlink if a shortlink is defined for the current page. */
    remove_action( 'wp_head', 'wp_shortlink_wp_head', 10 );

    /** Disables output of the WordPress site icon meta tags. */
    remove_action( 'wp_head', 'wp_site_icon', 99 );

    /** Disables the output of the Recent Comments default widget styles. */
    add_filter( 'show_recent_comments_widget_style', '__return_false' );

    /** Disables output of the XHTML generator for WordPress in RSS feeds. */
    add_filter( 'the_generator', '__return_null' );

    /** Disables the output of the default gallery styles. */
    add_filter( 'use_default_gallery_style', '__return_false' );

    /** Disables output of CSS rule to fix potential visual issues with images using `sizes=auto`. */
    add_filter( 'wp_img_tag_add_auto_sizes', '__return_false' );
} );

/**
 * Trims outer whitespace.
 */
add_filter( 'the_content', function ( string $value ) : string {
    return trim( $value );
}, 20 );

/**
 * Disables core block styles.
 */
add_action( 'wp_enqueue_scripts', function () : void {
    wp_dequeue_style( 'classic-theme-styles' );
    wp_dequeue_style( 'global-styles' );
    wp_dequeue_style( 'wp-block-library' );
} );

/**
 * Adds support for the `target` attribute on the HTML `<a>` element.
 *
 * @param  array<string, mixed> $html Allowed HTML tags and attributes.
 * @return array<string, mixed>
 */
add_filter( 'wp_kses_allowed_html', function ( array $html ) : array {
    if ( isset( $html['a'] ) && ! isset( $html['a']['target'] ) ) {
        $html['a']['target'] = true;
    }

    return $html;
} );

/**
 * Replaces specific plugin's localizations.
 *
 * Replacements:
 * - ACF and ACF Extended's file path for `fr_CA` with `fr_FR` given the former is broken since 6.3.1.
 * - WP Statuses missing/unsupported `fr_CA` with `fr_FR`.
 *
 * @todo Remove this hack once the issue has been resolved. Report submitted to ACF: Ticket #7754098.
 */
add_filter( 'load_textdomain_mofile', function ( string $mofile, string $domain ) : string {
    if (
        in_array( $domain, [ 'acf', 'acfe', 'wp-statuses' ], true ) &&
        str_ends_with( $mofile, 'fr_CA.mo' )
    ) {
        return str_replace( 'fr_CA.mo', 'fr_FR.mo', $mofile );
    }

    return $mofile;
}, 10, 2 );

/**
 * Excludes certain post types from {@link https://wordpress.org/plugins/page-links-to/ Pages Link To} plugin.
 *
 * @param  string[] $post_types List of post type names.
 * @return string[]
 */
add_filter( 'page-links-to-post-types', function ( array $post_types ) : array {
    return array_diff( $post_types, [
        'acf-field',
        'acf-field-group',
        'acf-post-type',
        'acf-taxonomy',
        'acf-ui-options-page',
        'acfe-dop',
        'acfe-dpt',
        'acfe-dt',
        'post',
    ] );
} );

/**
 * Remove Slim SEO for all terms
 *
 * @param object<SlimSEO\Container> $plugin Slim SEO plugin container.
 */
add_action( 'slim_seo_init', function( $plugin ) : void {
    $plugin->disable( 'settings_term' );
} );

/**
 * Update title tag for archive pages (do not show the selected categories)
 */
add_filter( 'document_title_parts', function( $title_parts ) {
    if ( is_archive() ) {
        $title_parts['title'] = get_the_title(get_the_ID());
    }
    return $title_parts;
} );

/**
 * Removes the "Customize" menu item under the "Appearence" menu
 * and the "Tools" for non-administrators.
 */
add_action( 'admin_menu', function() : void {
    global $submenu;

    unset( $submenu['themes.php'][6] );

    if ( ! current_user_can( 'administrator' ) ) {
        remove_menu_page('tools.php');
    }
}, 999 );

/**
 * Removes all taxonomies selection from quick edits
 */
add_filter( 'quick_edit_show_taxonomy', '__return_false', 10 );
