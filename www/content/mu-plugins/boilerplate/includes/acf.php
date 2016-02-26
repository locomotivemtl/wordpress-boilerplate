<?php

/**
 * File : ACF Options Section
 *
 * @package Boilerplate
 */

namespace Boilerplate;

if ( ! class_exists('acf') ) {
    return;
}

use acf_form_post;
use acf_form_attachment;
use acf_form_taxonomy;
use acf_form_user;
use acf_form_comment;
use acf_form_widget;

/**
 * Class : Toolbar
 */

class ACF
{
    public static $json_path;
    public static $toplevel_page = 'toplevel';

    public static function init()
    {
        static::$json_path = WPMU_PLUGIN_DIR . '/boilerplate/acf-json';

        $class = get_called_class();

        add_filter( 'pll/acf/value_keys', [ $class, 'boilerplate_acf_field_value_keys' ], 10, 2 );

        add_filter( 'acf/load_value/name=menu_order', [ $class, 'get_menu_order' ], 10, 3 );

        if ( class_exists('acf_location') ) {
            add_filter( 'acf/location/rule_values/page_type', [ $class, 'location_page_type_rule_values' ], 1 );
            add_filter( 'acf/location/rule_match/page_type',  [ $class, 'location_page_type_rule_match'  ], 1, 3 );
        }

        if ( class_exists('acf_json') ) {
            add_filter( 'acf/settings/save_json', [ $class, 'save_json' ], 1 );
            add_filter( 'acf/settings/load_json', [ $class, 'load_json' ], 1 );
        }

        if ( function_exists('acf_add_options_page') && function_exists('acf_add_options_sub_page') ) {
            if ( is_admin() ) {
                static::add_options_pages();

                add_action( 'acf/save_post',  [ $class, 'save_options' ], 1 );
            }

            add_filter( 'acf/load_value', [ $class, 'load_option' ], 1, 3 );

            add_action( 'acf/include_fields', [ $class, 'prepare_field_references' ], 11 );

            add_action( 'admin_enqueue_scripts', [ $class, 'admin_enqueue_assets' ] );

            add_action( 'load-' . static::$toplevel_page . '_page_boilerplate-settings', [ $class, 'options_init' ], 1 );
        }
    }

    /**
     * @used-by  Filters\"pll/acf/value_keys"
     * @param    array  $field_keys
     * @param    array  $field
     */

    public static function boilerplate_acf_field_value_keys( $field_keys, $field )
    {
        if ( 'textarea' === $field['type'] ) {
            array_walk(
                $field_keys,
                function ( &$multiline, $key ) use ( $field ) {
                    $multiline = true;
                }
            );
        }

        if ( class_exists('acf_field_address') && $field['type'] === 'address' ) {

            $field_keys = array_merge(
                $field_keys,
                [
                    'value.street1' => false,
                    'value.street2' => false,
                    'value.street3' => false,
                    'value.city'    => false,
                    'value.state'   => false,
                    'value.zip'     => false,
                    'value.country' => false
                ],
                [
                    'default_value.street1' => false,
                    'default_value.street2' => false,
                    'default_value.street3' => false,
                    'default_value.city'    => false,
                    'default_value.state'   => false,
                    'default_value.zip'     => false,
                    'default_value.country' => false
                ]
            );

        }

        return $field_keys;
    }

// ==========================================================================
// Field Groups
// ==========================================================================

    /**
     * Action: Enqueue assets for the administration
     *
     * @todo Support acf_form_widget() validation
     */

