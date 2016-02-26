<?php

/**
 * File: Theme Utilities
 *
 * @package Boilerplate\Utilities
 */

/**
 * Load a template view into a template
 *
 * Makes it easy for a theme to reuse components located
 * in the `views/` directory. This function is a clone
 * of {@see WordPress\get_template_part()}.
 *
 * @global Including variables declared in {@see load_template()}
 *
 * @param string $slug The slug name for the generic template.
 * @param string $name The name of the specialised template.
 */

function boilerplate_get_template_view( $slug, $name = null, $data = [] )
{
    $slug = ltrim( $slug, '/' );

    /**
     * Fires before the specified template view file is loaded.
     *
     * The dynamic portion of the hook name, `$slug`, refers to the slug name
     * for the generic template part.
     *
     * @param string $slug The slug name for the generic template.
     * @param string $name The name of the specialized template.
     * @param array  &$data Variables to be pass along to the specialized template.
     */

    do_action_ref_array( "boilerplate/get_template_view_{$slug}", [ $slug, $name, &$data ] );

    $slug = "views/{$slug}";

    $templates = [];
    $name = (string) $name;
    if ( '' !== $name ) {
        $templates[] = "{$slug}-{$name}.php";
    }

    $templates[] = "{$slug}.php";

    $template_name = locate_template( $templates, false, false );

    if ( '' != $template_name ) {
        global $posts, $post, $wp_did_header, $wp_query, $wp_rewrite, $wpdb, $wp_version, $wp, $id, $comment, $user_ID;

        unset( $templates, $slug, $name );

        if ( ! empty( $data ) && is_array( $data ) ) {
            extract( $data, EXTR_SKIP );
        }

        include( $template_name );
    }
}



/**
 * Load a template part into a template
 *
 * Makes it easy for a theme to reuse sections of code in a easy to overload way
 * for child themes.
 *
 * This function is a variant of {@see get_template_part()} that provides
 * a $data parameter to pass variables to the view without having to rely
 * on the global scope.
 *
 * @link https://core.trac.wordpress.org/ticket/21673
 *
 * @global Including variables declared in {@see load_template()}
 *
 * @param  mixed|string  $slug  The slug name for the generic template.
 * @param  string        $name  The name of the specialised template.
 * @param  array         $data  Variables to be pass along to the specialized template.
 */

function boilerplate_get_template_part( $slug, $name = null, $data = [] )
{
    $defaults = [
        'slug' => '',
        'name' => null,
        'data' => []
    ];

    $args = null;

    $params = func_get_args();

    foreach ( $params as $param => $value ) {
        if ( is_array( $value ) && isset( $value['slug'] ) ) {
            $args = $value;
            break;
        }
    }

    if ( is_null( $args ) && is_string( func_get_arg( 0 ) ) ) {
        $args = $params;
    }
    else {
        return false;
    }

    $args = wp_parse_args( $args, $defaults );

    extract( $args, EXTR_SKIP);

    unset( $args, $params );

    /**
     * Fires before the specified template part file is loaded.
     *
     * The dynamic portion of the hook name, `$slug`, refers to the slug name
     * for the generic template part.
     *
     * @param string $slug The slug name for the generic template.
     * @param string $name The name of the specialized template.
     * @param array  &$data Variables to be pass along to the specialized template.
     */

    do_action_ref_array( "boilerplate/get_template_part_{$slug}", [ $slug, $name, &$data ] );

    $templates = [];
    $name = (string) $name;
    if ( '' !== $name ) {
        $templates[] = "{$slug}-{$name}.php";
    }

    $templates[] = "{$slug}.php";

    $template_name = locate_template( $templates, false, false );

    if ( '' != $template_name ) {
        global $posts, $post, $wp_did_header, $wp_query, $wp_rewrite, $wpdb, $wp_version, $wp, $id, $comment, $user_ID;

        unset( $templates, $slug, $name );

        if ( ! empty( $data ) && is_array( $data ) ) {
            extract( $data, EXTR_SKIP );
        }

        include( $template_name );
    }
}

