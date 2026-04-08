<?php

/**
 * Hooks and side effects to customize ACF and ACF Extended.
 */

/**
 * Configures ACF and ACF Extended.
 *
 * Force automatic synchronization of ACF's Local JSON
 * using ACF Extended Pro's module.
 */
add_action( 'acf/init', function () : void {
    if ( function_exists( 'acf_update_setting' ) ) {
        /** Disable Dynamic Block Types; unsupported by theme. */
        acf_update_setting( 'acfe/modules/block_types', false );

        /** Force automatic synchronization of ACF's Local JSON. */
        acf_update_setting( 'acfe/modules/force_sync', true );

        /** Disable ACF Extended Options Pages manager in favour of ACF's manager. */
        acf_update_setting( 'acfe/modules/options_pages', false );

        /** Enable Hybrid Peformance Engine to compress all field meta references (`field_abcdef123456`). */
        acf_update_setting( 'acfe/modules/performance', 'hybrid' );

        /** Disable ACF Extended Post Types manager in favour of ACF's manager. */
        acf_update_setting( 'acfe/modules/post_types', false );

        /** Disable ACF Extended Taxonomies manager in favour of ACF's manager. */
        acf_update_setting( 'acfe/modules/taxonomies', false );
    }

    if ( function_exists( 'acf_register_location_type' ) ) {
        acf_register_location_type( 'Theme\\ACF_Location_Void' );
    }

    _theme_acf_disable_oembed_format_value();

    _theme_acf_register_theme_settings();
} );

/**
 * Adds the placeholder setting to the WYSIWYG field type.
 */
add_action( 'acf/render_field_presentation_settings/type=wysiwyg', function ( array $field ) : void {
    acf_render_field_setting( $field, [
        'label'        => __( 'Placeholder Text', 'acf' ),
        'instructions' => __( 'Appears within the editor', 'acf' ),
        'type'         => 'text',
        'name'         => 'placeholder',
    ] );
} );

/**
 * Customizes ACF WYSIWYG toolbars.
 *
 * @see admin/acf.js JS API required to customize quicktags toolbar.
 */
add_filter( 'acf/fields/wysiwyg/toolbars', function ( array $toolbars ) : array {
    $toolbars['Decorative Title'] = [
        1 => [ 'italic', 'superscript', 'removeformat' ],
    ];

    $toolbars['Job Offer Description'] = [
        1 => [ 'formatselect', 'bold', 'italic', 'bullist', 'numlist', 'superscript', 'link' ],
    ];

    $toolbars['Accordion item'] = [
        1 => [ 'formatselect', 'bold', 'italic', 'bullist', 'numlist', 'superscript', 'link' ],
    ];

    $toolbars['Simple'] = [
        1 => [ 'bold', 'italic', 'link', 'superscript' ],
    ];

    $toolbars['Flexible Text'] = [
        1 => [
            'formatselect', 'bold', 'italic', 'bullist', 'numlist',
            'alignleft', 'aligncenter', 'alignright',
            'link', 'strikethrough', 'superscript', 'undo', 'redo'
        ],
    ];

    $toolbars['Basic Enhanced'][1] = [
        'formatselect', 'link', 'bold', 'italic', 'underline', 'superscript', 'blockquote',
        '|', 'bullist', 'numlist', 'alignleft',
        '|', 'source_code', 'undo', 'redo'
    ];

    if ( ( $key = array_search( 'code', $toolbars['Full'][2] ) ) !== false ) {
        unset( $toolbars['Full'][2][$key] );
    }

    unset( $toolbars['Basic'] );

    add_filter('tiny_mce_before_init', function($init) {
        $custom_css = theme_get_acf_style_uri( 'wysiwyg-decorative-title-editor' );
        if ( ! empty($init['content_css'] ) ) {
            $init['content_css'] .= ',' . $custom_css;
        } else {
            $init['content_css'] = $custom_css;
        }

        return $init;
    });

    return $toolbars;
}, 100, 1 );

/**
 * Formats the Redirection `hreflang` field as lowercase in the WordPress Bashboard.
 */
add_filter( 'acf/format_value/name=_links_to_hreflang', function ( mixed $value ) : mixed {
    if ( $value && is_string( $value ) ) {
        return mb_strtolower( $value );
    }

    return $value;
}, 10, 3 );

/**
 * Format Decorative titles.
 */
add_filter( 'acf/format_value/type=wysiwyg', function ( mixed $value, mixed $post_id, array $field ) : mixed {
    if (
        !is_string( $value )
        || !isset( $field['toolbar'] )
        || ('decorative_title' !== $field['toolbar'])
    ) {
        return $value;
    }

    $allowed_tags = ['em', 'br', 'sup'];

    return strip_tags( $value, $allowed_tags );
}, 20, 3 );

