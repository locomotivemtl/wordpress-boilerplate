<?php

/**
 * File: WordPress Query Utilities
 *
 * @package Boilerplate\Utilities
 */



/**
 * Is the current post the first in The Loop?
 *
 * @return bool
 */

function boilerplate_is_the_post_first()
{
    global $wp_query;

    return ( $wp_query->current_post < 1 );
}



/**
 * Is the current post the last in The Loop?
 *
 * @return bool
 */

function boilerplate_is_the_post_last()
{
    global $wp_query;

    return ( $wp_query->current_post + 1 == $wp_query->post_count && $wp_query->post_count > 0 );
}



/**
 * Set up the next post and iterate current post index.
 *
 * Can't name the function `next_post()` because of an existing
 * function, since (0.71), that is now deprecated (2.0.0).
 *
 * @uses WP_Query::next_post()
 *
 * @return WP_Post Next post.
 */

function boilerplate_next_post()
{
    global $wp_query;

    return $wp_query->next_post();
}



/**
 * Does The Loop have a next posts page?
 *
 * @param int $max_page Optional. Max pages.
 *
 * @return bool
 */

function boilerplate_has_next_posts( $max_page = 0 )
{
    global $paged, $wp_query;

    if ( ! $max_page ) {
        $max_page = $wp_query->max_num_pages;
    }

    if ( ! $paged ) {
        $paged = 1;
    }

    $nextpage = ( intval( $paged ) + 1 );

    return ( ! is_single() && ( $nextpage <= $max_page ) );
}



/**
 * Load the page content view based on the currently-queried object.
 */

function boilerplate_the_queried_page()
{
    $queried_object = get_queried_object();

    if ( $queried_object instanceof WP_Post ) {
        if ( 'page' === $queried_object->post_type && ! empty( $queried_object->post_content ) ) {
            setup_postdata( $queried_object );

            boilerplate_get_template_view( 'content', 'page' );

            wp_reset_postdata();
        }
    }
}




if ( ! function_exists('get_post_progenitor') ) {

    /**
     * Retrieve progenitor of a post.
     *
     * @see get_post_ancestors()
     *
     * @param int|WP_Post $post Post ID or post object.
     * @return int|bool The first ancestor's ID or false if none are found.
     */

    function get_post_progenitor( $post )
    {
        $post = get_post( $post );

        if ( $post && ! empty( $post->post_parent ) && $post->post_parent != $post->ID ) {
            $ancestors = get_post_ancestors( $post );

            if ( count( $ancestors ) ) {
                return end( $ancestors );
            }
        }

        return false;
    }

}
