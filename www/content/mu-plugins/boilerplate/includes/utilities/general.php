<?php

/**
 * File: Framework Utilities
 *
 * @package Boilerplate\Utilities
 */

/**
 * Finds whether a variable is an associative array
 *
 * Determine whether the given variable is an associative array.
 * A variable is considered an associative array if its indexes
 * are _strings_ which also requires that the variable *not* be empty.
 *
 * @param mixed $var The variable being evaluated.
 *
 * @return boolean Returns TRUE if $var is an associative array, FALSE otherwise.
 */

function is_assoc( $var )
{
    return (bool) ( is_array( $var ) && count( array_filter( array_keys( $var ), 'is_string' ) ) );
}



/**
 * Is the value unknown?
 *
 * @param mixed $value
 *
 * @return string
 */

function is_unknown( $value )
{
    if ( is_string( $value ) ) {
        $value = trim( $value );
    }

    if ( empty( $value ) ) {
        return true;
    }

    if ( in_array( strtoupper( $value ), [ 'UNKNOWN', 'N/A' ] ) ) {
        return true;
    }

    if ( preg_match( '/[\?\-,]{3,}/i', $value ) ) {
        return true;
    }

    return false;
}



/**
 * Notice of non-availability
 *
 * @return string
 */

function not_available()
{
    return abbr( __( 'N/A', 'boilerplate' ), __( 'Not Available', 'boilerplate' ) );
}



if ( ! function_exists( 'has_query_var' ) ) :

    /**
     * Check if the current variable is set, and is not NULL, in the WP_Query class.
     *
     * @see    WP_Query::$query_vars
     * @uses   $wp_query
     *
     * @param  string        $var   The variable key to check for.
     * @param  null|WP_Query $query The WP_Query instance.
     *
     * @return bool True if the current variable is set.
     */

    function has_query_var( $var, $query = null )
    {
        if ( $query instanceof WP_Query ) {
            return isset( $query->query_vars[ $var ] );
        }
        else {
            global $wp_query;

            return isset( $wp_query->query_vars[ $var ] );
        }
    }

endif;



/**
 * Sets up a DateInterval from a time notation of the string.
 *
 * Uses regular expressions and the normal date parsers and sets up
 * a DateInterval from the notation used for a time strings
 * converted to the interval notation.
 *
 * As of 2015-06-02, milliseconds are rounded off because {@see DateInterval}
 * does not support fractions of a second.
 *
 * @param string         $time      A date with absolute parts. Specifically, the {@link http://php.net/manual/en/datetime.formats.time.php time formats}
 *                                  supported by the parser used for `strtotime()` and `DateTime`
 *                                  will be used to construct the DateInterval.
 * @param string|bool    $to_round  The {@see DateInterval} class does not support microseconds as of PHP5.6.
 *                                  Possible options can be: false (intact), true (`round()`), 'ceil', 'floor'.
 * @param string         $format    The format that the passed in string should be in.
 *                                  See the formatting options documented for {@link http://php.net/manual/en/function.date.php date()}.
 *
 * @return DateInterval
 */

function date_interval_create_from_time_string( $time, $to_round = true, $format = BOILERPLATE_PCRE_TIME_ELAPSED )
{
    preg_match( BOILERPLATE_PCRE_TIME_ELAPSED, $time, $parts );

    $duration = 'PT';

    /** Minutes */
    if ( $parts[1] ) {
        $duration .= $parts[1] . 'M';
    }

    /** Seconds + Milliseconds */
    if ( $parts[2] ) {
        switch ( $to_round ) {
            case true    :
            case 'round' : $duration .= round( $parts[2] ); break;
            case 'ceil'  : $duration .= ceil(  $parts[2] ); break;
            case 'floor' : $duration .= floor( $parts[2] ); break;
            default      : $duration .= $parts[2];          break;
        }

        $duration .= 'S';
    }

    $interval = new DateInterval( $duration );

    return $interval;
}



/**
 * Parse a time notation into an interval specification.
 *
 * Milliseconds are not rounded off. As of 2015-06-02, the interval is not
 * supported by {@see DateInterval}.
 *
 * @param string         $time      A date with absolute parts. Specifically, the {@link http://php.net/manual/en/datetime.formats.time.php time formats}
 *                                  supported by the parser used for `strtotime()` and `DateTime`
 *                                  will be used to construct the DateInterval.
 * @param string|bool    $to_round  The {@see DateInterval} class does not support microseconds as of PHP5.6.
 *                                  Possible options can be: false (intact), true (`round()`), 'ceil', 'floor'.
 * @param string         $format    The format that the passed in string should be in.
 *                                  See the formatting options documented for {@link http://php.net/manual/en/function.date.php date()}.
 *
 * @return string
 */