/**
 * Retrieve the post thumbnail.
 *
 * @param mixed[] $args {
 *     @type int $post_id       Post ID. Default is the ID of the `$post` global.
 *     @type string|array $size Optional. Registered image size to use, or flat array of height
 *                              and width values. Default 'post-thumbnail'.
 *     @type string|array $attr Optional. Query string or array of attributes. Default empty.
 * }
 *
 * @return string Returns a path to the featured image or a placeholder
 */

function boilerplate_get_featured_image( $args = [] )
{
    $upload_dir = wp_upload_dir();

    $defaults = [
          'placeholder' => boilerplate_get_asset_url( 'images/backgrounds/placeholder-' . intval( rand( 1, 2 ) ) . '.jpg' )
        , 'size'        => 'post-thumbnail'
        , 'attr'        => ''
        , 'post_id'     => null
    ];

    if ( is_string( $args ) ) {
        $args = [
            'size' => $args // 'post-thumbnail'
        ];
    }
    else if ( is_int( $args ) ) {
        $args = [
            'post_id' => $args // null
        ];
    }
    else if ( ! is_array( $args ) ) {
        $args = [];
    }

    $args = wp_parse_args( $args, $defaults );

    $args['post_id'] = ( null === $args['post_id'] ? get_the_ID() : $args['post_id'] );
    $args['thumb_id'] = get_post_thumbnail_id( $args['post_id'] );

    $image = wp_get_attachment_image_src( $args['thumb_id'], $args['size'], $args['attr'] );

    if ( is_array( $image ) ) {
        $image = $image[0];
    }

    $path = str_replace( $upload_dir['baseurl'], $upload_dir['basedir'], $image );

    if ( empty( $image ) || ! file_exists( $path ) ) {
        $image = $args['placeholder'];
    }

    return $image;
}



/**
 * Retrieve an SVG _use_ element representing an SVG _symbol_.
 *
 * @todo Both the SVG and fallback sources can be arrays.
 *
 * @param string[]      $svg_src      The path, relative to the theme directory, of the desired SVG.
 * @param bool|string[] $fallback_src Optional. The path, relative to the theme directory, for a non-SVG image alternative.
 * @param array         $svg_attr     Optional. XML attributes to apply to the _svg_ element wrapping the _use_ element.
 * @param array         $use_attr     Optional. XML attributes to apply to the _use_ element.
 *
 * @return string SVG _use_ element or empty string on failure.
 */

function use_svg_tag( $svg_src, $fallback_src = false, $svg_attr = [], $use_attr = [] )
{
    if ( ! is_array( $svg_attr ) ) {
        $svg_attr = [];
    }

    if ( ! isset( $svg_attr['role'] ) ) {
        $svg_attr['role'] = 'img';
    }
    if ( ! isset( $svg_attr['xmlns'] ) ) {
        $svg_attr['xmlns'] = 'http://www.w3.org/2000/svg';
    }
    if ( ! isset( $svg_attr['xmlns:xlink'] ) ) {
        $svg_attr['xmlns:xlink'] = 'http://www.w3.org/1999/xlink';
    }

    if ( ! is_array( $use_attr ) ) {
        $use_attr = [];
    }

    if ( file_exists( get_template_directory() . strstr( $svg_src, '#', true ) ) ) {
        $use_attr['xlink:href'] = get_template_directory_uri() . $svg_src;
    }
    else {
        return false;
    }

    if ( ! empty( $fallback_src ) ) {
        if ( file_exists( get_template_directory() . strstr( $fallback_src, '#', true ) ) ) {
            $use_attr['data-fallback-src'] = get_template_directory_uri() . $fallback_src;
        }
        else {
            return false;
        }
    }

    return '<svg ' . html_build_attributes( $svg_attr ) . '><use ' . html_build_attributes( $use_attr ) . '></use></svg>' . N;
}



/**
 * Generate a string of HTML attributes
 *
 * Generates a string of HTML attributes from the associative array provided.
 *
 * @param   mixed   $attr_data  May be an associative array or object containing properties.
 * @return  string  $output     Returns a string of HTML attributes.
 */

function html_build_attributes( $attr_data = [] )
{
    $output = '';

    if ( count($attr_data) ) {

        $output = array_map(
            function ( $v, $k ) {
                if ( is_bool($v) ) {
                    return ( $v ? $key : '' );
                }
                else if ( ! empty($v) ) {
                    if ( is_array( $v ) ) {
                        $v = implode( ' ', $v );
                    }
                    return sprintf('%s="%s"', $k, $v);
                }
            },
            $attr_data,
            array_keys($attr_data)
        );

        $output = implode(' ', $output);
    }

    return $output;
}