/**
 * Changes the save path for ACF JSON files based on
 * the custom field group setting `theme_local_theme`.
 */
add_filter( 'acf/json/save_paths', function ( array $paths, array $post ) {
    $post['theme_local_theme'] ??= get_template();

    $path = get_theme_root( $post['theme_local_theme'] ) . '/' . $post['theme_local_theme'] . '/acf-json';
    if ( file_exists( $path ) ) {
        return [ $path ];
    }

    if ( $post['theme_local_theme'] === get_stylesheet() ) {
        return [ get_stylesheet_directory() . '/acf-json' ];
    }

    if ( is_child_theme() ) {
        return [ get_template_directory() . '/acf-json' ];
    }

    return $paths;
}, 10, 2 );

/**
 * Loads the Site Theme from option page as default value for the institution field.
 */
add_filter( 'acf/load_field/name=site_theme', function ( mixed $field ) : mixed {
    $post_type = get_post_type();
    if (is_admin() && !$post_type) {
        $post_type = isset($_GET['post_type']) ? $_GET['post_type'] : 'post';
    }

    switch($post_type) {
        case 'theme_quote':
            $field['default_value'] = theme_get_site_theme();
            break;

        default:
            $field['default_value'] = ( $field['default_value'] ?: 'theme-theme' );
            break;
    }

    return $field;
}, 10, 3 );

/**
 * Loads and sets the Institution CPT data from Corpo.
 */
add_filter( 'acf/load_field/type=radio', function( mixed $field ) : mixed {
    if ($field['name'] !== 'institution') {
        return $field;
    }
    $family = theme_get_family();
    $institutions = $family['menu'];

    $choices = [];
    if ( !empty( $institutions ) ) {
        foreach ( $institutions as $institution ) {
            $id = $institution['theme'];
            $label = '';
            if ( $institution['prefix'] ) {
                $label = sprintf( '%s — ', $institution['prefix']);
            }
            $label .= $institution['name'];
            $choices[$id] = $label;
        }
    }

    $field['choices'] = $choices;
    return $field;
}, 10, 3 );

/**
 * Sets default post type selection for ACFE Advanced Link.
 */
add_filter( 'acf/load_field/type=acfe_advanced_link', function( mixed $field ) {
    $field['post_type'] = [ 'page', 'post' ];

    return $field;
}, 10, 3 );

/**
 * Remove the Term option for ACFE Advanced Link.
 */
add_filter( 'acfe/fields/advanced_link/sub_fields', function ( $sub_fields, $field, $value ) {
    $radio_sub_field_key = null;
    foreach ( $sub_fields as $key => $sub_field ) {
        if ( $sub_field['key'] === 'type' && $sub_field['type'] === 'radio' ) {
            $radio_sub_field_key = $key;
            break;
        }
    }

    if ( $radio_sub_field_key !== null ) {
        unset( $sub_fields[$radio_sub_field_key]['choices']['term'] );
    }

    return array_values ( $sub_fields ) ;
}, 10, 3 );

/**
 * Loads Remote Job Offers and populate them in local (hidden) Job offer CPT.
 */
add_filter( 'acf/fields/relationship/query/name=job_offers', function( $args, $field, $post_id ) {
    if ( ! wp_doing_ajax() ||
         (  ! doing_action( 'wp_ajax_acf/fields/relationship/query' ) &&
            ! doing_action( 'wp_ajax_nopriv_acf/fields/relationship/query' )
         )
    ) {
        return $args;
    }

    theme_sync_job_offers();
    return $args;
}, 10, 3 );

/**
 * Loads Remote Job Offer Instutions and populate in local (hidden) Job offer insitution taxonomy (theme_job_offer_institution).
 */
add_filter( 'acf/load_field/name=job_offers', function ( array $field ) : array {
    theme_sync_job_institutions();
    return $field;
}, 10, 3 );

/**
 * Loads the Redirection `hreflang` field as uppercase in the WordPress Bashboard.
 */
add_filter( 'acf/load_value/name=_links_to_hreflang', function ( mixed $value ) : mixed {
    if ( $value && is_string( $value ) && is_admin() ) {
        return mb_strtoupper( $value );
    }

    return $value;
}, 10, 3 );

/**
 * Integrates support for custom placeholder setting for the WYSIWYG field type.
 */
add_filter( 'acf/prepare_field/type=wysiwyg', function ( array $field ) : array {
    if ( ! empty( $field['placeholder'] ) && preg_match( '/\{([a-z_\-]+:[a-z_\-]+)\}/', $field['placeholder'], $matches ) ) {
        $field['placeholder'] = match ( $matches[1] ) {
            default => '',
        };

        /** Custom placeholder documented in admin/acf-custom-placeholders.js */
        if ( 'post:post_title' === $matches[1] ) {
            $field['wrapper'] ??= [];
            $field['wrapper']['data-spy'] = 'title';
        }
    }

    return $field;
} );

