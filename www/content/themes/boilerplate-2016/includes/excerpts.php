<?php
/**
 * File: Excerpts & Summaries
 *
 * @package Boilerplate\Includes
 */

/**
 * Display the post summary.
 *
 * @uses Filters\the_excerpt
 */

function the_summary()
{
    /**
     * Filter the displayed post summary.
     *
     * @uses Filters\get_the_excerpt
     * @see  get_the_summary()
     *
     * @param string $post_excerpt The post excerpt.
     */

    echo apply_filters( 'the_excerpt', get_the_summary() );
}

/**
 * Retrieve the post summary.
 *
 * @uses ACF\get_field()
 * @uses Boilerplate\excerpt_or_summary
 *
 * @return string
 */

function get_the_summary()
{
    $post = get_post();
    if ( empty( $post ) ) {
        return '';
    }

    /**
     * Filter the retrieved post summary.
     *
     * @param string $post_excerpt The post excerpt.
     */

    return apply_filters( 'get_the_excerpt', excerpt_or_summary( $post->ID ) );
}

/**
 * Whether post has summary.
 *
 * @uses   ACF\get_field()
 * @uses   Boilerplate\excerpt_or_summary
 *
 * @param  int|WP_Post $id Optional. Post ID or post object.
 * @return bool
 */

function has_summary( $id = 0 )
{
    $post = get_post( $id );
    $summary = excerpt_or_summary( $post->ID );
    return ! empty( $summary );
}

/**
 * Retrieve the post "excerpt" or "top_text".
 *
 * @uses   ACF\get_field()
 * @uses   WordPress\get_bloginfo()
 * @uses   $post->post_excerpt
 *
 * @param  int|object  $post  The ID of the post you'd like to fetch, or an object that specifies the post. By default the current post is fetched.
 * @return string
 */

function excerpt_or_summary( $post = null )
{
    $post = get_post( $post );

    if ( empty( $post ) ) {
        return '';
    }

    if ( is_front_page() ) {
        $summary = get_bloginfo('description');

        if ( __('Just another WordPress site') === $summary ) {
            $summary = '';
        }
    }

    if ( empty( $summary ) ) {
        $summary = get_field( 'top_text', $post->ID );
    }

    if ( empty( $summary ) ) {
        $summary = $post->post_excerpt;
    }

    return $summary;
}

if ( ! is_admin() ) :

    /**
     * Replaces "[...]" (appended to automatically generated excerpts)
     * with "..." and a "Continued" link.
     *
     * @param string $more_string The string shown within the more link.
     */

    function boilerplate_excerpt_more( $more )
    {
        $link = '';
        /*
        $link = sprintf(
            '<a href="%1$s" class="more-link">%2$s</a>',
            esc_url( get_permalink( get_the_ID() ) ),
            __( 'Continued', 'boilerplate' )
        );
        */

        return '&hellip; ' . $link;
    }

    add_filter( 'excerpt_more', 'boilerplate_excerpt_more' );

endif;

/**
 * Filter the number of words in an excerpt based
 * on the value of {@const POST_EXCERPT_LENGTH}.
 *
 * @param int $number The number of words. Default 55.
 */

function boilerplate_excerpt_length( $length )
{
    return ( defined('POST_EXCERPT_LENGTH') ? POST_EXCERPT_LENGTH : $length );
}

remove_filter( 'the_excerpt', 'wpautop' );
add_filter( 'excerpt_length', 'boilerplate_excerpt_length' );
