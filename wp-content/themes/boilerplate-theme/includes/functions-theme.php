<?php

/**
 * General functions for the theme.
 *
 * @phpstan-type Link array{
 *     label: string,
 *     url: string,
 *     target: ?string,
 *     icon: ?string,
 *     modifiers: list<string>|string|null,
 * }
 */

/**
 * Defines if the provided URL lead to a diffrent website.
 *
 * @param string $url
 *
 * @return boolean If the provided URL leads to different site
 */
function theme_is_external_url(string $url): bool
{
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        return false;
    }

    $current_host = parse_url( home_url(), PHP_URL_HOST );
    $target_host = parse_url( $url, PHP_URL_PATH );

    return $current_host !== $target_host;
}

/**
 * Returns an array with the differences between $array1 and $array2
 * $strict variable defines if comparison must be strict or not.
 *
 * Based on {@link https://github.com/rogervila/array-diff-multidimensional}.
 */
function theme_compare_array( array $array1, array $array2, bool $strict = true ) : array {
    if ( ! is_array( $array2 ) ) {
        return $array1;
    }

    $result = [];

    foreach ( $array1 as $key => $value ) {
        if ( ! array_key_exists( $key, $array2 ) ) {
            $result[$key] = $value;
            continue;
        }

        if ( is_array( $value ) && count( $value ) > 0) {
            $recursiveArrayDiff = theme_compare_array( $value, $array2[$key], $strict );

            if (count($recursiveArrayDiff) > 0) {
                $result[$key] = $recursiveArrayDiff;
            }

            continue;
        }

        $value1 = $value;
        $value2 = $array2[$key];

        if ( $strict ? is_float( $value1 ) && is_float( $value2 ) : is_float( $value1 ) || is_float( $value2 ) ) {
            $value1 = (string) $value1;
            $value2 = (string) $value2;
        }

        if ( $strict ? $value1 !== $value2 : $value1 != $value2 ) {
            $result[$key] = $value;
        }
    }

    return $result;
}

/**
 * Convert a URL into a standardized link array structure for the theme.
 *
 * If the URL has a different origin, the target will be "_blank".
 *
 * @param  array<string, mixed>|string $url The URL to convert.
 * @return array<string, mixed> The link array structure.
 *
 * @phpstan-return Link
 */
function theme_convert_url_to_link_array( array|string $url ) : array {
    if ( is_array( $url ) ) {
        if ( ! empty( $url['label'] ) ) {
            $label = $url['label'];
        } elseif ( ! empty( $url['title'] ) ) {
            $label = $url['title'];
        } elseif ( ! empty( $url['name'] ) ) {
            $label = $url['name'];
        }

        $link = [
            'label'     => ( $label ?? null ),
            'url'       => ( $url['url'] ?? null ),
            'target'    => ( $url['target'] ?? null ),
            'icon'      => ( $url['icon'] ?? null ),
            'modifiers' => ( $url['modifiers'] ?? [] ),
        ];

        if ( $link['url'] && $link['target'] !== '_blank' ) {
            $host = theme_parse_url_host( $link['url'] );
            if ( $host ) {
                $_host  = theme_parse_url_host( home_url() );
                $link['target'] = ( $host !== $_host ) ? '_blank' : null;
            }
        }

        $link['icon'] = ( '_blank' === $link['target'] )
            ? 'arrow-external'
            : 'arrow-right';

        return $link;
    }

    $host = theme_parse_url_host( $url );
    if ( $host ) {
        $_host  = theme_parse_url_host( home_url() );
        $target = ( $host !== $_host ) ? '_blank' : null;
    } else {
        $target = null;
    }

    $icon = ( '_blank' === $target )
        ? 'arrow-external'
        : 'arrow-right';

    return [
        'label'    => theme_parse_url_host( $url ),
        'url'      => $url,
        'target'   => $target,
        'icon'     => $icon,
        'modifiers'=> [],
    ];
}

/**
 * Parses a date/time string by guessing its format.
 */
function theme_create_datetime( string $datetime, ?DateTimeZone $timezone = null ): ?DateTimeImmutable {
    $format = theme_guess_date_format( $datetime );
    if ( ! $format ) {
        return null;
    }

    return DateTimeImmutable::createFromFormat( $format, $datetime, $timezone ) ?: null;
}

/**
 * @param string $name    The variable name.
 * @param mixed  $default The default value to return if
 *     the environment variable does not exist.
 *
 * @return mixed The value of the environment variable or the provided default value.
 */
function theme_get_environment_var( string $name, mixed $default = null ): mixed {
    if ( defined( $name ) ) {
        return constant( $name );
    }

    return getenv( $name ) ?: $default;
}

