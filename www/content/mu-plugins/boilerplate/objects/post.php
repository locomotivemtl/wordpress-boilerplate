<?php

/**
 * File : "Post" Object Type
 * Post Type : post
 *
 * Default Post Type
 *
 * Post in WordPress is a post type that is typical for and most used by blogs.
 * Posts are normally displayed in a blog in reverse sequential order by time
 * (newest posts first). Posts are also used for creating the feeds.
 */

namespace Boilerplate\Objects;

/**
 * Class : "Post" Object Type
 */

class Post extends AbstractObject
{
    use PolylangObject { PolylangObject::__construct as private polylang_hooks; }

    public $obj_type = 'post';
    public $obj_name = 'posts';

    public $obj_rewrite = true;

    public function __construct()
    {
        add_action('init',                 [ &$this, 'modify_object_type' ], 1);
        # add_action('registered_post_type', [ &$this, 'modify_permastruct' ], 1, 2);
        add_action('admin_menu',           [ &$this, 'rename_menu_labels' ], 1);

        // Polylang
        add_filter('pll_translated_post_type_rewrite_slugs', [ &$this, 'pll_translate_slugs' ]);
        add_filter('pll_translated_post_type_permastruct',   [ &$this, 'replace_permastruct' ], 1, 4);
    }

// ==========================================================================
// Features
// ==========================================================================

    /**
     * Modify "Post" Object Type
     *
     * Rename labels, disable comments, and enable archive.
     */

    public function modify_object_type()
    {
        _x( 'news', 'URI slug', 'boilerplate' );

        $this->obj_page = ( 'page' === get_option('show_on_front') ? get_option('page_for_posts') : false );

        $this->set_rewrite_slug( 'news' );

        unregister_taxonomy_for_object_type( 'post_tag', 'post' );

        remove_post_type_support( $this->obj_type, 'comments' );
        remove_post_type_support( $this->obj_type, 'custom-fields' );

        $object = get_post_type_object( $this->obj_type );

        $labels = &$object->labels;

        $labels->name           = __('News', 'boilerplate');
        $labels->menu_name      = __('News', 'boilerplate');
        $labels->name_admin_bar = __('News', 'boilerplate');
        $labels->singular_name  = __('Article', 'boilerplate');
        $labels->search_items   = __('Search Articles', 'boilerplate');
        $labels->all_items      = __('All Articles', 'boilerplate');

        if ( $this->obj_page ) {
            $object->has_archive = true;
        }
/*
        if ( $this->rewrite_slug ) {
            $object->rewrite['slug'] = $this->rewrite_slug;
        }
*/
        register_post_type( $this->obj_type, $object );
    }



    /**
     * Filter : Apply native permalink structure
     */

    public function replace_permastruct( $struct, $post_type, $name, $args = [] )
    {
        if ( $this->obj_type === $post_type ) {
            $permalink_structure = get_option('permalink_structure');
            $permalink_structure = substr( $permalink_structure, strpos( $permalink_structure, '%') );

            if ( ! empty( $permalink_structure ) ) {
                $struct = $permalink_structure;
                $struct = ltrim( $struct, '/' );
            }
        }

        return $struct;
    }



// ==========================================================================
// Labels
// ==========================================================================

    /**
     * Rename menu labels for Post object type
     */

    public function rename_menu_labels()
    {
        global $menu, $submenu;

        #$menu[5][0] = __('News', 'boilerplate');
        #$submenu['edit.php'][5][0] = __('All Articles', 'boilerplate');

        // Disable News from menu
        #unset( $menu[5] );

        // Disable Categories and Tags
        #unset($submenu['edit.php'][15], $submenu['edit.php'][16]);
    }

}

Post::get_instance();
