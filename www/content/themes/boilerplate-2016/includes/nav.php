<?php

/**
 * Theme navigation functions
 *
 * @package Boilerplate\Includes
 */

/**
 * Cleaner walker for wp_nav_menu()
 *
 * Walker_Nav_Menu (WordPress default) example output:
 *   <li id="menu-item-8" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-8"><a href="/">Home</a></li>
 *   <li id="menu-item-9" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-9"><a href="/sample-page/">Sample Page</a></l
 *
 * Roots_Nav_Walker example output:
 *   <li class="menu-home"><a href="/">Home</a></li>
 *   <li class="menu-sample-page"><a href="/sample-page/">Sample Page</a></li>
 *
 * @package Roots
 */

class Roots_Nav_Walker extends Walker_Nav_Menu
{
    public $tree_type = [ 'post_type', 'taxonomy', 'custom', 'boilerplate-archive' ];

    public function start_lvl( &$output, $depth = 0, $args = [] )
    {
        $output .= N . '<ul class="dropdown-menu">' . N;
    }

    public function start_el( &$output, $item, $depth = 0, $args = [], $id = 0 )
    {
        $item_html = '';

        parent::start_el( $item_html, $item, $depth, $args, $id );

        if ($item->is_dropdown && ($depth === 0)) {
            $item_html = str_replace('<a', '<a class="dropdown-toggle" data-toggle="dropdown" data-target="#"', $item_html);
            $item_html = str_replace('</a>', ' <b class="caret"></b></a>', $item_html);
        }
        elseif (stristr($item_html, 'li class="divider')) {
            $item_html = preg_replace('/<a[^>]*>.*?<\/a>/iU', '', $item_html);
        }
        elseif (stristr($item_html, 'li class="dropdown-header')) {
            $item_html = preg_replace('/<a[^>]*>(.*)<\/a>/iU', '$1', $item_html);
        }

        $item_html = apply_filters('roots/wp_nav_menu_item', $item_html);
        $output .= $item_html;
    }

    public function display_element( $element, &$children_elements, $max_depth, $depth = 0, $args, &$output )
    {
        $element->is_dropdown = ( ( ! empty( $children_elements[ $element->ID ] ) && ( ($depth + 1) < $max_depth || ($max_depth === 0) ) ) );

        if ( $element->is_dropdown ) {
            $element->classes[] = 'dropdown';
        }

        parent::display_element( $element, $children_elements, $max_depth, $depth, $args, $output );
    }
}



/**
 * Remove the id="" on nav menu items
 * Return 'menu-slug' for nav menu classes
 */

function roots_nav_menu_css_class( $classes, $item, $args )
{
    $classes = preg_replace('/^((menu|page)[-_\w+]+)+/', '', $classes);

    if ( isset( $item->title ) ) {
        $slug = sanitize_title( $item->title );

        if ( empty( $slug ) && ! empty( $item->post_name ) && ! is_numeric( $item->post_name ) ) {
            $slug = $item->post_name;
        }

        if ( ! empty( $slug ) ) {
            $classes[] = ( isset( $args->item_separator ) ? 'crumb' : 'menu' ) . '-' . $slug;
        }
    }

    $classes = array_unique( $classes );

    return array_filter( $classes, 'is_empty' );
}

add_filter('nav_menu_css_class', 'roots_nav_menu_css_class', 10, 3);
add_filter('nav_menu_item_id',   '__return_null');

add_filter('nav_trail_css_class', 'roots_nav_menu_css_class', 10, 3);
add_filter('nav_trail_item_id',   '__return_null');



/**
 * Clean up wp_nav_menu_args
 *
 * Remove the container
 * Use Roots_Nav_Walker() by default
 */