function timetointerval( $time, $to_round = false, $format = BOILERPLATE_PCRE_TIME_ELAPSED )
{
    preg_match( BOILERPLATE_PCRE_TIME_ELAPSED, $time, $parts );

    $duration = 'PT';

    /** Minutes */
    if ( $parts[1] ) {
        $duration .= $parts[1] . 'M';
    }

    /** Seconds + Milliseconds */
    if ( $parts[2] ) {
        switch ( $to_round ) {
            case true    :
            case 'round' : $duration .= round( $parts[2] ); break;
            case 'ceil'  : $duration .= ceil(  $parts[2] ); break;
            case 'floor' : $duration .= floor( $parts[2] ); break;
            default      : $duration .= $parts[2];          break;
        }

        $duration .= 'S';
    }

    return $duration;
}



/**
 * Retrieve variables in the WP_Query class.
 *
 * @param mixed[]       $vars  A collection of variables to retrieve.
 * @param null|WP_Query $query The WP_Query instance.
 *
 * @return mixed[]
 */

function boilerplate_get_query_vars( array $vars, $query = null )
{
    $vals = [];

    if ( is_assoc( $vars ) ) {
        foreach ( $vars as $key => $type ) {
            if ( $query instanceof WP_Query ) {
                $v = $query->get( $key, null );
            }
            else {
                $v = get_query_var( $key, null );
            }

            if ( ! is_null( $v ) ) {
                switch ( $type ) {
                    case 'int':
                        $v = (int) $v;
                        break;

                    case 'float':
                        $v = (float) $v;
                        break;

                    case 'string':
                        $v = (string) $v;
                        break;
                }
            }

            $vals[ $key ] = $v;
        }
    }
    else {
        foreach ( $vars as $key ) {
            if ( $query instanceof WP_Query ) {
                $vals[ $key ] = $query->get( $key, null );
            }
            else {
                $vals[ $key ] = get_query_var( $key, null );
            }
        }
    }

    return $vals;
}



if ( ! function_exists( 'get_object_type' ) ) :

    /**
     * Retrieve the post type of the current post,
     * of a given post, or of the queried object.
     *
     * @see get_post_type()
     *
     * @param  int|WP_Post  $post  Optional. Post ID or post object. Default is global $post.
     * @return string|bool  Post type on success, false on failure.
     */

    function get_object_type( $post = null )
    {
        global $wp_query;

        if ( $post = get_post( $post ) ) {
            return $post->post_type;
        }

        if ( ! empty( $wp_query->query_vars['post_type'] ) ) {
            return $wp_query->query_vars['post_type'];
        }

        if ( is_home() && is_main_query() ) {
            return 'post';
        }

        if ( $obj = get_queried_object() ) {
            return $obj->post_type;
        }

        return false;
    }

endif;



if ( ! function_exists( 'get_current_archive_link' ) ) :

    /**
     * Get the current archive link
     *
     * Documented in {@see Boilerplate\Canonical}
     *
     * @param boolean $paged Whether or not to return a link with the current page
     *                       in the archive. Defaults to `true`.
     */

    function get_current_archive_link( $paged = true )
    {
        $link = false;

        if ( is_front_page() ) {
            $link = home_url( '/' );
        }
        else if ( is_home() && 'page' == get_option( 'show_on_front' ) ) {
            $link = get_permalink( get_option( 'page_for_posts' ) );
        }
        else if ( is_tax() || is_tag() || is_category() ) {
            $term = get_queried_object();
            $link = get_term_link( $term, $term->taxonomy );
        }
        else if ( is_post_type_archive() ) {
            $link = get_post_type_archive_link( get_object_type() );
        }
        else if ( is_author() ) {
            $link = get_author_posts_url( get_query_var( 'author' ), get_query_var( 'author_name' ) );
        }
        else if ( is_archive() ) {
            if ( is_date() ) {
                if ( is_day() ) {
                    $link = get_day_link( get_query_var( 'year' ), get_query_var( 'monthnum' ), get_query_var( 'day' ) );
                }
                else if ( is_month() ) {
                    $link = get_month_link( get_query_var( 'year' ), get_query_var( 'monthnum' ) );
                }
                else if ( is_year() ) {
                    $link = get_year_link( get_query_var( 'year' ) );
                }
            }
        }

        if ( $paged && $link && get_query_var( 'paged' ) > 1 ) {
            global $wp_rewrite;

            if ( ! $wp_rewrite->using_permalinks() ) {
                $link = add_query_arg( 'paged', get_query_var( 'paged' ), $link );
            }
            else {
                $link = user_trailingslashit( trailingslashit( $link ) . trailingslashit( $wp_rewrite->pagination_base ) . get_query_var( 'paged' ), 'archive' );
            }
        }

        /**
         * Filter the current archive URL.
         *
         * @param string  $link   The current archive URL.
         * @param boolean $paged  Whether or not to return a link with the current
         *                        page in the archive. Defaults to `true`.
         */

        return apply_filters( 'current_archive_link', $link, $paged );
    }

endif;
