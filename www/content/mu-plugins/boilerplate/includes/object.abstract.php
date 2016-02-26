<?php

/**
 * File : Abstract Object Type Class
 *
 * @package  boilerplate
 */

namespace boilerplate\Objects;

use boilerplate\AbstractModel;

/**
 * Abstract : Object Type
 */

abstract class AbstractObject extends AbstractModel
{
    public $obj_type;
    public $obj_name;
    public $obj_page = false;
    public $obj_rewrite = false;

    protected $rewrite_slug;

    /**
     * Singleton
     *
     * @param  string  $class
     */

    public static function get_instance( $class = '' )
    {
        if ( empty( $class ) ) {
            $class = get_called_class();
        }

        if ( ! class_exists( $class ) ) {
            wp_die( sprintf( __('Class "%s" not found.', 'boilerplate'), $class ) );
        }

        if ( ! isset( static::$instances[ $class ] ) ) {
            static::$instances[ $class ] = new $class();

            if ( ! empty( static::$instances[ $class ]->obj_type ) ) {
                $all = \boilerplate\Objects\All::get_instance();

                $all::$post_types[ static::$instances[ $class ]->obj_type ] = static::$instances[ $class ];
            }
        }

        return static::$instances[ $class ];
    }

    /**
     * Bind actions and filters to customize columns
     */

    public function construct_columns()
    {
        if ( $this->obj_type === get_post_type() ) {
            add_filter( "manage_{$this->obj_type}_posts_columns",       [ &$this, 'manage_columns' ] );
            add_filter( "manage_edit-{$this->obj_type}_columns",        [ &$this, 'manage_columns' ] );
            add_action( "manage_{$this->obj_type}_posts_custom_column", [ &$this, 'manage_cells'   ], 10, 2 );
        }
    }

    /**
     * Filter the columns displayed in the Posts list table for a specific post type.
     *
     * @param array $post_columns An array of column names.
     * @return array $columns
     */

    public function add_columns( $columns = [] )
    {
    }

    /**
     * Fires for each custom column in the Posts list table.
     *
     * @param string $column_name The name of the column to display.
     * @param int    $post_id     The current post ID.
     */

    public function add_column_data( $column_name, $post_id )
    {
    }

    /**
     * Register Object Type
     *
     * @param array $args Array of arguments for registering a post type.
     *
     * @uses  WordPress\register_post_type()
     * @see http://codex.wordpress.org/Function_Reference/register_post_type
     */

    public function register( $args = [] )
    {
        $options = get_option('boilerplate', []);

        $this->obj_page = ( isset( $args['labels']->page_for_items_setting ) && isset( $options[ $args['labels']->page_for_items_setting ] ) ? $options[ $args['labels']->page_for_items_setting ] : false );

        if ( $this->obj_page && ! isset( $args['has_archive'] ) ) {
            $args['has_archive'] = true;
        }

        if ( $this->rewrite_slug && ! isset( $args['rewrite'] ) ) {
            $args['rewrite'] = [
                  'slug'       => $this->get_rewrite_slug()
                , 'with_front' => false
                , 'feed'       => true
                , 'pages'      => true
            ];
        }

        register_post_type( $this->obj_type, $args );
    }

    /**
     * Establish the object's rewrite slug.
     */

    public function set_rewrite_slug( $fallback = null )
    {
        if ( ! empty( $this->obj_page ) && ( $page = get_post( $this->obj_page ) ) ) {
            $this->rewrite_slug = $page->post_name;

            return true;
        }
        elseif ( ! empty( $fallback ) ) {
            $this->rewrite_slug = $fallback;

            return true;
        }

        return false;
    }

    /**
     * Retrieve the object's rewrite slug.
     *
     * @return string Object's rewrite slug
     */

    public function get_rewrite_slug()
    {
        return $this->rewrite_slug;
    }

    /**
     * Retrieve the object's permalink structure.
     *
     * @return string Object's Permastruct
     */