/**
 *
 */

function is_element_empty( $element )
{
    $element = trim( $element );
    return ! empty( $element );
}



/**
 *
 */

function is_empty( $element )
{
    if ( is_string($element) ) {
        $element = trim($element);
    }
    if ( is_array($element) ) {
        $element = array_filter($element, 'strlen');
    }
    return ! empty($element);
}



/**
 * Set the internal pointer of an array to its first element
 *
 * @param  array  $array  The array. This array is passed by reference because it is modified by the function.
 *                        This means you must pass it a real variable and not a function returning an array
 *                        because only actual variables may be passed by reference.
 * @return Returns a reference of the first element or NULL for empty array.
 */

function first( &$array )
{
    if ( ! is_array( $array ) ) {
        return $array;
    }

    if ( ! count( $array ) ) {
        return null;
    }

    reset( $array );

    return $array[ key( $array ) ];
}



/**
 * Set the internal pointer of an array to its last element
 *
 * @param  array  $array  The array. This array is passed by reference because it is modified by the function.
 *                        This means you must pass it a real variable and not a function returning an array
 *                        because only actual variables may be passed by reference.
 * @return Returns a reference of the last element or NULL for empty array.
 */

function last( &$array )
{
    if ( ! is_array( $array ) ) {
        return $array;
    }

    if ( ! count( $array ) ) {
        return null;
    }

    end( $array );

    return $array[ key( $array ) ];
}



/**
 * Display the HTML attributes for the search form tag.
 *
 * Builds up a set of HTML attributes containing the role, method, and action
 * values for the form.
 */

function boilerplate_search_form_attributes()
{
    $attributes = [];

    $attributes['role']   = 'search';
    $attributes['method'] = 'get';
    $attributes['action'] = boilerplate_get_search_url();

    /**
     * Filter the search form attributes for display in the HTML tag.
     *
     * @param string $output A space-separated list of search form attributes.
     */
    echo apply_filters( 'search_form_attributes', html_build_attributes( $attributes ) );
}



/**
 * Display the HTML attributes for the search input tag.
 *
 * Builds up a set of HTML attributes containing the name, value, placeholder, and title
 * values for the input.
 *
 * @param string $placeholder Optional. Defaults to "Search...".
 */

function boilerplate_search_input_attributes( $placeholder = null )
{
    $attributes = [];

    if ( empty( $placeholder ) ) {
        $placeholder = _x( 'Search&hellip;', 'search field placeholder', 'boilerplate' );
    }

    $attributes['name']        = 's';
    $attributes['value']       = get_search_query();
    $attributes['placeholder'] = esc_attr( $placeholder );
    $attributes['title']       = esc_attr_x( 'Search for:', 'label' );

    /**
     * Filter the search input attributes for display in the HTML tag.
     *
     * @param string $output A space-separated list of search input attributes.
     */
    echo apply_filters( 'search_input_attributes', html_build_attributes( $attributes ) );
}



/**
 * Display the search URL for the current site.
 *
 * @param  string $path   Optional. Path relative to the search URL. Default empty.
 * @param  string $scheme Optional. Scheme to give the search URL context. Accepts
 *                        'http', 'https', or 'relative'. Default null.
 * @return string Search URL link with optional path appended.
 */

function boilerplate_search_url( $path = '', $scheme = null )
{
    echo boilerplate_get_search_url( null, $path, $scheme );
}



/**
 * Retrieve the search URL for a given site.
 *
 * @param  int         $blog_id     Optional. Blog ID. Default null (current blog).
 * @param  string      $path        Optional. Path relative to the search URL. Default empty.
 * @param  string|null $orig_scheme Optional. Scheme to give the search URL context. Accepts
 *                                  'http', 'https', 'relative', or null. Default null.
 * @return string Search URL link with optional path appended.
 */

