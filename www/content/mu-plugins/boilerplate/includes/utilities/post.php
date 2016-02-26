<?php

/**
 * File: Post Template Helpers
 *
 * Provides additional functions to retrieve
 * content for the current post in the Loop.
 *
 * @package Boilerplate\Utilities
 */

if ( ! function_exists( 'parse_permastruct_tags' ) ) :

    /**
     * Parse permalink rewrite tags
     *
     * Replicates the operations performed in {@see get_permalink()}
     * to build a valid URL from a permastruct.
     *
     * This offers a more flexible solution for custom permalinks.
     *
     * @global  Polylang     $polylang   If Polylang is available, translations will be considered.
     *
     * @param   string       $post_link  The permalink building blocks.
     * @param   int|WP_Post  $id         Optional. Post ID or post object. Default current post.
     * @param   bool         $leavename  Optional. Whether to keep post name or page name. Default false.
     * @return  string|bool  $post_link  The permalink URL or false if post does not exist.
     */

    function parse_permastruct_tags( $post_link, $id = 0, $leavename = false )
    {
        $rewritecode = [
            '%year%',
            '%monthnum%',
            '%day%',
            '%hour%',
            '%minute%',
            '%second%',
            ( $leavename ? '' : '%postname%' ),
            '%post_id%',
            '%category%',
            '%author%',
            ( $leavename ? '' : '%pagename%' ),
        ];

        if ( is_object( $id ) && isset( $id->filter ) && 'sample' == $id->filter ) {
            $post   = $id;
            $sample = true;
        } else {
            $post   = get_post( $id );
            $sample = false;
        }

        if ( empty( $post->ID ) ) {
            return false;
        }

        // If we aren't published, permalinks don't work
        $draft_or_pending = ( isset( $post->post_status ) && in_array( $post->post_status, [ 'draft', 'pending', 'auto-draft' ] ) );

        if ( ! empty( $post_link ) && ! $draft_or_pending ) {

            $post_type = get_post_type_object( $post->post_type );

            /**
             * Filter the post name to use as a slug.
             *
             * @param string  $post_name  The post's name.
             * @param WP_Post $post       The post in question.
             * @param bool    $leavename  Whether to keep the post name.
             */
            $slug = apply_filters( "parse_{$post->post_type}_link/postname", $post->post_name, $post, $leavename );
            if ( ! $leavename ) {
                if ( $post_type->hierarchical ) {
                    $slug = get_page_uri( $id );
                }
                $post_link = str_replace( "%$post->post_type%", $slug, $post_link );
            }

            if ( class_exists( 'Polylang' ) ) {
                global $polylang;

                if ( is_object( $polylang ) && $polylang->options['force_lang'] ) {
                    $post_language = $polylang->model->get_post_language( $post->ID );

                    if ( is_object( $post_language ) ) {
                        $post_language = $post_language->slug;
                    }

                    if ( ! $post_language && pll_is_translated_post_type( $post->post_type ) ) {
                        $post_language = pll_current_language();
                    }

                    if ( $post_language ) {
                        // $post_link = $polylang->links_model->add_language_to_link( $post_link, $post_language );
                        $lang = ( $polylang->options['default_lang'] == $post_language && $polylang->options['hide_default'] ? '' : ( $polylang->options['rewrite'] ? '' : 'language/' ) . $post_language );
                        $post_link = str_replace( '%language%', $lang, $post_link );
                    }
                }
            }

            $unixtime = strtotime( $post->post_date );

            $category = '';
            if ( strpos( $post_link, '%category%' ) !== false ) {
                $cats = get_the_category( $post->ID );

                if ( $cats ) {
                    usort( $cats, '_usort_terms_by_ID' ); // order by ID

                    /**
                     * Filter the category that gets used in the %category% permalink token.
                     *
                     * @param stdClass $cat  The category to use in the permalink.
                     * @param array    $cats Array of all categories associated with the post.
                     * @param WP_Post  $post The post in question.
                     */

                    $category_object = apply_filters( 'post_link_category', $cats[0], $cats, $post );

                    $category_object = get_term( $category_object, 'category' );
                    $category = $category_object->slug;

                    if ( $parent = $category_object->parent ) {
                        $category = get_category_parents($parent, false, '/', true) . $category;
                    }
                }
                // show default category in permalinks, without
                // having to assign it explicitly
                if ( empty( $category ) ) {
                    $default_category = get_term( get_option( 'default_category' ), 'category' );
                    $category = ( is_wp_error( $default_category ) ? '' : $default_category->slug );
                }
            }

            $author = '';
            if ( strpos( $post_link, '%author%' ) !== false ) {
                $authordata = get_userdata( $post->post_author );
                $author = $authordata->user_nicename;
            }

            $date = explode( ' ', date( 'Y m d H i s', $unixtime ) );
            $rewritereplace = [
                $date[0],
                $date[1],
                $date[2],
                $date[3],
                $date[4],
                $date[5],
                $post->post_name,
                $post->ID,
                $category,
                $author,
                $post->post_name,
            ];
            $post_link = home_url( str_replace( $rewritecode, $rewritereplace, $post_link ) );

            $post_link = user_trailingslashit( $post_link, 'single' );
        }

        return $post_link;
    }

endif;

if ( ! function_exists( 'parse_permalink_tags' ) ) :

	/**
	 * Parse permalink rewrite tags
	 *
	 * @see parse_permastruct_tags()
	 */

	function parse_permalink_tags( $post_link, $id = 0, $leavename = false )
	{
        return parse_permastruct_tags( $post_link, $id, $leavename );
	}

endif;