function roots_nav_menu_args( $args = [] )
{
    $args['container'] = false;

    if ( 'nav-primary' === $args['theme_location'] && class_exists('Boilerplate_Primary_Nav_Walker') ) {

        $args['walker'] = new Boilerplate_Primary_Nav_Walker();

    }
    else if ( 'nav-footer' === $args['theme_location'] && class_exists('Boilerplate_Primary_Nav_Walker') ) {

        // $args['items_wrap'] = '%3$s';
        // $args['items_wrap'] = '<ul class="%2$s">' . . '%3$s' . . '</ul>';

        $args['walker'] = new Boilerplate_Primary_Nav_Walker();

    }
    else {

        $args['walker'] = new Roots_Nav_Walker();

    }

    if ( ! $args['items_wrap'] ) {
        $args['items_wrap'] = '<ul class="%2$s">%3$s</ul>';
    }

    if ( ! $args['depth'] ) {
        $args['depth'] = 2;
    }

    if ( ! $args['walker'] ) {
        $args['walker'] = new Roots_Nav_Walker();
    }

    return $args;
}

add_filter('wp_nav_menu_args', 'roots_nav_menu_args');

/**
 * Filter the HTML attributes applied to a menu item's <a>.
 *
 * @see {method} WordPress\Walker_Nav_Menu\start_el()
 *
 * @param array $atts {
 *     The HTML attributes applied to the menu item's <a>, empty strings are ignored.
 *
 *     @type string $title  Title attribute.
 *     @type string $target Target attribute.
 *     @type string $rel    The rel attribute.
 *     @type string $href   The href attribute.
 * }
 * @param object $item The current menu item.
 * @param array  $args An array of wp_nav_menu() arguments.
 */

function boilerplate_nav_menu_link_attributes( $attributes, $item, $args )
{

    if ('nav-primary' === $args->theme_location) {

        if ( ! isset( $attributes['class'] ) ) {
            $attributes['class'] = [];
        }

        $attributes['class'][] = $args->menu_class . '_link';

        if ($item->is_dropdown) {
            //unset( $attributes['target'], $attributes['rel'] , $attributes['href'] );
            $attributes['class'][] = '-dropdown';
            $attributes['class'][] = 'js-navMainLink';
        }

        if( ! $args->walker->has_active_menu_item ){

            if ( in_array( ( $post_type = get_post_type() ) ? $post_type : 'nothing' , $item->classes ) ){

                $attributes['class'][] = 'is-current';

            // Had a lot of issues with Wordpress' page_for_posts option, flagging custom post types as being the blog page
            } elseif ( get_post_type() === 'page' &&  in_array( ( $template_slug = basename( get_page_template_slug(), '.php' ) ) ? $template_slug : 'nothing' , $item->classes ) ) {

                $attributes['class'][] = 'is-current';

            // Had a lot of issues with Wordpress' page_for_posts option, flagging custom post types as being the blog page
            } elseif ( preg_grep('/(current(-menu-|[-_]page[-_])(item|parent|ancestor))/', $item->classes) && ! boilerplate_is_blog() ){

                $attributes['class'][] = 'is-current';

                $args->walker->has_active_menu_item = true;

            }elseif( boilerplate_is_blog() ){

                $blog_page_id = intval( get_option('page_for_posts') );

                if( $blog_page_id != 0 && $blog_page_id == $item->object_id){

                    $attributes['class'][] = 'is-current';

                }

            }

        }

        /**
         * Filter the CSS class(es) applied to a menu item's <a>.
         *
         * @param array  $classes The CSS classes that are applied to the menu item's <A>.
         * @param object $item    The current menu item.
         * @param array  $args    An array of wp_nav_menu() arguments.
         */
        $attributes['class'] = implode( ' ', apply_filters( 'boilerplate/nav_menu_link_css_class', array_filter( $attributes['class'] ), $item, $args ) );
    }

    /**
     * Match the "search" special keyword and replace it with the URL
     */
    global $wp_rewrite;

    if ( isset( $attributes['href'] ) && $attributes['href'] === $wp_rewrite->search_base ) {
        $attributes['href'] = get_search_link();
    }

    return $attributes;
}

add_filter('nav_menu_link_attributes', 'boilerplate_nav_menu_link_attributes', 10, 3);
