<?php

/**
 * File : All Object Types
 *
 * General actions and filters affecting all.
 */

namespace Boilerplate\Objects;

use Exception;

/**
 * Class : "Post" Object Type
 */

class All extends AbstractObject
{
    use PolylangObject { PolylangObject::__construct as private polylang_hooks; }

    public static $post_types = [];

    protected $_links = [];

    public function __construct()
    {
        add_action('init',                    [ &$this, 'pll_init' ], 21);

        add_action('parse_query',             [ &$this, 'parse_query'   ], 6); // After PLL_Frontend's 'parse_query'
        add_action('pre_get_posts',           [ &$this, 'pre_get_posts' ], 6); // After PLL_Frontend's 'parse_query'

        add_filter('post_link',               [ &$this, 'object_link'   ], 6, 4);
        add_filter('post_type_link',          [ &$this, 'object_link'   ], 6, 4);
        add_filter('post_type_archive_link',  [ &$this, 'archive_link'  ], 25, 2); // Bypass Polylang
        add_filter('post_type_archive_title', [ &$this, 'archive_title' ], 6, 2);

        # add_filter('rewrite_rules_array',   [ &$this, 'remove_extra_rules' ], 15);

        // Polylang
        $this->polylang_hooks();
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
        remove_filter('post_link',              [ $polylang->links, 'post_link'      ], 20);
        remove_filter('post_type_link',         [ $polylang->links, 'post_type_link' ], 20);
        # remove_filter('post_type_archive_link', [ $polylang->links, 'archive_link'   ], 20);

        if ( class_exists('Polylang_Translate_Rewrite_Slugs') && is_a( $polylang_trs, 'Polylang_Translate_Rewrite_Slugs' ) ) {
            remove_filter('post_type_link',         [ $polylang_trs, 'post_type_link_filter'         ], 10);
            remove_filter('post_type_archive_link', [ $polylang_trs, 'post_type_archive_link_filter' ], 25);
        }
    }

    /**
     * Parse WP_Query
     */

    public function parse_query( &$query )
    {
        $qv = &$query->query_vars;

        if ( ! $query->is_home ) {
            foreach ( static::$post_types as $obj_name => $obj_inst ) {
                if ( '' !== $qv['pagename'] ) {
                    if ( $obj_inst->obj_page && isset( $query->queried_object_id ) && ( $query->queried_object_id == $obj_inst->obj_page || $query->queried_object_id == pll_get_post( $obj_inst->obj_page ) ) ) {
                        unset( $qv['pagename'] );

                        if ( ! isset($qv['withcomments']) || ! $qv['withcomments'] ) {
                            $query->is_comment_feed = false;
                        }

                        $query->is_single = false;
                        $query->is_singular = false;
                        $query->is_page = false;
                        $query->is_archive = true;
                        $query->is_post_type_archive = true;

                        $qv['post_type'] = $obj_name;

                        break;
                    }
                }

                if ( $qv['page_id'] ) {
                    if  ( $qv['page_id'] == $obj_inst->obj_page || $qv['page_id'] == pll_get_post( $obj_inst->obj_page ) ) {
                        unset( $qv['page_id'] );

                        if ( ! isset($qv['withcomments']) || ! $qv['withcomments'] ) {
                            $query->is_comment_feed = false;
                        }

                        $query->is_single = false;
                        $query->is_singular = false;
                        $query->is_page = false;
                        $query->is_archive = true;
                        $query->is_post_type_archive = true;

                        $qv['post_type'] = $obj_name;

                        break;
                    }
                }
            }
        }

        if ( $post_type = $query->get('post_type') ) {
            if ( ! is_array( $post_type ) && isset( static::$post_types[ $post_type ] ) ) {
                $obj_inst = static::$post_types[ $post_type ];

                if ( method_exists( $obj_inst, 'parse_query' ) ) {
                    call_user_func_array( [ $obj_inst, 'parse_query' ], [ &$query ] );
                }
            }

            /**
             * Fires, for a specific post type, after the framework has parsed the main query vars.
             *
             * The dynamic portion of the hook name, `$post_type`, refers to the post type slug.
             *
             * @param WP_Query &$query The WP_Query instance (passed by reference).
             */
            do_action_ref_array( "parse_{$post_type}_query", [ &$query ] );
        }
    }

    /**
     * Prepare WP_Query
     */