/**
 * Loads ACF JSON files from parent theme if child theme is active.
 */
add_filter( 'acf/settings/load_json', function ( array $paths ) : array {
    if ( is_child_theme() ) {
        $paths[] = get_template_directory() . '/acf-json';
    }

    return $paths;
}, 10, 1 );

/**
 * Stores the Redirection `hreflang` field as lowercase.
 */
add_filter( 'acf/update_value/name=_links_to_hreflang', function ( mixed $value ) : mixed {
    if ( $value && is_string( $value ) ) {
        return mb_strtolower( $value );
    }

    return $value;
}, 10, 3 );

/**
 * Validates the end date of a Job offer, must be greater than its start date.
 */
add_filter( 'acf/validate_value/key=field_678974b0ea637', function ( bool $valid, mixed $value ) : bool|string {
    if ( ! $valid ) {
        return $valid;
    }

    $start_date = $_POST['acf']['field_67897439ea636'];

    if ( ! $start_date || ! $value ) {
        return $valid;
    }

    $start = new \DateTimeImmutable( $start_date );
    $end   = new \DateTimeImmutable( $value );

    if ( $start >= $end ) {
        $valid = __( 'La date de fin doit être postérieure à la date de début.', 'theme-theme' );
    }

    return $valid;
}, 10, 2 );

/**
 * Validates the oEmbed field, must be Cloudflare, Mux, Vimeo, or YouTube.
 */
add_filter( 'acf/validate_value/type=oembed', function ( bool|string $valid, mixed $value ) : bool|string {
    if ( ! $value ) {
        return $valid;
    }

	if ( empty( $field['streamable_only'] ) ) {
		return $valid;
	}

    if ( theme_determine_video_provider_from_url( $value ) ) {
        return $valid;
    }

    return __( 'URL non valide. Seuls Cloudflare, Mux, Vimeo, et YouTube sont autorisés', 'theme-theme' );
}, 10, 4);

/**
 * Adds a choice on Oembed ACF Group Field builder.
 */
add_action( 'acf/render_field_validation_settings/type=oembed', function ( array $field ) : void {
	acf_render_field_setting( $field, [
		'name'  => 'streamable_only',
		'label' => __( 'Streamable Video Only?', 'theme-theme' ),
		'type'  => 'true_false',
		'ui'    => 1,
	] );
} );

/**
 * Trims outer whitespace.
 */
add_filter( 'acf_the_content', function ( string $value ) : string {
    return trim( $value );
}, 20 );

/**
 * Fixes ACF reCAPTCHA field type translations.
 */
add_filter( 'gettext_acfe', '_theme_acfe_filter_gettext_translation', 10, 2 );

/**
 * Fixes ACF reCAPTCHA field type translations accidentally categorized with default domain.
 */
add_filter( 'gettext_default', '_theme_acfe_filter_gettext_translation', 10, 2 );

/**
 * Changes the default TinyMCE block formats.
 */
add_filter( 'tiny_mce_before_init', function ( array $mce_init ) : array {
    $mce_init['block_formats'] = 'Paragraph=p;Heading 2=h2;Heading 3=h3;Heading 4=h4;';

    return $mce_init;
} );

/**
 * Remove layout options from the Hero Standout block that is a clone of the standout content type.
 */
add_filter( 'acf/load_field/key=field_682397036f923' , function($field) {
    if ( is_iterable( $field['sub_fields'] ?? null ) ) {
        foreach ( $field['sub_fields'] as &$sub_field ) {
            if ( $sub_field['name'] !== 'layout' ) {
                continue;
            }

            $choices = $sub_field['choices'];
            unset($choices['triptych-light']);
            unset($choices['portrait-light']);
            unset($choices['diagonal-light']);
            unset($choices['notebook']);
            unset($choices['statistic']);
            unset($choices['percentages']);

            $sub_field['choices'] = $choices;
        }
    }
    return $field;
});

/**
 * Add possibility to remove some layout options.
 */
add_filter( 'acf/load_field/key=field_67929a73a2eaf', function($field) {
    if (!empty($field['choices'])) {
        $removed_layouts = apply_filters('theme/standout/removed-layouts', []);
        foreach ($removed_layouts as $layout) {
            unset($field['choices'][$layout]);
        }
    }

    return $field;
});

/**
 * Disable the base theme to use the percentages layout in standouts.
 */
add_filter( 'theme/standout/removed-layouts', function () {
    return [
        'percentages',
    ];
}, 10 );
