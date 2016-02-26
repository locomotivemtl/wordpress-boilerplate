<?php

/**
 * File : WordPress Search
 *
 * @package Boilerplate
 */

namespace Boilerplate;

use WP_Query;
use WP_Meta_Query;

/**
 * Class : Search
 */

class Search
{
    protected $custom_fields = [

    ];

    /**
     * Whether to trigger JOIN clause for postmeta
     *
     * @var bool
     */

    protected $search_postmeta = false;

    /**
     * Cached list of search stopwords.
     *
     * @var array
     */

    protected $stopwords = [];

    public static function init()
    {
        // Commented since buggy behaviour with mu-plugins includes
        $class = get_called_class();

        $class = new $class();

        add_action( 'init',                     [ &$class, 'pll_init' ], 21 );
        add_action( 'template_redirect',        [ &$class, 'nice_search_redirect' ] );

        add_filter( 'posts_search',             [ &$class, 'search_postmeta'    ], 1, 2 );
        add_filter( 'posts_join',               [ &$class, 'join_postmeta'      ], 11, 2 );
        add_filter( 'posts_request',            [ &$class, 'cancel_request'     ], 1, 2 );
        add_filter( 'posts_orderby',            [ &$class, 'group_by_post_type' ], 10, 2 );
        //add_filter( 'search_rewrite_rules',     [ &$class, 'rewrite_rules'      ], 11 );

        //add_filter( 'search_link',              [ &$class, 'search_link'        ], 1, 4 );
    }

    /**
     * Hook into Polylang's Initialization Process
     *
     * We need to remove a few filters because they can make a mess.
     */

    public function pll_init()
    {
        global $polylang, $polylang_trs;

        if ( ! is_object( $polylang ) ) {
            return false;
        }

        // Bypass Polylang
        remove_filter('search_link', [ $polylang->links, 'search_link' ], 20);
    }

    /**
     * Retrieve permalink for search.
     *
     * @param string $query Optional. The query string to use. If empty the current query is used.
     * @return string
     */

    function search_link( $link, $search )
    {
        $lang = pll_current_language();
        $slug = pll_translations_x( 'search', 'URI slug', 'boilerplate', $lang );

        //$link = "/$slug/$search";
        $link = "/$search";
        $link = home_url( user_trailingslashit( $link, 'search' ) );

        /**
         * Filter the search permalink.
         *
         * @param string $link   Search permalink.
         * @param string $search The URL-encoded search term.
         */
        var_dump($link);
        var_dump($search);
        return apply_filters( 'pll_search_link', $link, $search );
    }

    /**
     * Filter the JOIN clause of the query.
     *
     * If we are searching through metadata, make sure the 'postmeta' is joined.
     *
     * @global wpdb $wpdb
     *
     * @param string   $join   The JOIN clause of the query.
     * @param WP_Query &$query The WP_Query instance (passed by reference).
     */

    function join_postmeta( $join, &$query )
    {
        if ( $query->is_search() && ! $query->get('suppress_filters') && $this->search_postmeta ) {
            global $wpdb;

            $meta  = " INNER JOIN $wpdb->postmeta ON ( $wpdb->posts.ID = $wpdb->postmeta.post_id ) ";

            if ( false === strpos( $join, $meta ) ) {
                $join .= $meta;
            }
        }

        return $join;
    }

    /**
     * Filter the search SQL that is used in the WHERE clause of WP_Query.
     *
     * Include searching through certain custom fields
     * to find supplementary results.
     *
     * @global wpdb $wpdb
     *
     * @param string   $where Search SQL for WHERE clause.
     * @param WP_Query $query The current WP_Query object.
     *
     * @return string $where
     */

    function search_postmeta( $where, &$query )
    {
        if ( $query->is_search() && ! $query->get('suppress_filters') ) {
            global $wpdb;

            $q = &$query->query_vars;

            if ( count( $q['search_terms'] ) && count( $this->custom_fields ) ) {
                $n = ( ! empty( $q['exact'] ) ? '' : '%' );

                $search = '';
                $and    = '';

                foreach ( $q['search_terms'] as $term ) {
                    $like = $n . $wpdb->esc_like( $term ) . $n;
                    $test = "($wpdb->posts.post_title LIKE %s) OR ($wpdb->posts.post_content LIKE %s)";
                    $args = [ $like, $like ];

                    foreach ( $this->custom_fields as $field ) {
                        $compare = ( false !== strpos( $field, '%' ) ? 'LIKE' : '=' );
                        $args[]  = $field;
                        $args[]  = $like;
                        $test   .= " OR ($wpdb->postmeta.meta_key {$compare} %s AND CAST($wpdb->postmeta.meta_value AS CHAR) LIKE %s)";
                    }

                    $search .= call_user_func_array( [ $wpdb, 'prepare' ], [ "{$and}( {$test} )", $args ] );
                    $and = ' AND ';
                }

                if ( ! empty( $search ) ) {
                    $this->search_postmeta = true;

                    $where = " AND ({$search}) ";

                    if ( ! is_user_logged_in() ) {
                        $where .= " AND ($wpdb->posts.post_password = '') ";
                    }
                }
            }
        }

        return $where;
    }