    public function pre_get_posts( &$query )
    {
        // Shorthand.
        $qv = &$query->query_vars;

        // Fill again in case pre_get_posts unset some vars.
        # $qv = $query->fill_query_vars( $qv );

        if ( ! $query->is_home ) {

            if ( ! $query->is_page && $query->is_post_type_archive ) {
                unset( $qv['page_id'], $qv['pagename'] );
            }

        }

        if ( $query->is_search || $query->is_post_type_archive ) {
            $qv['nopaging'] = true;
        }

        if ( $query->is_search ) {
            $qv['s'] = trim( $qv['s'] );

            if ( empty( $qv['s'] ) ) {
                $qv = [];
            }
        }

        /**
         * @hack Using a backtrace to figure out if this $query was called by {@see acf_get_posts()}
         *       by way of {@see acf_field->render_field()}.
         *       If it was initiated by said function, we enable mixing post translations.
         */

        $is_acf_get_posts = (
               ( $e = new Exception )
            && ( $trace = $e->getTraceAsString() )
            && false !== strpos( $trace, 'acf_get_posts(Array)' )
            && (
                   (   is_admin() && false !== strpos( $trace, '->render_field(Array)' ) )
                || ( ! is_admin() && false !== strpos( $trace, '->format_value(' ) )
            )
        );

        if ( $is_acf_get_posts && pll_is_not_translated_post_type( $qv['post_type'] ) ) {
            pll_remove_language_query_var( $query );
        }

        /**
         * Do we need to mix translations?
         */

        $has_mixed_translations = ( ! is_admin() && ( $query->is_main_query() || ( isset( $qv['lang'] ) && 'mix' === $qv['lang'] ) ) );

        if ( function_exists('pll_default_language') && $has_mixed_translations ) {
            if ( isset( $qv['lang'] ) && 'mix' === $qv['lang'] ) {
                pll_remove_language_query_var( $query );
            }

            self::pre_get_default_language_posts( $query );
        }

        if ( $post_type = $query->get('post_type') ) {
            /**
             * Fires, for a specific post type, after the query variable object is created, but before the actual query is run.
             *
             * The dynamic portion of the hook name, `$post_type`, refers to the post type slug.
             *
             * @param WP_Query &$query The WP_Query instance (passed by reference).
             */
            do_action_ref_array( "pre_get_{$post_type}_posts", [ &$query ] );
        }
    }

    /**
     * Display posts in the default language if the translation does not exist
     *
     * Modifies the taxonomy query set by PLL_Choose_Lang::set_lang_query_var()
     *
     * @todo This method is conceived to deal with bilingual posts only.
     * @link http://wordpress.syllogic.in/2014/08/going-multi-lingual-with-polylang/
     */

    public function pre_get_default_language_posts( &$query )
    {
        $dl = pll_default_language();
        $cl = pll_current_language();

        if ( $dl !== $cl ) {
            /** Polylang stores translated post IDs in a serialized array in the description field of this custom taxonomy */
            $terms = get_terms('post_translations');

            $exclude_posts = [];

            foreach ( $terms as $translation ) {
                $tp = unserialize( $translation->description );

                /** If the current language is not the default, lets pick up the default language post */
                if ( $dl !== $cl && isset( $tp[ $dl ] ) ) {
                    $exclude_posts[] = $tp[ $dl ];
                }
            }

            $query->set( 'post__not_in', $exclude_posts ); /** Remove the duplicate post in the default language */
            $query->set( 'lang', "$dl,$cl" );  /** Select both default and current language post */

            /** Remove any existing term-based language determiners to prevent conflicts */
            if ( ! empty( $query->query_vars['tax_query'] ) ) {
                foreach ( $query->query_vars['tax_query'] as $tax_key => $tax_query ) {
                    if ( 'term_taxonomy_id' === $tax_query['field'] && 'language' === $tax_query['taxonomy'] && 'IN' === $tax_query['operator'] ) {
                        unset( $query->query_vars['tax_query'][ $tax_key ] );
                        break;
                    }
                }
            }
        }
    }

    /**
     * Remove extra rules for archives.
     *
     * Modified method to target all object types.
     *
     * @see \Boilerplate\AbstractObject::remove_extra_rules().
     */