function boilerplate_get_search_url( $blog_id = null, $path = '', $scheme = null )
{
    $orig_scheme = $scheme;

    if ( empty( $blog_id ) || !is_multisite() ) {
        $url = get_option( 'home' );
    }
    else {
        switch_to_blog( $blog_id );
        $url = get_option( 'home' );
        restore_current_blog();
    }

    if ( ! in_array( $scheme, array( 'http', 'https', 'relative' ) ) ) {
        if ( is_ssl() && ! is_admin() && 'wp-login.php' !== $GLOBALS['pagenow'] ) {
            $scheme = 'https';
        }
        else {
            $scheme = parse_url( $url, PHP_URL_SCHEME );
        }
    }

    $url = set_url_scheme( $url, $scheme );

    if ( $path && is_string( $path ) ) {
        $url .= '/' . ltrim( $path, '/' );
    }

    /**
     * Filter the search URL.
     *
     * @param string      $url         The complete search URL including scheme and path.
     * @param string      $path        Path relative to the search URL. Blank string if no path is specified.
     * @param string|null $orig_scheme Scheme to give the search URL context. Accepts 'http', 'https', 'relative' or null.
     * @param int|null    $blog_id     Blog ID, or null for the current blog.
     */
    return apply_filters( 'search_url', $url, $path, $orig_scheme, $blog_id );
}



/**
 * Filter the HTML output of the search form.
 *
 * Tell WordPress to use `searchform.php`
 * from the _views_ directory
 *
 * @param string $form The search form HTML output.
 *
 * @return string $form
 */

function boilerplate_get_search_form( $form )
{
    $search_form_template = locate_template('views/search-form.php');

    if ( '' != $search_form_template ) {
        ob_start();
        require( $search_form_template );
        $form = ob_get_clean();
    }

    return $form;
}



/**
 * Retrieve the object's "search_items" label.
 *
 * @param string|object $post_type Post type name or object.
 *
 * @return string
 */

function boilerplate_get_search_label( $post_type = '' )
{
    $label = _x( 'Search&hellip;', 'search field placeholder', 'boilerplate' );

    if ( empty( $post_type ) ) {
        $post_type = get_post_type();
    }

    if ( is_string( $post_type ) ) {
        $post_object = get_post_type_object( $post_type );
    }
    elseif ( is_object( $post_type ) ) {
        $post_object = $post_type;
    }

    if ( isset( $post_object ) && ! in_array( $post_object->name, [ 'page', 'attachment' ] ) && isset( $post_object->labels->search_items ) ) {
        $label = $post_object->labels->search_items;
    }

    return $label;
}



/**
 * Filter the list of CSS body classes for the current post or page.
 *
 * Add page slug to body_class() classes if it doesn't exist
 *
 * @param array  $classes An array of body classes.
 * @param string $class   A comma-separated list of additional classes added to the body.
 *
 * @return array
 */

function boilerplate_body_class( $classes )
{
    global $wp_query;

    if ( is_home() ) {
        $classes[] = 'archive';
        $classes[] = 'post-type-archive';

        $post_type = get_query_var( 'post_type' );

        if ( is_array( $post_type ) ) {
            $post_type = reset( $post_type );
        }

        if ( empty( $post_type ) ) {
            $post_type = 'post';
        }

        $classes[] = 'post-type-archive-' . sanitize_html_class( $post_type );
    }

    if ( is_home() || is_archive() ) {
        $classes[] = 'page-template';
        $classes[] = 'page-template-archive';
    }

    // Add post/page slug
    if ( is_single() || is_page() && ! is_front_page() ) {
        if ( ! in_array( basename( get_permalink()), $classes ) ) {
            $classes[] = basename( get_permalink() );
        }
    }

    if ( is_page_template() ) {
        $page_id  = $wp_query->get_queried_object_id();
        $template = 'page-template-' . sanitize_html_class( str_replace( '.', '-', get_page_template_slug( $page_id ) ) );

        if ( false !== ( $k = array_search( $template, $classes ) ) ) {
            $classes[ $k ] = substr( $template, 0, -4 );
        }
    }

    // Loading animation
    //$classes[] = 'is-fadein';

    //if (is_search()) {
    //    $classes[] = 'has-search-open';
    //}

    return $classes;
}

/**
 * Display the classes for the html element.
 *
 * @param string|array $class One or more classes to add to the class list.
 */