    public static function get_permastruct()
    {
        global $polylang;

        $obj = static::get_instance();
        $url = home_url( trailingslashit( $obj->get_rewrite_slug() ) );

        if ( isset( $polylang->curlang ) && is_object( $polylang->links_model ) && method_exists( $polylang->links_model, 'add_language_to_link' ) ) {
            $url = $polylang->links_model->add_language_to_link( $url, $polylang->curlang );
        }

        return $url;
    }

    /**
     * Filter : Apply native permalink structure
     */

    public function replace_permastruct( $struct, $post_type, $name, $args = [] )
    {
        if ( $this->obj_type === $post_type ) {
            global $wp_post_types, $wp_rewrite;

            $obj_tag = '%' . $this->obj_type . '%';
            $setting = $this->obj_name . '_structure';
            $options = get_option('boilerplate', []);

            $struct = ( isset( $options[ $setting ] ) ? $options[ $setting ] : '/' . $obj_tag . '/' );
            $struct = str_replace( '%postname%', $obj_tag, $struct );
            $struct = ltrim( $struct, '/' );
        }

        return $struct;
    }

    /**
     * Remove extra rules for archives.
     *
     * This removes rewrite rules dedicated for archive routes for certain
     * special object types. These special object types, like post, rely
     * on the `page_for_*` option as their archive center.
     *
     * @see Extra rules from WordPress (wp-include/post.php, register_post_type()).
     */

    public function remove_extra_rules( $rules )
    {
        global $wp_rewrite;

        if ( $this->obj_rewrite ) {
            $front = pll_get_rewrite_front();

            $translated_slugs = $this->pll_translate_slugs();
            $translated_slugs = $translated_slugs[ $this->obj_type ];

            foreach ( $translated_slugs as $lang => $translated_slug ) {
                $translated_slug = (object) $translated_slug;

                /**
                 * Remove the root archive request such that the request matches
                 * with the `pagename` query variable.
                 *
                 * @example Matchs & Removes "(fr|en)/statements/?$"
                 */

                $extra_rule_key = $front . $translated_slug->rewrite['slug'] . '/?$';

                if ( array_key_exists( $extra_rule_key, $rules ) ) {
                    unset( $rules[ $extra_rule_key ] );
                }

                /**
                 * Replace the archive pagination route such that the request matches
                 * with the `pagename` query variable.
                 *
                 * @example - Matches  "(fr|en)/statements/page/([0-9]{1,})/?$"
                 *          - Modifies "index.php?lang=$matches[1]&post_type=upa-statement&paged=$matches[2]"
                 */

                // Replace Archive Pagination Route
                // to match
                $extra_rule_key = $front . $translated_slug->rewrite['slug'] . '/' . $wp_rewrite->pagination_base . '/([0-9]{1,})/?$';

                if ( array_key_exists( $extra_rule_key, $rules ) ) {
                    $search  = [
                        '#lang=\$matches\[\d+?\]&?#',
                        '#post_type=' . $this->obj_type . '#'
                    ];
                    $replace = [
                        '',
                        'pagename=' . $translated_slug->rewrite['slug']
                    ];

                    $rules[ $extra_rule_key ] = preg_replace( $search, $replace, $rules[ $extra_rule_key ] );
                }
            }
        }

        return $rules;
    }

    /**
     * Check the current object against a WP_Query.
     *
     * @param object $obj
     *
     * @return bool
     */

    public function is_related( $obj )
    {
        if ( ! is_object( $obj ) ) {
            return null;
        }

        return ( is_a( $obj, 'WP_Query' ) && $this->obj_type === $obj->get('post_type') );
    }

    /**
     * Load MediaElement assets in case they don't get queueued.
     */

    function enqueue_media( $post )
    {
        if ( isset( $post->ID ) ) {
            wp_enqueue_media( array( 'post' => $post->ID ) );
        }
    }

}
