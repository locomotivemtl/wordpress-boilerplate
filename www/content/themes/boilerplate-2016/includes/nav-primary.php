<?php

/**
 * Walker for "nav-primary" menu location
 *
 * @package Boilerplate\Includes
 */

class Boilerplate_Primary_Nav_Walker extends Walker_Nav_Menu
{
    public $tree_type = [ 'post_type', 'taxonomy', 'custom', 'boilerplate-archive' ];

    public $has_active_menu_item = null;

    public function start_lvl( &$output, $depth = 0, $args = [] )
    {
        $classes = [
            $args->menu_class . '_dropdown',
            'js-navMainDropdown'
        ];

        $indent  = str_repeat( T, $depth );
        $output .= N . $indent . '<div class="' . implode(' ', $classes) . '">' . N;
        $output .= N . $indent . $indent . '<ul class="' . $args->menu_class . '_dropdown_list' . '">' . N;
    }

    public function end_lvl( &$output, $depth = 0, $args = [] ) {
        $indent = str_repeat(T, $depth);
        $output .= N . $indent . $indent . '</ul>' . N;
        $output .= N . $indent . '</div>' . N;
    }

    public function start_el( &$output, $item, $depth = 0, $args = [], $id = 0 )
    {
        $item_html = '';


        parent::start_el( $item_html, $item, $depth, $args, $id );

        if ( $item->is_dropdown ) {
            //var_dump($item);
        /*
            $item_html = preg_replace('/<a([^>]*)>(.*)<\/a>/iU', '<a$1>$2</a>', $item_html);
            $item_html = preg_replace('/\s?href=".*?"/iU', '', $item_html);
        */
        }

        $output .= apply_filters( 'boilerplate/wp_nav_menu_item/start_el', $item_html, $item, $depth, $args );
    }

    public function display_element( $element, &$children_elements, $max_depth, $depth = 0, $args = [], &$output )
    {
        $element->is_dropdown = ( ( ! empty( $children_elements[ $element->ID ] ) && ( ($depth + 1) < $max_depth || ($max_depth === 0) ) ) );

        if ( ! is_object( $args[0] ) ) {
            $args[0] = (object) $args[0];
        }

        $args[0]->current_depth = $depth;
        $args[0]->max_depth     = $max_depth;

        $classes = $element->classes;

        //$classes[] = $args[0]->menu_class . '__depth--' . ( $depth + 1 );
        $classes[] = $args[0]->menu_class . '_item';

        if ( $element->is_dropdown ) {
            //$classes[] = 'nested';
        }

        $element->classes = $classes;

        parent::display_element( $element, $children_elements, $max_depth, $depth, $args, $output );
    }
}