function html_class( $class = '' ) {
    // Separates classes with a single space, collates classes for html element
    echo 'class="' . implode( ' ', get_html_class( $class ) ) . '"';
}



/**
 * Retrieve the classes for the html element as an array.
 *
 * @param string|array $class One or more classes to add to the class list.
 * @return array Array of classes.
 */
function get_html_class( $class = '' ) {
    global $wp_query, $wpdb;

    $classes = [];

    //$classes[] = 'no-js';
    if ($template = get_data_template()) {
        $classes[] = 't-' . $template;
    }

    if ( ! empty( $class ) ) {
        if ( !is_array( $class ) )
            $class = preg_split( '#\s+#', $class );
        $classes = array_merge( $classes, $class );
    } else {
        // Ensure that we always coerce class to being an array.
        $class = [];
    }

    $classes = array_map( 'esc_attr', $classes );

    /**
     * Filter the list of CSS html classes for the current post or page.
     *
     * @param array  $classes An array of html classes.
     * @param string $class   A comma-separated list of additional classes added to the html.
     */
    $classes = apply_filters( 'html_class', $classes, $class );

    return array_unique( $classes );
}



/**
 * Chromeless layouts
 *
 * @param array $templates
 *
 * @return array $templates
 */

function boilerplate_wrap_chromless_base( $templates = [] )
{
    $match = apply_filters( 'sage/chromeless_templates', [] );
    $base  = 'base-chromeless.php';

    if ( array_intersect( $match, $templates ) ) {
        $count = count( $templates );

        if ( $count > 1 ) {
            $templates = array_insert( $templates, [ $base ], ( $count - 1 ), 'before' );
        }
        else {
            $templates[] = $base;
        }
    }

    return $templates;
}



/**
 * If the content is empty, display a message.
 *
 * @param string $content
 *
 * @return string $content
 */

function boilerplate_the_content( $content )
{
    if ( ! is_front_page() && ! is_home() && ! is_post_type_archive() && ! is_page_template() && is_singular() && empty( $content ) ) {
        $content = '<p>' . __( 'Sorry, content is in production.', 'boilerplate' ) . ' ' . __( 'Please try again later.', 'boilerplate' ) . '</p>';
    }

    return $content;
}



/**
 * Adds the WordPress Ajax Library to the frontend.
 */

function boilerplate_wp_ajax( $nonce = null )
{
?>
        <script>
            var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
        </script>
<?php
}



/**
 * Wrap embedded media as suggested by Readability
 *
 * @see WP_Embed::shortcode()
 *
 * @param mixed  $cache   The cached HTML result, stored in post meta.
 * @param string $url     The attempted embed URL.
 * @param array  $attr    An array of shortcode attributes.
 * @param int    $post_ID Post ID.
 */

function boilerplate_embed_wrap_iframe( $cache, $url, $attr, $post_ID )
{
    if ( 1 === preg_match( '#\<iframe\b#i', $cache ) ) {
        $cache = preg_replace( '#\b(entry-content-asset)\b#i', '$1 entry-asset-iframe', $cache );
    }

    return $cache;
}

add_filter( 'embed_oembed_html', 'boilerplate_embed_wrap_iframe', 11, 4 );



/**
 * Outputs a simple template identifier
 */

function data_template()
{
    echo get_data_template();
}

/**
 * Return a simple template identifier
 */