    public function remove_extra_rules( $rules )
    {
        $front = pll_get_rewrite_front();

        foreach ( static::$post_types as $obj_name => $obj_inst ) {
            if ( $obj_inst->obj_rewrite && method_exists( $obj_inst, 'remove_extra_rules' ) ) {
                $rules = $obj_inst->remove_extra_rules( $rules );
            }
        }

        return $rules;
    }

    /**
     * Retrieve the permalink for a post type object.
     *
     * @param string       $post_link  The post's permalink.
     * @param int|WP_Post  $post       The post in question.
     * @param bool         $leavename  Whether to keep the post name.
     * @param bool         $sample     Is it a sample permalink.
     */

    public function object_link( $link, $post, $leavename = false, $sample = false )
    {
        global $polylang, $wp_rewrite;

        $key = $link . '|' . (int) $leavename . '|' . (int) $sample;

        /** Emulate Polylang's "cached" collection */
        if ( isset( $this->_links[ $key ] ) ) {
            return $this->_links[ $key ];
        }
        else {
            // Manipulate a copy
            $post_link = $link;
            $permastruct = '';

            // This filter uses the ID instead of the post object
            if ( '_get_page_link' == current_filter() ) {
                $post = get_post( $post );
            }

            if ( ! isset( $wp_rewrite->extra_permastructs[ $post->post_type ] ) ) {

                $post_language = $polylang->model->get_post_language( $post->ID );

                if ( is_object( $post_language ) ) {
                    $post_language = $post_language->slug;
                }

                if ( ! $post_language && pll_is_translated_post_type( $post->post_type ) ) {
                    $post_language = pll_current_language();
                }

                if ( $post_language ) {
                    $permastruct = $wp_rewrite->get_extra_permastruct( $post->post_type . '_' . $post_language );
                }

                $post_type = get_post_type_object( $post->post_type );

                $slug = $post->post_name;

                // If we aren't published, permalinks don't work
                $draft_or_pending = isset( $post->post_status ) && in_array( $post->post_status, [ 'draft', 'pending', 'auto-draft' ] );

                if ( ( ! empty( $post_link ) || ! empty( $permastruct ) ) && ( ! $draft_or_pending || $sample ) ) {
                    $filter_name = "parse_{$post->post_type}_link";
                    $filter_link = ( empty( $permastruct ) ? $post_link : $permastruct );

                    if ( has_filter( $filter_name ) ) {  // Don't bother filtering and parsing if no plugins are hooked in.
                        /**
                         * Filter the post link with the permastruct manually.
                         *
                         * @param string  $post_link  The post's permalink.
                         * @param WP_Post $post       The post in question.
                         * @param bool    $leavename  Whether to keep the post name.
                         */
                        $post_link = apply_filters( $filter_name, $filter_link, $post, $leavename );
                    }
                    else {
                        $post_link = parse_permalink_tags( ( empty( $permastruct ) ? $post_link : $permastruct ), $post, $leavename );
                    }
                }
            }

            /**
             * Filter the permalink for a post with a custom post type.
             *
             * @param string  $post_link  The post's permalink.
             * @param WP_Post $post       The post in question.
             * @param bool    $leavename  Whether to keep the post name.
             * @param bool    $sample     Is it a sample permalink.
             */

            $post_link = apply_filters( 'pll_post_type_link', $post_link, $post, $leavename, $sample );

            $this->_links[ $key ] = $post_link;
        }

        return $post_link;
    }

    /**
     * Retrieve the permalink for a post type archive.
     */

    public function archive_link( $link, $post_type )
    {
        /** Emulate Polylang's "cached" collection */
        if ( isset( $this->_links[ $link ] ) ) {
            return $this->_links[ $link ];
        }

        if ( isset( static::$post_types[ $post_type ] ) ) {
            $obj_inst = static::$post_types[ $post_type ];

            if ( $obj_inst->obj_page ) {
                $link  = get_permalink( pll_get_post( $obj_inst->obj_page ) );
                $this->_links[ $link ] = $link;
            }
        }

        return $link;
    }

    /**
     * Retrieve the title for a post type archive.
     */

    public function archive_title( $title, $post_type )
    {
        if ( isset( static::$post_types[ $post_type ] ) ) {
            $obj_inst = static::$post_types[ $post_type ];

            if ( $obj_inst->obj_page ) {
                $title = get_the_title( pll_get_post( $obj_inst->obj_page ) );
            }
        }

        return $title;
    }

}

All::get_instance();