/**
 * Formats a relative path to a theme file.
 */
function theme_get_relative_theme_file_path( string $path ) : string {
    return ltrim(
        str_replace( get_stylesheet_directory(), '', $path ),
        '/'
    );
}

/**
 * Retrieves the handle for the given user.
 */
function theme_get_user_handle( int $user_id, ?WP_User $user_data = null ) : string {
    $user_info = ( $user_data ?? get_userdata( $user_id ) );

    return $user_info
        ? ( $user_info->display_name ?: $user_info->user_login )
        : 'N/A';
}

/**
 * Guesses the format from the given date/time string.
 *
 * Can detect ACF's `d/m/Y` and SQL's `Y-m-d`.
 */
function theme_guess_date_format( string $datetime ): ?string {

    if ( preg_match( '#^(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})$#', $datetime ) ) {
        return 'Y-m-d H:i:s';
    }

    if ( preg_match( '#^(\d{2})/(\d{2})/(\d{4})$#', $datetime ) ) {
        return 'd/m/Y';
    }

    if ( preg_match( '#^(\d{4})-(\d{2})-(\d{2})$#', $datetime ) ) {
        return 'Y-m-d';
    }

    if ( preg_match( '#^(\d{8})$#', $datetime ) ) {
        return 'Ymd';
    }

    return null;
}

/**
 * Hashes a unique file name.
 *
 * @param  string  $name The file's base name.
 * @param  ?string $ext  The file's extension.
 * @return string  The full file name.
 */
function theme_hash_file_name( string $name, ?string $ext = null ) : string {
    if ( null === $ext ) {
        $ext  = pathinfo( $name, PATHINFO_EXTENSION );
        $name = pathinfo( $name, PATHINFO_BASENAME );

        if ( $ext ) {
            $ext = '.' . $ext;
        }

        // Edge case: if file is named '.ext', treat as an empty name.
        if ( $name === $ext ) {
            $name = '';
        }
    }

    $file_name = hash( 'sha1', ( $name . uniqid( (string) rand(), true ) ) ) . time();
    return sanitize_file_name( $file_name . $ext );
}

/**
 * Loads a template part from the blocks sub-directory.
 *
 * @param string               $block The block name.
 * @param array<string, mixed> $args  Arguments to pass to the template.
 */
function theme_include_block( string $block, array $args = [] ) : bool {
    return (bool) theme_include_partial( "blocks/block-{$block}", $args );
}

/**
 * Loads a template part from the partials sub-directory.
 *
 * @param string               $view The view name.
 * @param array<string, mixed> $args Arguments to pass to the template.
 */
function theme_include_partial( string $view, array $args = [] ) : bool {
    return (bool) get_template_part( "partials/{$view}", null, $args );
}

/**
 * Determines whether a post is publicly viewable with the given status.
 *
 * Alternative to {@see is_post_publicly_viewable()}.
 */
function theme_is_post_publicly_viewable( WP_Post|int $post = 0, ?string $status = null ) : bool {
    $post = get_post( $post );

    if ( ! $post ) {
        return false;
    }

    $is_viewable = apply_filters( 'theme/pre_is_post_viewable', null, $post, $status );
    if ( is_bool( $is_viewable ) ) {
        return $is_viewable;
    }

    $post_status = get_post_status( $post );

    if ( $status && $status !== $post_status ) {
        return false;
    }

    $post_type = get_post_type( $post );

    return is_post_type_viewable( $post_type ) && is_post_status_viewable( $post_status );
}

/**
 * Recursive version of {@see wp_parse_args()}.
 *
 * @param  array $args     Value to merge with $defaults.
 * @param  array $defaults Array that serves as the defaults.
 * @return array
 */
function theme_parse_args( array $args, array $defaults = [] ) : array {
    $parsed_args = $defaults;

    foreach ( $args as $key => $value ) {
        if ( isset( $parsed_args[ $key ] ) && is_array( $parsed_args[ $key ] ) ) {
            if ( is_array( $value ) ) {
                if ( array_is_list( $parsed_args[ $key ] ) ) {
                    $parsed_args[ $key ] = array_merge( $parsed_args[ $key ], $value );
                } else {
                    $parsed_args[ $key ] = theme_parse_args( $value, $parsed_args[ $key ] );
                }
            }
        } else {
            $parsed_args[ $key ] = $value;
        }
    }

    return $parsed_args;
}

/**
 * Parse the host of a given URL.
 */
function theme_parse_url_host( string $url ) : ?string {
    if ( empty( $url ) ) {
        return null;
    }

    $host = parse_url( $url, PHP_URL_HOST );

    if ( empty( $host ) ) {
        return null;
    }

    $host = str_replace( 'www.', '', $host );

    return $host;
}