    public static function admin_enqueue_assets()
    {
        $file_rel = '/boilerplate/assets/scripts/dist/*.js';
        $file_abs = WPMU_PLUGIN_DIR . $file_rel;

        if ( is_readable( $file_abs ) ) {
            $file_url = WPMU_PLUGIN_URL . $file_rel;
            wp_enqueue_script( '*', $file_url, [ 'jquery' ], false, true );
        }

        $found = false;

        $acf_form_post = new acf_form_post();

        if ( ! $found && $acf_form_post->validate_page() ) {
            global $post, $typenow;

            $args = [
                'post_id'   => ( empty( $post ) ? 0 : $post->ID ),
                'post_type' => $typenow
            ];

            $found = true;
        }
        else {
            $acf_form_attachment = new acf_form_attachment();
        }

        if ( ! $found && $acf_form_attachment->validate_page() ) {
            $args = [
                'attachment' => 'All'
            ];

            $found = true;
        }
        else {
            $acf_form_taxonomy = new acf_form_taxonomy();
        }

        if ( ! $found && $acf_form_taxonomy->validate_page() ) {
            $screen = get_current_screen();
            $taxonomy = $screen->taxonomy;

            $args = [
                'taxonomy' => $taxonomy
            ];

            $found = true;
        }
        else {
            $acf_form_user = new acf_form_user();
        }

        if ( ! $found && $acf_form_user->validate_page() ) {
            $current_user = wp_get_current_user();

            $args = [
                'user_id'   => ( defined( 'IS_PROFILE_PAGE' ) && IS_PROFILE_PAGE ? $current_user->ID : ( isset( $_REQUEST['user_id'] ) && $user_id = absint( $_REQUEST['user_id'] ) ? $user_id : 'new' ) ),
                'user_form' => $user_form
            ];

            $found = true;
        }
        else {
            $acf_form_comment = new acf_form_comment();
        }

        if ( ! $found && $acf_form_comment->validate_page() ) {
            $args = [
                'comment' => ( isset( $_GET['c'] ) && $comment_id = absint( $_GET['c'] ) ? $comment_id : 'new' )
            ];

            $found = true;
        }

        if ( ! $found ) {
            return;
        }

        $field_groups = acf_get_field_groups( $args );

        foreach ( $field_groups as $group ) {
            if ( isset( $group['key'] ) ) {
                $asset_key = 'acf-input-' . $group['key'];
                $file_rel  = '/boilerplate/assets/styles/dist/' . $group['key'] . '.css';
                $file_abs  = WPMU_PLUGIN_DIR . $file_rel;

                if ( is_readable( $file_abs ) ) {
                    # $file_uri = WPMU_PLUGIN_URL . $file_rel;
                    wp_add_inline_style( 'acf-input', trim( file_get_contents( $file_abs ) ) );
                }

                $file_rel = '/boilerplate/assets/scripts/dist/' . $group['key'] . '.js';
                $file_abs = WPMU_PLUGIN_DIR . $file_rel;

                if ( is_readable( $file_abs ) ) {
                    $file_url = WPMU_PLUGIN_URL . $file_rel;
                    wp_enqueue_script( $asset_key, $file_url, [ 'jquery' ], false, true );
                }
            }
        }
    }

    /**
     * Filter: Save field groups and field settings to local JSON files
     *
     * @param  string $path
     * @return $path
     */

    public static function save_json( $path = '' )
    {
        // Use project dir if it exists
        if ( is_writable( static::$json_path ) ) {
            $path = static::$json_path;
        }

        return $path;
    }

    /**
     * Filter: Load field groups and field settings to local JSON files
     *
     * @param  array $path
     * @return $paths
     */

    public static function load_json( $paths = [] )
    {
        // Use project dir if it exists
        if ( is_readable( static::$json_path ) ) {
            $paths[] = static::$json_path;
        }

        return $paths;
    }



// ==========================================================================
// Locations
// ==========================================================================


    /**
     * Modify values in the "Page Type" location rule
     *
     * Added:
     * - Nowhere
     * - Pages for archives
     *
     * @param  array  $choices
     * @return bool
     */

    public static function location_page_type_rule_values( $choices = [] )
    {
        $choices[''] = __( 'None', 'boilerplate' );
        $choices['archives'] = __( 'Site Archives', 'boilerplate' );

        ksort( $choices );

        return $choices;
    }



    /**
     * Match modified values for "Page Type" location rule
     *
     * @param  bool   $match    The true / false variable which must be returned.
     * @param  array  $rule     The current rule that you are matching against.
     * @param  array  $options  Data about the current edit screen, includes any data posted in an AJAX call.
     * @return bool   $match
     */

