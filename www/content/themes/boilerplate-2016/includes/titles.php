<?php

/**
 * Page titles
 *
 * @package Boilerplate\Includes
 */

/**
 * Handle output of page title
 */

function boilerplate_title( $post = null )
{
    $queried_object = get_queried_object();

    if ( is_home() ) {
        if ( get_option('page_for_posts', true) ) {
            return get_the_title( get_option('page_for_posts', true) );
        }
        else {
            return __('Latest Posts', 'boilerplate');
        }
    } elseif ( is_archive() ) {
        $term = get_term_by( 'slug', get_query_var('term'), get_query_var('taxonomy') );

        if ( $term && get_query_var('taxonomy') !== 'language' ) {
            return apply_filters('single_term_title', $term->name);
        }
        elseif ( is_post_type_archive() ) {
            return apply_filters('the_title', ( @$queried_object->labels->name ?: $queried_object->post_title ));
        }
        elseif ( is_day() ) {
            return sprintf( __('Daily Archives: %s', 'boilerplate'), get_the_date() );
        }
        elseif ( is_month() ) {
            return sprintf( __('Monthly Archives: %s', 'boilerplate'), get_the_date('F Y') );
        }
        elseif ( is_year() ) {
            return sprintf( __('Yearly Archives: %s', 'boilerplate'), get_the_date('Y') );
        }
        elseif ( is_author() ) {
            $author = $queried_object;
            return sprintf( __('Author Archives: %s', 'boilerplate'), apply_filters( 'the_author', is_object( $author ) ? $author->display_name : null ) );
        }
        /*else {
            _e( 'Archives', 'twentyfourteen' );
        }*/
        else {
            return single_cat_title('', false);
        }

    } elseif ( is_search() ) {
        return sprintf( __('Search Results for &ldquo;%s&rdquo;', 'boilerplate'), get_search_query() );
    } elseif ( is_404() ) {
        return __('Not Found', 'boilerplate');
    } else {
        return get_the_title();
    }
}