    /**
     * Filter the ORDER BY clause of the query.
     *
     * Sort search results by object type.
     *
     * @global wpdb $wpdb
     *
     * @param string   $orderby The ORDER BY clause of the query.
     * @param WP_Query &$this   The WP_Query instance (passed by reference).
     */

    function group_by_post_type( $orderby, $query )
    {
        global $wpdb;

        if ( $query->is_search() && ! $query->get('suppress_filters') ) {
            $order = " $wpdb->posts.post_type ASC";

            if ( empty( $orderby ) ) {
                $orderby .= $order;
            }
            else {
                $orderby = $order . ', ' . $orderby;
            }
        }

        return $orderby;
    }

    /**
     * Filter the completed SQL query before sending.
     *
     * @param array    $request The complete SQL query.
     * @param WP_Query &$this   The WP_Query instance (passed by reference).
     */

    function cancel_request( $request, $query )
    {
        if ( $query->is_main_query() && $query->is_search && empty( $query->query_vars['s'] ) ) {
            return '';
        }

        return $request;
    }

    /**
     * Redirects search results from /?s=query to /search/query/, converts %20 to +
     *
     * @link http://txfx.net/wordpress-plugins/nice-search/
     *
     * You can enable/disable this feature in functions.php (or lib/config.php if you're using Roots):
     * add_theme_support('soil-nice-search');
     */

    function nice_search_redirect()
    {
        global $wp_rewrite;

        if ( ! isset( $wp_rewrite ) || ! is_object( $wp_rewrite ) || ! $wp_rewrite->using_permalinks() ) {
            return;
        }

        //$search_uri  = '/' . pll_current_language() . '/' . _x('search', 'URI slug', 'boilerplate') . '/';
        $search_uri  = '/' . _x('search', 'URI slug', 'boilerplate') . '/';

        if ( is_search() && ! is_admin() && strpos( $_SERVER['REQUEST_URI'], $search_uri ) === false ) {
            wp_redirect( home_url( $search_uri . urlencode( get_query_var('s') ) ) );
            exit();
        }
    }

    /**
     * Filter rewrite rules used for search archives.
     *
     * Likely search-related archives include /search/search+query/ as well as
     * pagination and feed paths for a search.
     *
     * @param array $search_rewrite The rewrite rules for search queries.
     */

    function rewrite_rules( $rules )
    {
        global $wp_rewrite, $polylang;

        _x('search', 'URI slug', 'boilerplate');

        if ( ! empty( $polylang ) && $polylang->options['force_lang'] ) {

            $new_rules = [];

            $languages = $polylang->model->get_languages_list([ 'fields' => 'slug' ]);
            if ( $polylang->options['hide_default'] ) {
                $languages = array_diff( $languages, [ $polylang->options['default_lang'] ] );
            }

            if ( ! empty( $languages ) ) {
                $slug = $wp_rewrite->root . ( $polylang->options['rewrite'] ? '' : 'language/') . '(' . implode( '|', $languages ) . ')/';
            }

            $translations = pll_translations_x( 'search', 'URI slug', 'boilerplate' );
            //$search_uri = '(' . implode( '|', $translations ) . ')';
            $search_uri = '(search|recherche)';

            foreach ( $rules as $key => $rule ) {

                $new_rules[ str_replace( $wp_rewrite->search_base, $search_uri, $key ) ] = str_replace(
                    [ '[9]', '[8]', '[7]', '[6]', '[5]', '[4]', '[3]', '[2]' ],
                    [ '[10]', '[9]', '[8]', '[7]', '[6]', '[5]', '[4]', '[3]' ],
                    $rule
                );

            }

            $new_rules[ $slug . $search_uri . '/?$' ] = 'index.php?lang=$matches[1]&s=%20';

            $rules = $new_rules;
        }

        return $rules;
    }

}

Search::init();