    public static function location_page_type_rule_match( $match, $rule = [], $options = [] )
    {
        if ( 'archives' === $rule['value'] ) {

            if ( empty( $options['post_id'] ) ) {
                return false;
            }

            $post = get_post( $options['post_id'] );

            // Test for Post Archive
            $archive_page = (int) get_option('page_for_posts');

            if ( $post->ID != $archive_page ) {

                $boilerplate_options = get_option('boilerplate', []);

                if ( empty( $boilerplate_options ) ) {
                    return false;
                }

                $exclude    = [ 'post', 'attachment', 'polylang_mo' ];
                $post_types = acf_get_post_types( $exclude );

                foreach ( $post_types as $post_type ) {
                    $archive_page = 0;

                    $obj = get_post_type_object( $post_type );

                    if ( ! isset( $obj->has_archive ) || ! $obj->has_archive ) {
                        continue;
                    }

                    if ( empty( $obj->labels->page_for_items_setting ) || empty( $boilerplate_options[ $obj->labels->page_for_items_setting ] ) ) {
                        continue;
                    }

                    $archive_page = (int) $boilerplate_options[ $obj->labels->page_for_items_setting ];

                    if ( $post->ID == $archive_page ) {
                        break;
                    }
                }

            }

            if ( $post->ID == $archive_page ) {

                if ( $rule['operator'] === '==' ) {
                    return true;
                }
                elseif ( $rule['operator'] === '!=' ) {
                    return false;
                }

            }
            else {
                return true;
            }

        }

        return $match;
    }



// ==========================================================================
// Options Page
// ==========================================================================

    /**
     *
     */

    public static function options_init()
    {
        set_current_screen('boilerplate-settings');
    }

    /**
     * Add ACF Options Pages and Subpages.
     *
     * Since we only have one page, "Contact Information",
     * we've assigned it as a sub-page to the "Settings" page
     * (`options-general.php`).
     */

    public static function add_options_pages()
    {
        static::$toplevel_page = 'settings';

        /**
         * Top-Level Menu Item
         */
        /*
        acf_add_options_page([
              'page_title' => sprintf( _x( '%s Settings', 'project settings', 'boilerplate' ), get_bloginfo('name') )
            , 'menu_title' => 'boilerplate'
            , 'menu_icon'  => 'dashicons-location'
            , 'menu_slug'  => 'boilerplate-settings'
            , 'capability' => 'edit_posts'
            , 'redirect'   => true
        ]);
        */

        /**
         * Sub-Level Menu Items
         */

        acf_add_options_sub_page([
              'page_title'  => __('Contact Information', 'boilerplate')
            , 'menu_title'  => __('Contact')
            , 'menu_icon'   => 'dashicons-admin-settings'
            , 'menu_slug'   => 'boilerplate-settings' # boilerplate-contact-settings
            , 'parent_slug' => 'options-general.php'  # 'boilerplate-settings'
        ]);

    }

    /**
     * Create a valid field reference to for the ACF options
     * located under the boilerplate option.
     *
     * @param array $acf_options
     */

    public static function save_field_references( array $acf_options )
    {
        static $done = false;

        if ( $done ) {
            return;
        }

        if ( function_exists('pll_current_language') ) {
            $current_lang = pll_current_language();
            $default_lang = pll_default_language();
        }
        else {
            $current_lang = null;
            $default_lang = null;
        }

        if ( ! empty( $acf_options ) ) {
            foreach ( $acf_options as $key => $reference ) {
                $option = 'options' . ( $current_lang !== $default_lang ? "_{$current_lang}" : '' );

                // Reference
                if ( is_string( $reference ) && substr( $reference, 0, 6 ) === 'field_' ) {
                    $key = substr( $key, 1 );
                    wp_cache_set( "field_reference/post_id={$option}/name={$key}", $reference, 'acf' );
                }
                /*
                // Value
                else {
                    # var_dump( "load_value/post_id={$option}/name={$key} -- {$reference}" );
                    wp_cache_set( "load_value/post_id={$option}/name={$key}", $reference, 'acf' );
                }
                */
            }

            $done = true;
        }
    }



    /**
     * Preemptively cache field references for ACF data in boilerplate option
     *
     * @used-by Actions: "acf/include_fields"
     */

    public static function prepare_field_references()
    {
        $cl = acf_get_setting('current_language');

        $boilerplate_options = get_option('boilerplate', []);

        if ( empty( $boilerplate_options['acf_options'] ) ) {
            $boilerplate_options['acf_options'] = [];
        }

        $acf_options = &$boilerplate_options['acf_options'];

        static::save_field_references( $acf_options );
    }



    /**
     * Load ACF data to boilerplate option
     *
     * Bypass ACF saving procedure.
     *
     * @param  mixed  $value    The value found in the database
     * @param  mixed  $post_id  The $post_id from which the value was loaded
     * @param  array  $field    The field array holding all the field options
     * @return $value
     */