/**
 * Marks something as being incorrectly called.
 *
 * This function is based on {@see _doing_it_wrong()}.
 */
function _theme_doing_template_part_wrong(
    string $file,
    string $message,
    ?string $version = null
) : void {
    $file = theme_get_relative_theme_file_path( $file );

    /** This filter is documented in `wp-includes/functions.php`. */
    do_action( 'doing_it_wrong_run', $file, $message, $version );

    /** This filter is documented in `wp-includes/functions.php`. */
    if ( WP_DEBUG && apply_filters( 'doing_it_wrong_trigger_error', true, $file, $message, $version ) ) {
        if ( $version ) {
            /* translators: %s: Version number. */
            $version = sprintf( __( '(This message was added in version %s.)' ), $version );
        }

        $message .= ' ' . sprintf(
            /* translators: %s: Documentation URL. */
            __( 'Please see <a href="%s">Debugging in WordPress</a> for more information.' ),
            __( 'https://developer.wordpress.org/advanced-administration/debug/debug-wordpress/' )
        );

        $message = sprintf(
            /* translators: Developer debugging message. 1: PHP function name, 2: Explanatory message, 3: WordPress version number. */
            __( 'Theme file %1$s was called <strong>incorrectly</strong>. %2$s %3$s' ),
            $file,
            $message,
            $version
        );

        wp_trigger_error( '', $message );
    }
}

/**
 * Retrieves the output of the callback.
 */
function _theme_ob_func( callable $callback ) : string {
    ob_start();
    $callback();
    return trim( ob_get_clean() );
}

/**
 * @todo Figure out a reliable way to check if a string contains valid HTML syntax.
 */
function _theme_string_is_valid_html( string $string ) : bool {
    return true;
}

if ( ! function_exists( 'array_insert' ) ) {
    /**
     * Insert an array into another array before/after a certain key
     *
     * Merge the elements of the $array array after, or before, the designated $key from the $input array.
     * It returns the resulting array.
     *
     * {@link https://gist.github.com/mcaskill/902ac7703021ce663eac}
     *
     * @param array  $input  The input array.
     * @param mixed  $insert The value to merge.
     * @param mixed  $key    The key from the $input to merge $insert next to.
     * @param string $pos    Wether to splice $insert before or after the $key.
     *
     * @return array Returns the resulting array.
     */
    function array_insert( array $input, mixed $insert, int|string $key, string $pos = 'after') : array {
        if ( ! is_string( $key ) && ! is_int( $key ) ) {
            trigger_error( 'array_insert(): The key should be a string or an integer', E_USER_ERROR );
        }

        $offset = array_search( $key, array_keys( $input ) );

        if ( 'after' === $pos ) {
            $offset++;
        }

        if ( false !== $offset ) {
            $result = array_slice( $input, 0, $offset );
            $result = array_merge( $result, (array) $insert, array_slice( $input, $offset ) );
        } else {
            $result = array_merge( $input, (array) $insert );
        }

        return $result;
    }
}

if ( ! function_exists( 'theme_esc_html' ) ) {
    function theme_esc_html( string $content, $allowed_html = null ) : string {
        if ( $allowed_html === null ) {
            $allowed_html = apply_filters( 'theme/esc-html/allowed-html', [
                'strong' => [],
                'p'      => [],
                'a'      => [],
                'i'      => [],
                'em'     => [],
                'b'      => [],
                'sup'    => [],
                'sub'    => [],
                'br'    => [],
            ] );
        }

        return wp_kses($content, $allowed_html);
    }
}

function _theme_transient_caching_is_enabled(): bool {
    // Disable the transient caching only if the WP Config specifies it.
    if (defined('THEME_TRANSIENT_CACHING') && !THEME_TRANSIENT_CACHING) {
        return false;
    }

    return true;
}

function theme_get_site_transient(string $transient) {
    if ( ! _theme_transient_caching_is_enabled() ) {
        return false;
    }

    return get_site_transient( $transient );
}

function theme_get_transient(string $transient) {
    if ( ! _theme_transient_caching_is_enabled() ) {
        return false;
    }

    return get_transient( $transient );
}

function theme_set_transient(string $transient, $value, int $expiration = 0 ) : bool {
    if ( ! _theme_transient_caching_is_enabled() ) {
        return false;
    }

    return set_transient($transient, $value, $expiration);
}

function theme_set_site_transient(string $transient, $value, int $expiration = 0 ) : bool {
    if ( ! _theme_transient_caching_is_enabled() ) {
        return false;
    }

    return set_site_transient($transient, $value, $expiration);
}