function get_data_template()
{
    global $wp_query, $wpdb;

    $data_template = '';

    if ( is_front_page() ){
        $data_template = 'home';
    }
    if ( is_search() ) {
        $data_template = 'search';
    }
    if ( is_404() ) {
        $data_template = 'error404';
    }
    if ( is_single() ) {
        $post_id = $wp_query->get_queried_object_id();
        $post = $wp_query->get_queried_object();

        $data_template = 'single';

        if ( isset( $post->post_type ) ) {
            $data_template = $data_template . '-' . sanitize_html_class($post->post_type, $post_id);
        }

    }
    if ( is_archive() || is_home() ) {

        if ( is_post_type_archive() ) {
            $post_type = get_query_var( 'post_type' );
            if ( is_array( $post_type ) )
                $post_type = reset( $post_type );
            $template = 'post-type-' . sanitize_html_class( $post_type );
        } elseif ( is_category() ) {
            $cat = $wp_query->get_queried_object();
            if ( isset( $cat->term_id ) ) {
                $template = 'category-' . $cat->term_id;
            }
        } elseif ( is_tag() ) {
            $tag = $wp_query->get_queried_object();
            if ( isset( $tag->term_id ) ) {
                $template = 'tag-' . sanitize_html_class( $tag->term_id );
            }
        } elseif ( is_tax() ) {
            $term = $wp_query->get_queried_object();
            if ( isset( $term->term_id ) ) {
                $template = 'tax-' . sanitize_html_class( $term->taxonomy );
            }
        }else {
            $template = 'post';
        }

        $data_template = 'archive-' . sanitize_html_class( $template );
    }
    if ( is_page() ) {
        //$data_template = 'page';

        $page_id = $wp_query->get_queried_object_id();

        if ( is_page_template() ) {

            $template_slug  = get_page_template_slug( $page_id );

            /*$template_parts = explode( '/', $template_slug );

            foreach ( $template_parts as $part ) {
                $classes[] = 'page-template-' . sanitize_html_class( str_replace( array( '.', '/' ), '-', basename( $part, '.php' ) ) );
            }
            */


            $data_template = basename( $template_slug, '.php' );

            $data_template = sanitize_html_class( str_replace( '.', '-', $data_template ) );
        }
    }

    $data_template = str_replace('boilerplate-','',$data_template);

    $data_template = str_replace('-','_',$data_template);

    return $data_template;
}


/**
 * Quick detection for Wordpress thinking we're on the blog page
 */

function boilerplate_is_blog()
{
    global $post;
    $post_type = get_post_type( $post );
    return ( ( $post_type == 'post' ) && ( is_home() || is_single() || is_archive() || is_category() || is_tag() || is_author() ) ) ? true : false;
}


/**
 * Retrieve category parents with separator.
 *
 * Based on {@see get_category_parents()}.
 *
 * @link http://wordpress.stackexchange.com/a/39862
 *
 * @param int    $id        Category ID.
 * @param string $taxonomy  Taxonomy name that $id is part of.
 * @param bool   $link      Optional, default is false. Whether to format with link.
 * @param string $separator Optional, default is '/'. How to separate categories.
 * @param bool   $nicename  Optional, default is false. Whether to use nice name for display.
 * @param array  $visited   Optional. Already linked to categories to prevent duplicates.
 *
 * @return string|WP_Error A list of category parents on success, WP_Error on failure.
 */

function boilerplate_get_taxonomy_parents( $id, $taxonomy, $link = false, $separator = '/', $nicename = false, $visited = [] )
{
    $chain = '';
    $parent = get_term( $id, $taxonomy );

    if ( is_wp_error( $parent ) ) {
        return $parent;
    }

    if ( $nicename ) {
        $name = $parent->slug;
    }
    else {
        $name = $parent->name;
    }

    if ( $parent->parent && ( $parent->parent != $parent->term_id ) && ! in_array( $parent->parent, $visited ) ) {
        $visited[] = $parent->parent;
        $chain .= boilerplate_get_taxonomy_parents( $parent->parent, $taxonomy, $link, $separator, $nicename, $visited );
    }

    if ( $link ) {
        $chain .= '<a href="' . esc_url( get_term_link( $parent->term_id, $taxonomy ) ) . '">' . $name . '</a>' . $separator;
    }
    else {
        $chain .= $name . $separator;
    }

    return $chain;
}


/**
 * Calculate the copyright range
 *
 * @param bool $from_launch Optional, default is false. Whether to create a range from launch till now (ex.: 2015-2016 instead of 2016)
 *
 * @return string
 */

function boilerplate_website_copyright( $from_launch = false )
{

    $now_date = new DateTime( 'now', new DateTimeZone( 'America/Montreal' ) );
    $now_year = (int)$now_date->format('Y');

    if( $from_launch ){

        // Soft launched in February 2016
        $launch_date = new DateTime( '2016-02-26', new DateTimeZone( 'America/Montreal' ) );
        $launch_year = (int)$launch_date->format('Y');

        $copyright = ( $now_year > $launch_year ) ? $launch_year . '-' . $now_year : $now_year;

    }else{

        $copyright = $now_year;

    }

    echo "©&nbsp;".$copyright;
}