    public static function load_option( $value, $post_id, $field )
    {
        $cl = acf_get_setting('current_language');

        if ( in_array( $post_id, [ 'options', "options_$cl" ] ) ) {
            $boilerplate_options = get_option('boilerplate', []);

            if ( empty( $boilerplate_options['acf_options'] ) ) {
                $boilerplate_options['acf_options'] = [];
            }

            $acf_options = &$boilerplate_options['acf_options'];

            static::save_field_references( $acf_options );

            # $key = $post_id . '_' . $field['name'];
            $key = $field['name'];

            if ( isset( $acf_options[ $key ] ) ) {
                $value = $acf_options[ $key ];

                if ( function_exists('pll_translate_string') ) {
                    if ( is_array( $value ) ) {
                        foreach ( $value as &$v ) {
                            $v = pll_translate_string( $v, pll_current_language() );
                        }
                    }
                    else {
                        $value = pll_translate_string( $value, pll_current_language() );
                    }
                }

                // no value? try default_value
                if ( $value === null && isset( $field['default_value'] ) ) {
                    $value = $field['default_value'];

                    if ( function_exists('pll_translate_string') ) {
                        if ( is_array( $value ) ) {
                            foreach ( $value as &$v ) {
                                $v = pll_translate_string( $v, pll_current_language() );
                            }
                        }
                        else {
                            $value = pll_translate_string( $value, pll_current_language() );
                        }
                    }
                }

                // if value was duplicated, it may now be a serialized string!
                $value = maybe_unserialize( $value );
            }
        }

        return $value;
    }



    /**
     * Save ACF data to boilerplate option
     *
     * Bypass ACF saving procedure.
     */

    public static function save_options( $post_id )
    {
        $current_screen = get_current_screen();

        if ( 'boilerplate-settings' === $current_screen->id ) {
            // save $_POST data
            foreach ( $_POST['acf'] as $k => $v ) {

                // get field
                $field = acf_get_field( $k );

                // update field
                if ( $field ) {
                    static::update_option( $v, $post_id, $field );
                }

            }

            // Cancel following actions, such as `acf_input::save_post()`
            $_POST['acf'] = [];
        }
    }



    /**
     * Updates a value into the options db
     *
     * Bypass ACF saving procedure.
     *
     * @param   {mixed}     $value      the value to be saved
     * @param   {int}       $post_id    the post ID to save the value to
     * @param   {array}     $field      the field array
     * @param   {boolean}   $exact      allows the update_value filter to be skipped
     * @return  N/A
     */

    public static function update_option( $value = null, $post_id = 0, $field )
    {
        $boilerplate_options = get_option('boilerplate', []);
        $return  = false;

        if ( empty( $boilerplate_options['acf_options'] ) ) {
            $boilerplate_options['acf_options'] = [];
        }

        $acf_options = &$boilerplate_options['acf_options'];

        // strip slashes
        // allow 3rd party customisation
        if ( acf_get_setting('stripslashes') ) {
            $value = stripslashes_deep( $value );
        }

        // filter for 3rd party customization
        $value = apply_filters( 'acf/update_value',        $value, $post_id, $field );
        $value = apply_filters( 'acf/update_value/type=' . $field['type'], $value, $post_id, $field );
        $value = apply_filters( 'acf/update_value/name=' . $field['name'], $value, $post_id, $field );
        $value = apply_filters( 'acf/update_value/key='  . $field['key'], $value, $post_id, $field );

        // for some reason, update_option does not use stripslashes_deep.
        // update_metadata -> http://core.trac.wordpress.org/browser/tags/3.4.2/wp-includes/meta.php#L82: line 101 (does use stripslashes_deep)
        // update_option -> http://core.trac.wordpress.org/browser/tags/3.5.1/wp-includes/option.php#L0: line 215 (does not use stripslashes_deep)
        $value = stripslashes_deep( $value );

        $key = /* $post_id . '_' . */$field['name'];

        $acf_options[ $key ] = $value;
        $acf_options[ '_' . $key ] = $field['key'];

        $return = update_option( 'boilerplate', $boilerplate_options );

        // clear cache
        wp_cache_delete( 'load_value/post_id=' . $post_id . '/name=' . $field['name'], 'acf' );

        return $return;
    }

// ==========================================================================
// Special Fields
// ==========================================================================

    /**
     * Filter: Retrieve the menu order from the current object.
     *
     * @return int
     */
    public static function get_menu_order( $value, $post_id, $field )
    {
        $post = get_post($post_id);

        $value = $post->menu_order;

        return $value;
    }

}

ACF::init();
